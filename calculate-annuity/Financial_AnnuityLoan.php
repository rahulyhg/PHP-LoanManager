<?php
/* 
http://www.dotvoid.com/2008/10/calculating-annuity-in-php/ 
*/

/**
 * @version 1.0
 * @author Danne Lundqvist, http://www.dotvoid.com
 */
 
/**
 * Class Financial_AnnuityLoan
 */
class Financial_AnnuityLoan {
 
  /**
   * Number of periods per year
   * @var int
   */
  private $aP;
 
  /**
   * Total number of periods
   * @var int
   */
  private $tP;
 
  /**
   * Yearly interest rate in decimal
   * @var float
   */
  private $rate;
 
  /**
   * Debt amount
   * @var float
   */
  private $amount;
 
  /**
   * Monthly payment
   * @var float
   */
  private $monthlyPayment;
 
  /**
   * Period specification
   * @var array
   */
  private $periodSpec;
 
  /**
   * Constructor for Annuity
   *
   * @param int $annualPeriods Number of periods per year
   * @param int $totalPeriods Total number of periods
   * @param float $interestRate Interest rate in decimal notation
   * @param float $amount Total amount of debt
   */
  public function __construct($annualPeriods, $totalPeriods, $interestRate, $amount) {
    $this->aP = $annualPeriods;
    $this->tP = $totalPeriods;
    $this->rate = $interestRate;
    $this->amount = $amount;
 
    $this->monthlyPayment = null;
    $this->periodSpecification = null;
  }
 
  /**
   * Calculate monthly payment for annuity loan
   *
   * @return float
   */
  public function monthlyPayment() {
    if ($this->monthlyPayment != null) {
      return $this->monthlyPayment;
    }
 
    $i = 1 / (1 + ($this->rate / $this->aP));
    $this->monthlyPayment = ((1 - $i) * $this->amount) / ($i * (1 - pow($i, $this->tP)));
    return $this->monthlyPayment;
  }
 
  /**
   * Calculate actual debt, interest and amount to pay each period
   *
   * Calculates the actual interest, amortization, and remaining debt for each period.
   * The method return an array with associative arrays, each with period, interest,
   * payment and debt elements.
   *
   * @return array
   */
  public function periodSpecification() {
    if ($this->periodSpec != null) {
      return $this->periodSpec;
    }
 
    $pmt = $this->monthlyPayment();
    $periodrate = $this->rate / $this->aP;
    $debt = $this->amount;
 
    $val = array();
    for ($i = 0; $i < $this->tP; $i++) {
      $interest = $debt * $periodrate;
      $payment = $pmt - $interest;
      $debt -= $payment;
 
      $val[] = array('period' => $i + 1,
                     'total' => round($interest + $payment, 2),
                     'interest' => round($interest, 2),
                     'payment' => round($payment, 2),
                     'debt' => round($debt, 2));
    }
 
    $this->periodSpec = $val;
    return $val;
  }
}
