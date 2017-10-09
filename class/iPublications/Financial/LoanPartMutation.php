<?php namespace iPublications\Financial;

use iPublications\Financial\Loan;
use iPublications\Financial\LoanPart;
use \Exception as Exception;

class LoanPartMutation implements \JsonSerializable {

    use \iPublications\Traits\DateTrait;

    /**
     * Constants
     **/

    const INTEREST_COMPOUND = 'INTEREST_COMPOUND';  // Samengestelde interest
    const INTEREST_SIMPLE   = 'INTEREST_SIMPLE';    // Enkelvoudige interest

    /**
     * Members
     **/

    private $M_i_dateUnixtime;

    private $M_f_loanAmount;
    private $M_f_interestAmount;
    private $M_f_loanAmountMutation;
    private $M_f_interestAmountMutation;
    private $M_s_loanCurrency;
    private $M_s_loanInterestType;
    private $M_f_loanInterestPercentage;

    /**
     * Magic Methods
     **/

    public function __construct($P_s_date = null){
        if(!is_null($P_s_date)) $this->setDate($P_s_date);
        return $this;
    }

    public function __toString(){
        return '(object) ' . __CLASS__;
    }

    public function jsonSerialize() {
        $L_a_returnMutation = [];

        if(!empty($this->M_i_dateUnixtime))
            $L_a_returnMutation['date'] = @date('Y-m-d', (int) @$this->M_i_dateUnixtime);

        if(!empty($this->M_f_loanAmount))
            $L_a_returnMutation['amount'] = (float) @$this->M_f_loanAmount;

        if(!empty($this->M_f_interestAmount))
            $L_a_returnMutation['interestamount'] = (float) @$this->M_f_interestAmount;

        if(!empty($this->M_f_loanAmountMutation))
            $L_a_returnMutation['amount_mutation'] = (float) @$this->M_f_loanAmountMutation;

        if(!empty($this->M_f_interestAmountMutation))
            $L_a_returnMutation['interestamount_mutation'] = (float) @$this->M_f_interestAmountMutation;

        if(!empty($this->M_f_loanInterestPercentage))
            $L_a_returnMutation['interest_percentage'] = (float) @$this->M_f_loanInterestPercentage;

        if(!empty($this->M_s_loanInterestType))
            $L_a_returnMutation['interest_type'] = (string) @$this->M_s_loanInterestType;

        if(!empty($this->M_s_loanCurrency))
            $L_a_returnMutation['currency'] = (string) @$this->M_s_loanCurrency;

        return $L_a_returnMutation;
    }

    /**
     * Public Methods
     **/


    public function setAmount($P_f_loanAmount = 0, $P_s_currency = null){
        if(!is_int($P_f_loanAmount) && !is_float($P_f_loanAmount)){
            throw new Exception(__METHOD__ . ": invalid value, expects int or float");
        }

        $this->M_f_loanAmount = (float) $P_f_loanAmount;

        if(!is_null($P_s_currency)){
            $this->setCurrency($P_s_currency);
        }

        return $this;
    }

    public function setInterestAmount($P_f_interestAmount = 0){
        if(!is_int($P_f_interestAmount) && !is_float($P_f_interestAmount)){
            throw new Exception(__METHOD__ . ": invalid value, expects int or float");
        }

        $this->M_f_interestAmount = (float) $P_f_interestAmount;

        return $this;
    }

    public function setAmountMutation($P_f_loanAmountMutation = 0, $P_s_currency = null){
        if(!is_int($P_f_loanAmountMutation) && !is_float($P_f_loanAmountMutation)){
            throw new Exception(__METHOD__ . ": invalid value, expects int or float");
        }

        $this->M_f_loanAmountMutation += (float) $P_f_loanAmountMutation;

        return $this;
    }

    public function setInterestAmountMutation($P_f_interestAmountMutation = 0, $P_s_currency = null){
        if(!is_int($P_f_interestAmountMutation) && !is_float($P_f_interestAmountMutation)){
            throw new Exception(__METHOD__ . ": invalid value, expects int or float");
        }

        $this->M_f_interestAmountMutation += (float) $P_f_interestAmountMutation;

        if($this->M_f_interestAmountMutation > 0){
            throw new Exception(__METHOD__ . ": invalid value, expects *negative* int or float (deduction)");
        }

        return $this;
    }

    public function setCurrency($P_s_currency = ''){
        if(is_string($P_s_currency)){
            $L_s_currency = trim(strtoupper($P_s_currency));
            if(preg_match("@^[A-Z]{3}$@", $L_s_currency)){
                $this->M_s_loanCurrency = $L_s_currency;
            }else{
                throw new Exception(__METHOD__ . ": invalid currency, expects 3 upper A-Z chars");
            }
        }else{
            throw new Exception(__METHOD__ . ": currency non-string");
        }

        return $this;
    }

    public function setInterestType($P_s_loanInterestType = ''){
        if(is_string($P_s_loanInterestType)){
            $L_s_loanInterestType = strtoupper(trim($P_s_loanInterestType));

            if($L_s_loanInterestType == self::INTEREST_COMPOUND || $L_s_loanInterestType == self::INTEREST_SIMPLE){
                $this->M_s_loanInterestType = $P_s_loanInterestType;
            }else{
                throw new Exception(__METHOD__ . ": invalid interest-type");
            }
        }else{
            throw new Exception(__METHOD__ . ": interest-type non-string");
        }

        return $this;
    }

    public function setInterestPercentage($P_f_loanInterestPercentage = 0){
        if(!is_int($P_f_loanInterestPercentage) && !is_float($P_f_loanInterestPercentage)){
            throw new Exception(__METHOD__ . ": invalid value, expects int or float");
        }

        $this->M_f_loanInterestPercentage = (float) $P_f_loanInterestPercentage;

        return $this;
    }

    public function getDate($P_b_asTimestamp = false){
        $L_s_format = ($P_b_asTimestamp ? 'U' : 'Y-m-d');
        return date($L_s_format, $this->M_i_dateUnixtime);
    }

    public function getAmount($P_f_fallback = null){
        if(isset($this->M_f_loanAmount) && (is_float($this->M_f_loanAmount) || is_int($this->M_f_loanAmount))){
            return $this->M_f_loanAmount;
        }else{
            if(!is_null($P_f_fallback) && !is_empty($P_f_fallback) && (is_float($P_f_fallback) || is_int($P_f_fallback))){
                return $P_f_fallback;
            }
        }

        return null;
    }

    public function getInterestAmount($P_f_fallback = null){
        if(isset($this->M_f_interestAmount) && (is_float($this->M_f_interestAmount) || is_int($this->M_f_interestAmount))){
            return $this->M_f_interestAmount;
        }else{
            if(!is_null($P_f_fallback) && !is_empty($P_f_fallback) && (is_float($P_f_fallback) || is_int($P_f_fallback))){
                return $P_f_fallback;
            }
        }

        return null;
    }

    public function getAmountMutation(){
        if(isset($this->M_f_loanAmountMutation) && (is_float($this->M_f_loanAmountMutation) || is_int($this->M_f_loanAmountMutation))){
            return $this->M_f_loanAmountMutation;
        }

        return null;
    }

    public function getInterestAmountMutation(){
        if(isset($this->M_f_interestAmountMutation) && (is_float($this->M_f_interestAmountMutation) || is_int($this->M_f_interestAmountMutation))){
            return $this->M_f_interestAmountMutation;
        }

        return null;
    }

    public function getInterestPercentage($P_f_fallback = null){
        if(isset($this->M_f_loanInterestPercentage) && (is_float($this->M_f_loanInterestPercentage) || is_int($this->M_f_loanInterestPercentage))){
            return $this->M_f_loanInterestPercentage;
        }else{
            if(!is_null($P_f_fallback) && !is_empty($P_f_fallback) && (is_float($P_f_fallback) || is_int($P_f_fallback))){
                return $P_f_fallback;
            }
        }

        return null;
    }

    public function getCurrency($P_s_fallback = null){
        if(isset($this->M_s_loanCurrency) && is_string($this->M_s_loanCurrency)){
            return $this->M_s_loanCurrency;
        }else{
            if(!is_null($P_s_fallback) && !is_empty($P_s_fallback)){
                return $P_s_fallback;
            }
        }

        return null;
    }

    public function getInterestType($P_s_fallback = null){
        if(isset($this->M_s_loanInterestType) && is_string($this->M_s_loanInterestType)){
            return $this->M_s_loanInterestType;
        }else{
            if(!is_null($P_s_fallback) && !is_empty($P_s_fallback)){
                if($P_s_fallback == self::INTEREST_COMPOUND || $P_s_fallback == self::INTEREST_SIMPLE){
                    return $P_s_fallback;
                }
            }
        }

        return null;
    }

    public function isComplete(){
        $L_b_valid = true;

        if(empty($this->M_s_loanCurrency)){
            $L_b_valid = false;
            throw new Exception(__METHOD__ . ": invalid LoanPartMutation: loanCurrency is empty");
        }
        if(empty($this->M_s_loanInterestType)){
            $L_b_valid = false;
            throw new Exception(__METHOD__ . ": invalid LoanPartMutation: loanInterestType is empty");
        }
        if(empty($this->M_f_loanInterestPercentage)){
            $L_b_valid = false;
            throw new Exception(__METHOD__ . ": invalid LoanPartMutation: loanInterestPercentage is empty");
        }

        return $L_b_valid;
    }

    public function deferLoanCharacterizations(Loan $P_o_loan){
        if(!empty($this->M_i_dateUnixtime)){
            throw new Exception(__METHOD__ . ": cannot defer loanPartMutation startDate: non-empty");
        }

        $this->setDate($P_o_loan->getStartDate());
        return null;
    }

    /**
     * Private Methods
     **/

    private function setDate($P_s_date = 'today'){
        $this->M_i_dateUnixtime = $this->getTimestampFromDateString($P_s_date);
        return $this;
    }

}