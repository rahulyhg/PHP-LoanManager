<?php namespace iPublications\Traits;

use \Exception as Exception;

trait DateTrait {

    public function isLeapYear($P_i_year = 0){
        if ($P_i_year % 400 == 0) return true;
        if ($P_i_year % 100 == 0) return false;
        if ($P_i_year % 4 == 0)   return true;
                                  return false;
    }

    public function getDaysInYear($P_i_year = 0){
        return $this->isLeapYear($P_i_year) ? 366 : 365;
    }

    public function getTimestampFromDateString($P_s_date = 'today'){
        if(is_string($P_s_date) || is_integer($P_s_date)){
            $L_i_dateTimestamp = strtotime(date('Y-m-d 00:00:00', strtotime($P_s_date)));
            if($L_i_dateTimestamp > 0){
                return $L_i_dateTimestamp;
            }else{
                throw new Exception(__METHOD__ . ": cannot parse input as date/moment (strtotime)");
            }
        }

        throw new Exception(__METHOD__ . ": expect string or int");
    }

}