<?php namespace iPublications\Financial;

use iPublications\Financial\Loan;
use iPublications\Financial\LoanPartMutation;
use \Exception as Exception;

class LoanPart implements \JsonSerializable {

    /**
     * Constants
     **/

    const COMPONENT_LOAN    = 'COMPONENT_LOAN';     // Lening
    const COMPONENT_GRANT   = 'COMPONENT_GRANT';    // Beurs

    /**
     * Members
     **/

    private $M_a_loanPartMutations;
    private $M_s_loanPartIdentifier;
    private $M_s_loanPartType;
    private $M_a_loanPartOptions = [];

    /**
     * Magic Methods
     **/

    public function __construct(LoanPartMutation $P_o_mutation, $P_s_loanPartType, $P_s_loanPartIdentification, $P_a_loanPartOptions = []){
        $L_b_validLoanPartType = false;

        if(is_string($P_s_loanPartType)){
            $L_s_loanPartType = trim(strtoupper($P_s_loanPartType));
            if($L_s_loanPartType == self::COMPONENT_LOAN || $L_s_loanPartType == self::COMPONENT_GRANT){
                $L_b_validLoanPartType = true;
                $this->M_s_loanPartType = $L_s_loanPartType;
            }
        }

        if(is_string($P_s_loanPartIdentification) && !empty($P_s_loanPartIdentification)){
            $this->setIdentifier($P_s_loanPartIdentification);
        }

        if(!$L_b_validLoanPartType){
            throw new Exception(__METHOD__ . ": invalid loanType, ENUM[ COMPONENT_LOAN, COMPONENT_GRANT ]");
        }

        if(is_array($P_a_loanPartOptions) && !empty($P_a_loanPartOptions)){
            foreach($P_a_loanPartOptions as $loanPartOptionKey => $loanPartOptionValue){
                if(preg_match("@^[a-zA-Z0-9]{3,}$@", $loanPartOptionKey)){
                    $this->M_a_loanPartOptions[strtoupper($loanPartOptionKey)] = (bool) $loanPartOptionValue;
                }
            }
        }

        $this->addMutation($P_o_mutation);

        return $this;
    }

    public function __toString(){
        return '(object) ' . __CLASS__;
    }

    public function jsonSerialize() {
        return @array_map(function($r){
            return $r;
        }, @$this->M_a_loanPartMutations);
    }

    /**
     * Public Methods
     **/

    public function getLoanPartOptions(){
        return (array) $this->M_a_loanPartOptions;
    }

    public function addMutation(LoanPartMutation $P_o_mutation){
        $this->validateMutationDate($P_o_mutation);

        if($this->isInitialMutation() && !$P_o_mutation->isComplete()){
            throw new Exception(__METHOD__ . ": initial LoanPartMutation is invalid");
        }

        if($this->isInitialMutation()){
            $this->M_a_loanPartMutations[0] = $P_o_mutation;
        }else{
            $L_i_addedMutationTimestamp = $P_o_mutation->getDate(true);
            if(!is_null($P_o_mutation->getAmount())){
                throw new Exception(__METHOD__ . ": setAmount invalid for non-initial LoanPartMutation");
            }
            if(!is_null($P_o_mutation->getInterestAmount())){
                throw new Exception(__METHOD__ . ": setInterestAmount invalid for non-initial LoanPartMutation");
            }

            if($L_i_addedMutationTimestamp > $this->M_a_loanPartMutations[0]->getDate(true)){
                if(isset($this->M_a_loanPartMutations[$L_i_addedMutationTimestamp])){
                    /**
                     * Note: Only initial mutation can set Amount,
                     *   after initial mutation the CHANGE needs to be set,
                     *   using "AmountMutation",
                     *   where:
                     *       incresed DEBT is positive and
                     *       decreased DEBT is negative
                     */
                    foreach([ /*'Amount',*/ 'AmountMutation', 'InterestAmountMutation', 'InterestPercentage', 'Currency', 'InterestType' ] as $applyChange){
                        $applyValue = $P_o_mutation->{'get'.$applyChange}();
                        if(!is_null($applyValue)){
                            $this->M_a_loanPartMutations[$L_i_addedMutationTimestamp]->{'set'.$applyChange}($applyValue);
                        }
                    }
                }else{
                    $this->M_a_loanPartMutations[$L_i_addedMutationTimestamp] = $P_o_mutation;
                }
            }else{
                throw new Exception(__METHOD__ . ": loanPartMutation startDate lt or eq initial mutation startDate");
            }
        }

        return $this;
    }

    public function getIdentifier(){
        return $this->M_s_loanPartIdentifier;
    }

    public function deferLoanCharacterizations(Loan $P_o_loan){
        return $this->M_a_loanPartMutations[0]->deferLoanCharacterizations($P_o_loan);
    }

    public function getStartDate(){
        return $this->M_a_loanPartMutations[0]->getDate();
    }

    public function getMutations(){
        return $this->M_a_loanPartMutations;
    }

    public function getDetails(){
        $L_o_initialMutation = @reset(@$this->getMutations());
        return [
            'type' => $this->M_s_loanPartType ,
            'start' => [
                'amount'         => (float) @$L_o_initialMutation->getAmount(),
                'interestamount' => (float) @$L_o_initialMutation->getInterestAmount(),
            ],
            'currency' => @$L_o_initialMutation->getCurrency(),
        ];
    }

    /**
     * Private Methods
     **/

    private function isInitialMutation(){
        if(empty($this->M_a_loanPartMutations))
            return true;

        return false;
    }

    private function validateMutationDate(LoanPartMutation $P_o_mutation){
        if(!$this->isInitialMutation() && empty($P_o_mutation->getDate(true))){
            throw new Exception(__METHOD__ . ": non-initial loanPartMutation should be constructed with mutationDate");
        }
        return $this;
    }

    private function setIdentifier($P_s_loanPartIdentification){
        $L_s_loanPartIdentification = preg_replace("@[^A-Z0-9]@", "", strtoupper(trim($P_s_loanPartIdentification)));

        if(!empty($L_s_loanPartIdentification)){
            $this->M_s_loanPartIdentifier = $L_s_loanPartIdentification;
        }else{
            throw new Exception(__METHOD__ . ": loanPart identification empty, should contain [A-Z0-9]+");
        }

        return $this;
    }

}