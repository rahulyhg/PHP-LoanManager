<?php

use iPublications\Financial\Debtor;

use iPublications\Financial\Loan;
use iPublications\Financial\LoanPart;
use iPublications\Financial\LoanPartMutation;

use iPublications\Financial\LoanCalculation;
use iPublications\Financial\Render\Json;

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'bootstrap.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

$data = null;
if(isset($_GET["url"]) && preg_match("@^http@", $_GET["url"]) && !preg_match("@localhost|^127|^10\.@i", trim($_GET["url"]))){
  $json = @file_get_contents($_GET["url"]);
  if($json) $data = $json;
}else{
  $data = @file_get_contents('php://input');
}

$till = 'today + 1 year';
if(isset($_GET['till']) && @strtotime($_GET["till"])){
  $till = $_GET["till"];
}

$daily = true;
if(isset($_GET["daily"])){
  $daily = (bool) (int) (string) $_GET["daily"];
}

$null = [];

if(!empty($data)){
        try {
                $loan = Loan::constructFromJson($data);
                $calculation = new LoanCalculation($loan, date('Y-m-d', strtotime($till)));
                $json = (new Json($calculation))->setWithDaily($daily)->serve();
                echo $json;
                exit(0);
        }
        catch (\Exception $e){
                //
                $null['error'] = $e->getMessage();
        }
}

header('Content-type: application/json; Charset=UTF-8');
echo json_encode($null);
exit(0);



