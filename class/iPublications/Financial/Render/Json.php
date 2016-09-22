<?php namespace iPublications\Financial\Render;

use iPublications\Financial\Loan;
use iPublications\Financial\LoanPart;
use iPublications\Financial\LoanPartMutation;
use iPublications\Financial\LoanCalculation;
use \Exception as Exception;

class Json implements \JsonSerializable {

    /**
     * Constants
     **/

    /**
     * Members
     **/

    private $M_o_loanCalculation;
    private $M_a_calculationResults;

    private $M_b_withDaily = true;

    /**
     * Magic Methods
     **/

    public function __construct(LoanCalculation $P_o_loanCalculation){
      $this->M_o_loanCalculation    = $P_o_loanCalculation;
      $this->M_a_calculationResults = $this->M_o_loanCalculation->fetch();

      return $this;
    }

    public function __toString(){
      return (string) @json_encode($this->jsonSerialize());
    }


    public function jsonSerialize() {
      $L_a_json = [
        'meta' => [
          'debtor'      => $this->M_o_loanCalculation->getDebtorDetails(),
          'calculation' => [
            'date_range' => [
              'start'  => $this->M_o_loanCalculation->getLoanDetails()['startdate'],
              'end'    => $this->M_o_loanCalculation->getCalculationEndDate(),
            ],
            'decimals' => (int) ini_get('precision'),
          ],
        ],
        'loans' => [],
      ];

      if(!empty(@$this->M_a_calculationResults)){
        foreach(array_keys(@$this->M_a_calculationResults) as $part){
          $L_a_details   = @$this->M_a_calculationResults[$part];
          $L_a_lpDetails = @$this->M_o_loanCalculation->getLoanPartDetails()[$part];
          $L_a_meta      = reset($L_a_details);
          $L_a_meta      = $L_a_meta['values'];

          $L_a_loanPart = [
            'meta' => [
              'initial'  => [
                'loan'     => [
                  'amount'   => (float) $L_a_meta['loan_amount'],
                  'currency' => (string) $L_a_meta['loan_currency'],
                  'type'     => (string) $L_a_lpDetails['type'],
                ],
                'interest' => [
                  'percentage' => (float) $L_a_meta['interest_percentage'],
                  'type'       => (string) $L_a_meta['interest_type'],
                ],
              ],
            ],
            'result' => [
              'date'     => $this->M_o_loanCalculation->getCalculationEndDate(),
              'currency' => $L_a_meta['loan_currency'],
              'debt'     => (float) ( end($this->M_a_calculationResults[$part])['values']['loan_amount'] + end($this->M_a_calculationResults[$part])['values']['interest_amount'] ),
              'loan'     => (float) end($this->M_a_calculationResults[$part])['values']['loan_amount'],
              'interest' => (float) end($this->M_a_calculationResults[$part])['values']['interest_amount'],
            ],
            'daily'  => [],
          ];

          if($this->M_b_withDaily === true){
            foreach($L_a_details as $mutation){
              $L_a_loanPart['daily'][$mutation['values']['reference_date']] = [
                'currency' => $mutation['values']['loan_currency'],
                'debt' => (float) $mutation['values']['debt_total'],
                'loan' => (float) $mutation['values']['loan_amount'],
                'interest' => (float) $mutation['values']['interest_amount'],
                'meta' => [
                  'interest' => [
                    'percentage' => (float) $mutation['values']['interest_percentage'],
                    'type'       => (string) $mutation['values']['interest_type'],
                  ],
                ],
                'mutations' => $mutation['mutations'],
              ];
            }
          }else{
            unset($L_a_loanPart['daily']);
          }

          $L_a_json['loans'][$part] = $L_a_loanPart;
        }
      }

      return $L_a_json;
    }

    public function serve($P_b_withDaily = null){
      if(!is_null($P_b_withDaily)){
        $this->setWithDaily($P_b_withDaily);
      }
      @header('Content-type: application/json; Charset=UTF-8');
      return $this->render();
    }

    public function setWithDaily($P_b_withDaily = true){
      $this->M_b_withDaily = (bool) $P_b_withDaily;
      return $this;
    }

    /**
     * Public Methods
     **/

    private function render(){
      return @json_encode($this->jsonSerialize());
    }

}