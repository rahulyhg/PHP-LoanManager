<?php namespace iPublications\Financial;

use \Exception as Exception;

class Debtor implements \JsonSerializable {

    /**
     * Constants
     **/

    /**
     * Members
     **/

    private $M_s_debtorIdentifier;
    private $M_s_uid;

    /**
     * Magic Methods
     **/

    public function __construct($P_s_debtorIdentifier, $P_s_uid = ''){
        $this->setDebtorIdentifier($P_s_debtorIdentifier);
        $this->setUid($P_s_uid);

        return $this;
    }

    public function __toString(){
        return '(object) ' . __CLASS__;
    }

    public function jsonSerialize() {
        return [
            'identifier' => @$this->M_s_debtorIdentifier,
            'uid'        => @$this->M_s_uid,
        ];
    }

    /**
     * Public Methods
     **/


    public function getIdentifier(){
        return $this->M_s_debtorIdentifier;
    }

    public function getUid(){
        return $this->M_s_uid;
    }

    /**
     * Private Methods
     **/

    private function setDebtorIdentifier($P_s_debtorIdentifier){
        if(is_string($P_s_debtorIdentifier) && !empty($P_s_debtorIdentifier)){
            $this->M_s_debtorIdentifier = trim($P_s_debtorIdentifier);
        }else{
            throw new Exception(__METHOD__ . ": invalid debtorIdentifier, should be string");
        }

        return $this;
    }

    private function setUid($P_s_uid){
        if(empty($P_s_uid)){
            $this->M_s_uid = md5($this->getIdentifier());
        }else{
            if(is_string($P_s_uid) && preg_match("@^[a-zA-Z0-9]+$@", $P_s_uid)){
                $this->M_s_uid = $P_s_uid;
            }else{
                throw new Exception(__METHOD__ . ": invalid debtorUid, should be string [a-zA-Z0-9]+");
            }
        }

        return $this;
    }
}