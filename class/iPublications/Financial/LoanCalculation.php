<?php namespace iPublications\Financial;

use iPublications\Financial\Loan;
use iPublications\Financial\LoanPart;
use iPublications\Financial\LoanPartMutation;
use \Exception as Exception;

class LoanCalculation implements \JsonSerializable {
    use \iPublications\Traits\DateTrait;

    /**
     * Constants
     **/

    /**
     * Members
     **/

    private $M_o_loan;
    private $M_i_startDateUnixtime;
    private $M_i_endDateUnixtime;

    private $M_a_mutationTotals = [];

    /**
     * Magic Methods
     **/

    public function __construct(Loan $P_o_loan, $P_s_date = 'today'){
        $this->M_o_loan = $P_o_loan;
        $this->setDateRange($P_s_date);

        return $this;
    }

    public function __toString(){
        return '(object) ' . __CLASS__;
    }

    public function jsonSerialize() {
        return $this->M_o_loan;
    }

    /**
     * Public Methods
     **/

    public function fetch($P_s_loanPartIdentifier = null){
        $L_a_fetchResults = [];

        if(!empty($P_s_loanPartIdentifier)){
            $L_o_loanPart     = $this->M_o_loan->getLoanPart($P_s_loanPartIdentifier);
            $L_a_fetchResults = $this->calculateLoanPart($L_o_loanPart);
        }else{
            $L_a_loanParts    = $this->M_o_loan->getLoanParts();
            array_walk($L_a_loanParts, function($loanPartIdentifier) use (&$L_a_fetchResults){
                $L_o_loanPart                          = $this->M_o_loan->getLoanPart($loanPartIdentifier);
                $L_a_fetchResults[$loanPartIdentifier] = $this->calculateLoanPart($L_o_loanPart);
            });$L_a_loanParts    = $this->M_o_loan->getLoanParts();
        }

        return $L_a_fetchResults;
    }

    public function getDebtorDetails(){
        return $this->M_o_loan->getDebtorDetails();
    }

    public function getLoanDetails(){
        return $this->M_o_loan->getDetails();
    }

    public function getMutationTotals($P_s_loanPart = ''){
        if(!empty($P_s_loanPart) && isset($this->M_a_mutationTotals) && is_array($this->M_a_mutationTotals) && isset($this->M_a_mutationTotals[$P_s_loanPart])){
            return @$this->M_a_mutationTotals[$P_s_loanPart];
        }
        return [];
    }

    public function getLoanPartDetails(){
        $L_a_loanPartDetails = [];
        $L_a_loanParts       = $this->M_o_loan->getLoanParts();
        array_walk($L_a_loanParts, function($loanPartIdentifier) use (&$L_a_loanPartDetails){
            $L_o_loanPart                             = $this->M_o_loan->getLoanPart($loanPartIdentifier);
            $L_a_loanPartDetails[$loanPartIdentifier] = $L_o_loanPart->getDetails();
        });
        return $L_a_loanPartDetails;
    }

    public function getCalculationEndDate($P_b_asTimestamp = false){
        $L_s_format = ($P_b_asTimestamp ? 'U' : 'Y-m-d');
        return date($L_s_format, $this->M_i_endDateUnixtime);
    }

    /**
     * Private Methods
     **/

    private function constructLoanPartMutationTotals(){
        return [
            'loan' => [
                'payout'   => 0,
                'receive'  => 0,
            ],
            'interest' => [
                'increase' => 0,
                'receive'  => 0,
            ],
        ];
    }

    private function calculateLoanPart(LoanPart $P_o_loanPart){
        $L_a_calculation = [];
        $L_a_mutations   = $P_o_loanPart->getMutations();

        if(!isset($this->M_a_mutationTotals[$P_o_loanPart->getIdentifier()])){
            $this->M_a_mutationTotals[$P_o_loanPart->getIdentifier()] = $this->constructLoanPartMutationTotals();
        }

        $L_b_negativeInterest     = true;
        if(isset($P_o_loanPart->getLoanPartOptions()['NEGATIVEINTEREST']) && !$P_o_loanPart->getLoanPartOptions()['NEGATIVEINTEREST']){
            $L_b_negativeInterest = false;
        }

        $L_loanAmount             = (float)  $L_a_mutations[0]->getAmount();
        $L_loanInterestPercentage = (float)  $L_a_mutations[0]->getInterestPercentage();
        $L_loanCurrency           = (string) $L_a_mutations[0]->getCurrency();
        $L_loanInterestType       = (string) $L_a_mutations[0]->getInterestType();
        $L_interestAmount         = (float)  $L_a_mutations[0]->getInterestAmount();

        // Todo: convert rate on change of currency (rate-exchange)

        for($i=$this->M_i_startDateUnixtime;$i<=$this->M_i_endDateUnixtime;$i=date('U', strtotime('+1 day', $i))){
            $I_s_date        = date('Y-m-d', $i);
            $I_i_yearDays    = $this->getDaysInYear(substr($I_s_date,0,4));

            /**
             * Mutations + Audit Trail
             **/

            $I_a_mutations   = [];
            if(isset($L_a_mutations[$i])){
                foreach([ 'Amount', 'InterestAmount', 'InterestPercentage', 'Currency', 'InterestType' ] as $applyChange){

                    if($applyChange !== 'InterestAmount'){
                        $oldValue = @${'L_loan'.$applyChange};
                    }else{
                        $oldValue = $L_interestAmount;
                    }

                    // Only the first mutation can set the Amount itself,
                    // hereafter only AmountMutation(s) can be set
                    if($applyChange == 'Amount'){
                        $newValue = $oldValue + $L_a_mutations[$i]->getAmountMutation();

                        if($L_a_mutations[$i]->getAmountMutation() > 0){
                            $this->M_a_mutationTotals[$P_o_loanPart->getIdentifier()]['loan']['payout']
                                += $L_a_mutations[$i]->getAmountMutation();
                        }elseif($L_a_mutations[$i]->getAmountMutation() < 0){
                            $this->M_a_mutationTotals[$P_o_loanPart->getIdentifier()]['loan']['receive']
                                += $L_a_mutations[$i]->getAmountMutation()*-1;
                        }

                    }elseif($applyChange == 'InterestAmount' && !is_null($L_a_mutations[$i]->getInterestAmountMutation())){
                        $newValue = $oldValue + $L_a_mutations[$i]->getInterestAmountMutation();
                        $L_interestAmount += $L_a_mutations[$i]->getInterestAmountMutation();
                        $I_a_mutations['InterestDeduction'] = '{'.$L_loanCurrency.'} -=> {'.(((float)$L_a_mutations[$i]->getInterestAmountMutation())*-1).'}';

                        // Interest return
                        if($L_a_mutations[$i]->getInterestAmountMutation() < 0){
                            $this->M_a_mutationTotals[$P_o_loanPart->getIdentifier()]['interest']['receive']
                                += $L_a_mutations[$i]->getInterestAmountMutation()*-1;
                        }

                    }else{
                        $newValue = $L_a_mutations[$i]->{'get'.$applyChange}();
                    }

                    if(!is_null($newValue)){
                        if($oldValue !== $newValue && $applyChange !== 'InterestAmount'){
                            $I_a_mutations[$applyChange] = '{'.$oldValue.'} => {'.$newValue.'}';
                            @${'L_loan'.$applyChange} = $newValue;
                        }
                    }
                }
            }

            /**
             * Calculate debt
             **/
            $I_f_dayInterest = $L_loanInterestPercentage/100/$I_i_yearDays;

            if($L_loanInterestType == LoanPartMutation::INTEREST_SIMPLE){
                $additionalInterest = ( ($L_loanAmount) * (1+$I_f_dayInterest) ) - ($L_loanAmount);
            }else{
                // Sample: (6000*(1+(0,056/365))^(365))-6000
                $additionalInterest = ( ($L_loanAmount+$L_interestAmount) * (1+$I_f_dayInterest) ) - ($L_loanAmount+$L_interestAmount);
            }

            if($additionalInterest > 0 || ($additionalInterest < 0 && $L_b_negativeInterest)){
                $L_interestAmount += $additionalInterest;
                $I_a_mutations['InterestAmount'] = '{'.$L_loanCurrency.'} +=> {'.$additionalInterest.'}';

                // if((float) $additionalInterest > 0){
                // Increase can be negative (decrease)
                // but it would still be a result on the
                // sum of interest. Skip the if-statement
                $this->M_a_mutationTotals[$P_o_loanPart->getIdentifier()]['interest']['increase']
                    += $additionalInterest;
                // }

            }

            /**
             * Output
             **/

            $L_a_calculation[$I_s_date] = [
                'values'    => [
                    'reference_date'      => $I_s_date,
                    'loan_amount'         => $L_loanAmount,
                    'interest_amount'     => $L_interestAmount,
                    'loan_currency'       => $L_loanCurrency,
                    'interest_percentage' => $L_loanInterestPercentage,
                    'interest_type'       => $L_loanInterestType,
                    'debt_total'          => ($L_loanAmount + $L_interestAmount),
                ],
                'mutations' => $I_a_mutations,
                'meta'      => [
                    'days_in_year'                => $I_i_yearDays,
                    'interest_percentage_per_day' => $I_f_dayInterest,
                ],
            ];
        }

        return $L_a_calculation;
    }

    private function setDateRange($P_s_date = 'today'){
        $this->M_i_startDateUnixtime = $this->M_o_loan->getStartDate(true);
        $this->M_i_endDateUnixtime   = $this->getTimestampFromDateString($P_s_date);
        // if(!empty($this->M_i_endDateUnixtime)) $this->M_i_endDateUnixtime += 86400;
        if($this->M_i_endDateUnixtime <= $this->M_i_startDateUnixtime){
            throw new Exception(__METHOD__ . ": endDate should be greater than startDate (set on Loan object)");
        }
        return $this;
    }

}