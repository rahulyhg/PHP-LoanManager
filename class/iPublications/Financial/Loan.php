<?php namespace iPublications\Financial;

use iPublications\Financial\Debtor;
use iPublications\Financial\LoanPart;
use \Exception as Exception;

class Loan implements \JsonSerializable {
    use \iPublications\Traits\DateTrait;

    /**
     * Constants
     **/

    const __LOAN_VERSION__ = 1;

    /**
     * Members
     **/

    private $M_b_constructed = false;

    private $M_o_debtor;
    private $M_a_loanParts = [];
    private $M_i_startDateUnixtime;

    /**
     * Magic Methods
     **/

    public function __construct(Debtor $P_o_debtor, $P_a_loanParts = [], $P_s_date = null){
        $this->setStartDate($P_s_date);

        $this->M_o_debtor = $P_o_debtor;

        if(empty($P_a_loanParts)){
            throw new Exception(__METHOD__ . ": loanParts empty");
        }
        if(!is_array($P_a_loanParts)){
            throw new Exception(__METHOD__ . ": loanParts non-array");
        }

        foreach(array_values($P_a_loanParts) as $loanPartIterator => $loanPart){
            if($loanPart instanceof LoanPart){
                $this->addLoanPart($loanPart);
            }else{
                throw new Exception(__METHOD__ . ": loanPart[".$loanPartIterator."] no instanceof LoanPart");
            }
        }

        $this->M_b_constructed = true;

        return $this;
    }

    public function __toString(){
        return '(object) ' . __CLASS__;
    }

    public function jsonSerialize() {
        $L_a_jsonObject = @json_decode(@json_encode([
            'version'   => self::__LOAN_VERSION__,
            'objects'   => [
                'sources'   => [
                    'iPublications\\Financial\\Debtor',
                    'iPublications\\Financial\\Loan',
                    'iPublications\\Financial\\LoanPart',
                    'iPublications\\Financial\\LoanPartMutation',
                ],
                'targets'   => [
                    'iPublications\\Financial\\Loan',
                ],
            ],
            'debtor'    => @json_decode(@json_encode(@$this->M_o_debtor)),
            'loan'      => [
                'start_date' => @date('Y-m-d', @$this->M_i_startDateUnixtime),
            ],
            'parts'     => @array_keys(@$this->M_a_loanParts),
            'mutations' => @array_map(@function($r){
                return $r;
            }, @$this->M_a_loanParts),
        ]));

        if(!empty($L_a_jsonObject->parts)){
            foreach($L_a_jsonObject->parts as $k=>$part){
                $exception = true;
                if(isset($L_a_jsonObject->mutations)){
                    if(isset($L_a_jsonObject->mutations->$part)){
                        $countPartsEqOne = count((array) $L_a_jsonObject->mutations->$part) == 1;
                        if(isset($L_a_jsonObject->mutations->$part->{0}) || $countPartsEqOne){
                            $exception = false;
                            $L_a_jsonObject->parts[$part] = [
                                'identifier' => $part,
                                'type' => @$this->M_a_loanParts[$part]->getDetails()['type'],
                                'initial_mutation' => $countPartsEqOne ? reset($L_a_jsonObject->mutations->$part) : $L_a_jsonObject->mutations->$part->{0},
                                'options' => @$this->M_a_loanParts[$part]->getLoanPartOptions(),
                            ];
                            unset($L_a_jsonObject->parts[$k]);
                            if($countPartsEqOne){
                                unset($L_a_jsonObject->mutations->$part);
                            }else{
                                unset($L_a_jsonObject->mutations->$part->{0});
                            }
                            $L_a_jsonObject->mutations->$part = @array_values((array) $L_a_jsonObject->mutations->$part);
                        }
                    }
                }else{
                    throw new Exception(__METHOD__ . ": cannot serialize Loan Object: invalid parts/mutations");
                }
            }
        }
        return $L_a_jsonObject;
    }

    /**
     * Public Methods
     **/

    public function addLoanPart(LoanPart $P_o_loanPart){
        if(!$this->M_b_constructed){
            $P_o_loanPart->deferLoanCharacterizations($this);
            if(isset($this->M_a_loanParts[$P_o_loanPart->getIdentifier()])){
                throw new Exception(__METHOD__ . ": loanPart [ ".$P_o_loanPart->getIdentifier()." ] not unique");
            }
        }
        $this->M_a_loanParts[$P_o_loanPart->getIdentifier()] = $P_o_loanPart;

        return $this;
    }

    public function getStartDate($P_b_asTimestamp = false){
        $L_s_format = ($P_b_asTimestamp ? 'U' : 'Y-m-d');
        return date($L_s_format, $this->M_i_startDateUnixtime);
    }

    public function getLoanParts(){
        return array_keys($this->M_a_loanParts);
    }

    public function getLoanPart($P_s_loanPartIdentifier = ''){
        $L_s_loanPartIdentification = preg_replace("@[^A-Z0-9]@", "", strtoupper(trim($P_s_loanPartIdentifier)));

        if(!empty($L_s_loanPartIdentification)){
            if(isset($this->M_a_loanParts[$L_s_loanPartIdentification])){
                return $this->M_a_loanParts[$L_s_loanPartIdentification];
            }
        }else{
            throw new Exception(__METHOD__ . ": loanPart identification empty, should contain [A-Z0-9]+");
        }

        throw new Exception(__METHOD__ . ": loanPart not found");
    }

    public function getDebtorDetails(){
        return [
            'identifier' => $this->M_o_debtor->getIdentifier(),
            'uid' => $this->M_o_debtor->getUid(),
        ];
    }

    public function getDetails(){
        return [
            'startdate' => $this->getStartDate(),
        ];
    }

    /**
     * Private Methods
     **/

    private function setStartDate($P_s_date = 'today'){
        $this->M_i_startDateUnixtime = $this->getTimestampFromDateString($P_s_date);
        return $this;
    }

    /**
     * Static methods
     **/

    public static function constructFromJson($P_s_json = ''){
        if(is_string($P_s_json)){
            $L_a_data = @json_decode($P_s_json);
            if($L_a_data){
                if(is_object($L_a_data)){
                    if( isset($L_a_data->version) &&
                        isset($L_a_data->debtor) &&
                        isset($L_a_data->loan) &&
                        isset($L_a_data->parts) &&
                        isset($L_a_data->mutations)){

                        $loanParts = [];
                        foreach($L_a_data->parts as $part=>$partData){
                            $loanPartOptions = [];
                            if(isset($partData->options) && (is_array($partData->options) || is_object($partData->options)) && !empty($partData->options)){
                                $loanPartOptions = (array) $partData->options;
                            }

                            $loanPart = new LoanPart(
                                (new LoanPartMutation())
                                    ->setInterestType(constant('iPublications\\Financial\\LoanPartMutation::' . $partData->initial_mutation->interest_type))
                                    ->setAmount( (float) @$partData->initial_mutation->amount, @$partData->initial_mutation->currency)
                                    ->setInterestAmount( (float) @$partData->initial_mutation->interestamount)
                                    ->setInterestPercentage($partData->initial_mutation->interest_percentage),
                                constant('iPublications\\Financial\\LoanPart::' . $partData->type),
                                $partData->identifier,
                                $loanPartOptions
                            );

                            if(!empty($L_a_data->mutations->$part)){
                                foreach($L_a_data->mutations->$part as $mutation){
                                    $mutationObject = new LoanPartMutation($mutation->date);

                                    if(isset($mutation->interest_type)){
                                        $mutationObject->setInterestType(constant('iPublications\\Financial\\LoanPartMutation::' . $mutation->interest_type));
                                    }
                                    if(isset($mutation->interest_percentage)){
                                        $mutationObject->setInterestPercentage((float) $mutation->interest_percentage);
                                    }
                                    if(isset($mutation->amount_mutation)){
                                        $mutationObject->setAmountMutation((float) $mutation->amount_mutation, @$mutation->currency);
                                    }
                                    if(isset($mutation->interestamount_mutation)){
                                        $mutationObject->setInterestAmountMutation((float) $mutation->interestamount_mutation, @$mutation->currency);
                                    }

                                    $loanPart->addMutation($mutationObject);
                                }
                            }

                            $loanParts[] = $loanPart;
                        }

                        if($L_a_data->version == 1){
                            if(count($L_a_data->parts) > 0 && count($L_a_data->parts) == count($L_a_data->mutations)){
                                $self = new self(
                                   $debtor = (new Debtor($L_a_data->debtor->identifier,$L_a_data->debtor->uid)),
                                   $loanParts,
                                   $L_a_data->loan->start_date
                                );

                                return $self;
                            }else{
                                throw new Exception(__METHOD__ . ": cannot read JSON object: corrupted parts/mutations");
                            }
                        }else{
                            throw new Exception(__METHOD__ . ": cannot read JSON object: invalid document version");
                        }

                        return true;
                    }else{
                        throw new Exception(__METHOD__ . ": cannot read JSON object: valid JSON but missing sections");
                    }
                }
            }
        }

        throw new Exception(__METHOD__ . ": cannot read JSON object (input invalid)");

        return null;
    }

}