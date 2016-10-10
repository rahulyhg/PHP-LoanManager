<?php

use iPublications\Financial\Debtor;

use iPublications\Financial\Loan;
use iPublications\Financial\LoanPart;
use iPublications\Financial\LoanPartMutation;

use iPublications\Financial\LoanCalculation;
use iPublications\Financial\Render\Json;
use iPublications\Financial\Render\WebPage;

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'bootstrap.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

@session_start();

$data = null;
if(isset($_GET["url"]) && preg_match("@^http@", $_GET["url"]) && !preg_match("@localhost|^127|^10\.@i", trim($_GET["url"]))){
  $data = @file_get_contents($_GET["url"]);
}else{
  $data = @file_get_contents('php://input');
}

if($data){
  if(isset($_GET["web"])){
    $_SESSION['__data'] = $data;
  }
}

if(!empty($_SESSION['__data'])) $data = $_SESSION['__data'];

$till = 'today + 1 year';
if(isset($_GET['till']) && @strtotime($_GET["till"])){
  $till = $_GET["till"];
  if(!empty($_SESSION['__data'])) $_SESSION["__till"] = $till;
}

$daily = true;
if(isset($_GET["daily"])){
  $daily = (bool) (int) (string) $_GET["daily"];
  if(!empty($_SESSION['__data'])) $_SESSION["__daily"] = $daily;
}

$web = false;
if(isset($_GET["web"])){
  $web = (bool) (int) (string) $_GET["web"];
  if(!empty($_SESSION['__data'])) $_SESSION["__web"] = $web;
}

if(!empty($_SESSION['__data'])){
  if(isset($_SESSION['__web']))   $web   = $_SESSION['__web'];
  if(isset($_SESSION['__till']))  $till  = $_SESSION['__till'];
  if(isset($_SESSION['__daily'])) $daily = $_SESSION['__daily'];
}

$null = [];

if(!empty($data)){
        try {
                $loan        = Loan::constructFromJson($data);
                $calculation = new LoanCalculation($loan, date('Y-m-d', strtotime($till)));
		$object      = 'iPublications\\Financial\\Render\\'.($web ? 'WebPage' : 'Json');
                $output      = (new $object($calculation))->setWithDaily($daily)->serve();
                echo $output;
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



