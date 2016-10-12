<?php namespace iPublications\Financial\Render;

use iPublications\Financial\Loan;
use iPublications\Financial\LoanPart;
use iPublications\Financial\LoanPartMutation;
use iPublications\Financial\LoanCalculation;
use \Exception as Exception;

class WebPage {

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
        return '(object) ' . __CLASS__;
    }

    /**
     * Public Methods
     **/

    public function serve($P_b_withDaily = null){
        if(!is_null($P_b_withDaily)){
          $this->setWithDaily($P_b_withDaily);
        }
        $L_s_head = '
            <!--  iPublications Software, NL, https://ipublications.nl  -->
            <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
            <html lang="nl">
            <head>
                <meta charset="utf-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
                <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
                <meta name="author" content="iPublications Software, NL">
                <title>iPublications\\Financial\\Render\\WebPage</title>
                <link rel="icon" href="https://ipublications.nl/static/favicon.ico">
                <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
                <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
                <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-T8Gy5hrqNKT+hzMclPo118YTQO6cYprQmhrYwIiQ/3axmI1hQomh7Ud2hPOy8SP1" crossorigin="anonymous">
                <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
                <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
                <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue/1.0.26/vue.min.js" integrity="sha256-kGI69nguxQ6ywqsMUr42eeqd32vILSIe+ZG+WYkGX0E=" crossorigin="anonymous"></script>
                <style>
                    /* Navbar */
                    body {
                        padding: 20px;
                    }

                    .navbar {
                        margin-bottom: 20px;
                    }

                    /* Sticky Footer */
                    html {
                        position: relative;
                        min-height: 100%;
                    }
                    .footer {
                      width: 100%;
                      padding-top: 5px;
                      padding-bottom: 15px;
                      line-height: 18px;
                      text-align: center;
                      background-color: #fff;
                    }
                    .footer p {
                        padding: 0;
                        margin: 0;
                    }
                    pre {
                      padding: 1px 5px;
                      margin: 0;
                      font-size: 13px;
                      line-height: 15px;
                    }
                    pre span.text-grey {
                      color: #999;
                      font-size: 11px;
                    }
                </style>
            </head>

            <body class="default" role="document">

                <div class="x-container">

                  <!-- Static navbar -->
                  <nav class="navbar navbar-default">
                    <div class="container-fluid">
                      <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                          <span class="sr-only">Toggle navigation</span>
                          <span class="icon-bar"></span>
                          <span class="icon-bar"></span>
                          <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="?">Financial \\ LoanCalculation</a>
                      </div>
                      <div id="navbar" class="navbar-collapse collapse">
                        <ul class="nav navbar-nav">
                          <li class="'.(!isset($_GET["part"]) && !isset($_GET["about"]) ? 'active' : '').'"><a href="?"><i class="fa fa-home"></i> Home</a></li>

                          ' . $this->getLoanPartButtons() . '

                          <li class="dropdown hidden">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Dropdown <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                              <li><a href="#">Action</a></li>
                              <li><a href="#">Another action</a></li>
                              <li><a href="#">Something else here</a></li>
                              <li role="separator" class="divider"></li>
                              <li class="dropdown-header">Nav header</li>
                              <li><a href="#">Separated link</a></li>
                              <li><a href="#">One more separated link</a></li>
                            </ul>
                          </li>

                        </ul>
                        <ul class="navbar-right hidden-sm hidden-xs list-unstyled" style="padding-bottom: 0; margin-bottom: 0;">
                          <li style="padding-top: 8px; margin-right: -7px; padding-bottom: 0; margin-bottom: 0;">
                            <a href="?json=true&force_download=true" style="margin-bottom: 0;" class="btn pull-right btn-success"><i class="fa fa-download"></i> Download JSON</a>
                            <a href="?json=true" style="margin-right: 4px;" target="_blank" style="margin-bottom: 0;" class="btn pull-right btn-primary"><i class="fa fa-search"></i> JSON definition</a>
                          </li>
                        </ul>
                      </div><!--/.nav-collapse -->
                    </div><!--/.container-fluid -->
                  </nav>

                  <!-- Main component for a primary marketing message or call to action -->
                  <div class="x-jumbotron">

                  <!-- End header, Start content -->
        ';

        $L_s_foot = '
                  <!-- End content, Start footer -->

                  </div>
                </div> <!-- /container -->

                <footer class="footer">
                  <div class="container-fluid">
                    <hr />
                    <p class="text-muted">
                        <small>
                            <a href="https://ipublications.nl" target="_blank">iPublications Software</a> &dash;
                            <code>iPublications\\Financial\\Render\\WebPage</code>
                            render of
                            <code>iPublications\\Financial\\LoanCalculation</code>
                            component.
                        </small>
                    </p>
                  </div>
                </footer>
            </body>
            </html>
        ';

        $L_s_content = '';
        if(isset($_GET["json"])){
          @header('Content-type: application/json; Charset=UTF-8');
          if(isset($_GET["force_download"])){
            @header('Content-Disposition: attachment; filename="Loan_Serialized.json"');
          }
          return @json_encode($this->M_o_loanCalculation);
        }else{
          if(isset($_GET["part"]) && isset($this->M_a_calculationResults[$_GET["part"]])){
            $L_s_content .= $this->renderLoanPart($_GET["part"]);
          }else{
            $L_s_content .= $this->getRouted();
          }
        }

        @header('Content-type: text/html; Charset=UTF-8');
        return trim(
          $L_s_head       . PHP_EOL .
          $L_s_content    . PHP_EOL .
          $L_s_foot       . PHP_EOL
        );
    }

    public function getEmbeddable($part = null){
      if(!is_null($part) && is_string($part) && isset($this->M_a_calculationResults[$part])){
        $L_a_details = @$this->M_a_calculationResults[$part];
        $L_s_html = '
          <table class="table table-condensed">
            <thead>
              <tr>
                <th class="text-left">Date</th>
                <th class="text-right">Loan</th>
                <th class="text-right">Interest</th>
                <th class="text-left">Interest details</th>
                <th class="text-right">Debt</th>
                <th class="text-left">Mutation(s)</th>
              </tr>
            </thead>
            <tbody>
        ';
        foreach($L_a_details as $mutation){
          $L_s_html .= '
                <tr>
                  <td class="text-left"><b>' . $mutation['values']['reference_date'] . '</b></td>
                  <td class="text-right"><b><code>' . $mutation['values']['loan_amount'] . '</code></b> <code>' . $mutation['values']['loan_currency'] . '</code></td>
                  <td class="text-right"><code>' . round($mutation['values']['interest_amount'],40) . '</code> <code>' . $mutation['values']['loan_currency'] . '</code></td>
                  <td class="text-left"><code>' . $mutation['values']['interest_percentage'] . '</code>% <code>' . $mutation['values']['interest_type'] . '</code></td>
                  <td class="text-right"><b><code>' . round($mutation['values']['debt_total'],40) . '</code></b></td>
                  <td class="text-left"><pre>' . preg_replace_callback("@\[(.+?)\] => @ms", function($e){
                    return "<span class=\"text-grey\">" . $e[1].str_repeat(" ",20-strlen($e[1])) . ":</span> ";
                  }, preg_replace("@^[\t ]+@ms", "", substr(print_r($mutation['mutations'],1),7,-2))) . '</pre></td>
                </tr>
          ';
        }
        $L_s_html .= '
            </tbody>
          </table>
        ';
      }else{
        $L_s_html = '<p class="alert alert-danger">Unknown \\iPublications\\Financial\\LoanPart <code>(arg[0], $part)</code></p>';
      }

      return $L_s_html;
    }

    public function setWithDaily($P_b_withDaily = true){
      $this->M_b_withDaily = (bool) $P_b_withDaily;
      return $this;
    }

    /**
     * Private Methods
     **/

    private function renderLoanPart($part){
      $L_a_details   = @$this->M_a_calculationResults[$part];
      $L_a_lpDetails = @$this->M_o_loanCalculation->getLoanPartDetails()[$part];
      $L_a_meta      = reset($L_a_details);
      $L_a_meta      = $L_a_meta['values'];

      $L_s_intro     = '';
      if(!empty($L_a_details)){
        $L_s_intro .= '
          <h1>LoanPart details: <q><b>' . $part . '</b></q></h1>
          <h3>Meta-information <small><b>INITIAL</b></small></h3>
          <table class="table table-condensed table-striped">
            <thead>
              <tr>
                <th width="20%">Object</th>
                <th width="30%">Key</th>
                <th>Value</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><b>Debtor</b></td>
                <td>Identifier</td>
                <td><q><code>' . $this->M_o_loanCalculation->getDebtorDetails()['identifier'] . '</code></q></td>
              </tr>
              <tr>
                <td></td>
                <td>UID</td>
                <td><q><code>' . $this->M_o_loanCalculation->getDebtorDetails()['uid'] . '</code></q></td>
              </tr>
              <tr style="border-top: 2px solid black;">
                <td><b>Loan</b></td>
                <td>startDate</td>
                <td><q><code>' . $this->M_o_loanCalculation->getLoanDetails()['startdate'] . '</code></q></td>
              </tr>
              <tr>
                <td></td>
                <td>Amount, Currency</td>
                <td>
                  <q><code>' . number_format($L_a_meta['loan_amount'],2) . '</code></q>
                  <q><code>' . $L_a_meta['loan_currency'] . '</code></q>
                  <small style="color: #999;">(rounded @ 2 decimals)</small>
                </td>
              </tr>
              <tr>
                <td></td>
                <td>Type</td>
                <td><q><code>' . $L_a_lpDetails['type'] . '</code></q></td>
              </tr>
              <tr style="border-top: 2px solid black;">
                <td><b>Interest</b></td>
                <td>startDate</td>
                <td><q><code>' . $this->M_o_loanCalculation->getLoanDetails()['startdate'] . '</code></q></td>
              </tr>
              <tr>
                <td></td>
                <td>Amount, Currency</td>
                <td>
                  <q><code>' . number_format(@$L_a_lpDetails['start']['interestamount'], 2) . '</code></q>
                  <q><code>' . $L_a_meta['loan_currency'] . '</code></q>
                  <small style="color: #999;">(rounded @ 2 decimals)</small>
                </td>
              </tr>
              <tr>
                <td></td>
                <td>Percentage</td>
                <td><q><code>' . $L_a_meta['interest_percentage'] . '</code></q></td>
              </tr>
              <tr>
                <td></td>
                <td>Type</td>
                <td><q><code>' . $L_a_meta['interest_type'] . '</code></q></td>
              </tr>
              <tr style="border-top: 2px solid black;">
                <td><b>Calculation</b></td>
                <td>Date range</td>
                <td><code>' . $this->M_o_loanCalculation->getLoanDetails()['startdate'] . '</code> - <code>' . $this->M_o_loanCalculation->getCalculationEndDate() . '</code></td>
              </tr>
              <tr>
                <td></td>
                <td>Calculation decimals</td>
                <td>'.ini_get('precision').'</td>
              </tr>
            </tbody>
          </table>

          <h3>Results <small><b>AFTER CALCULATION</b></small></h3>
          <table class="table table-condensed table-striped">
            <thead>
              <tr>
                <th width="20%">Object</th>
                <th width="30%">Key</th>
                <th>Value</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><b>Debt <small> @ <code>' . $this->M_o_loanCalculation->getCalculationEndDate() . '</code></small></b></td>
                <td>Loan</td>
                <td>
                  <q><code>' . number_format(end($this->M_a_calculationResults[$part])['values']['loan_amount'],2) . '</code></q>
                  <q><code>' . $L_a_meta['loan_currency'] . '</code></q>
                </td>
              </tr>
              <tr>
                <td></td>
                <td>Interest</td>
                <td>
                  <q><code>' . number_format(end($this->M_a_calculationResults[$part])['values']['interest_amount'],2) . '</code></q>
                  <q><code>' . $L_a_meta['loan_currency'] . '</code></q>
                </td>
              </tr>
              <tr>
                <td></td>
                <td>Total (sum)</td>
                <td>
                  <q><code>' . number_format(end($this->M_a_calculationResults[$part])['values']['loan_amount'] + end($this->M_a_calculationResults[$part])['values']['interest_amount'],2) . '</code></q>
                  <q><code>' . $L_a_meta['loan_currency'] . '</code></q>
                </td>
              </tr>
              <tr style="border-top: 2px solid black;">
                <td><b>Mutation Totals</b></td>
                <td>Date range</td>
                <td><code>' . $this->M_o_loanCalculation->getLoanDetails()['startdate'] . '</code> - <code>' . $this->M_o_loanCalculation->getCalculationEndDate() . '</code></td>
              </tr>
              <tr>
                <td></td>
                <td>Loan</td>
                <td>
                  Payout   <q><code>'.number_format(@$this->M_o_loanCalculation->getMutationTotals($part)['loan']['payout'],2).'</code></q> -
                  Receive  <q><code>'.number_format(@$this->M_o_loanCalculation->getMutationTotals($part)['loan']['receive'],2).'</code></q>
                </td>
              </tr>
              <tr>
                <td></td>
                <td>Interest</td>
                <td>
                  Increase <q><code>'.number_format(@$this->M_o_loanCalculation->getMutationTotals($part)['interest']['increase'],2).'</code></q> -
                  Receive  <q><code>'.number_format(@$this->M_o_loanCalculation->getMutationTotals($part)['interest']['receive'],2).'</code></q>
                </td>
              </tr>
            </tbody>
          </table>
          <h3>Daily Mutation Log <small><b>INITIAL - AFTER CALCULATION</b></small></h3>
        ';
        if($this->M_b_withDaily === true){
          $L_s_intro .= $this->getEmbeddable($part);
        }else{
          $L_s_intro .= '<p class="alert alert-danger text-center">No mutation log (withDaily = false)</p>';
        }
      }
      return $L_s_intro;
    }

    private function getRouted(){
        return '
            <h1>Loan Calculaton</h1>
            <p>
                Please select a Loan (part) to review the financial details of the Loan.
            </p>
            <ul>
              ' . $this->getLoanPartButtons(false) . '
            </ul>
        ';
    }

    private function getLoanPartButtons($showIcon=true){
        $L_s_html = '';
        foreach(array_keys($this->M_a_calculationResults) as $loanPart){
            $L_s_html .= '
                <li class="' . ((isset($_GET["part"]) && $_GET["part"] == $loanPart ? 'active' : '')) . '"><a href="?part='.urlencode($loanPart).'"><b>' . ($showIcon ? '<i class="fa fa-money"></i>' : '') . ' ' . $loanPart . '</b></a></li>
            ';
        }
        return $L_s_html;
    }

}