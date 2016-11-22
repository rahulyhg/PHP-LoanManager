<?php

use iPublications\Financial\Debtor;

use iPublications\Financial\Loan;
use iPublications\Financial\LoanPart;
use iPublications\Financial\LoanPartMutation;

use iPublications\Financial\LoanCalculation;
use iPublications\Financial\Render\WebPage;

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'bootstrap.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

$data = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'Loan_Serialized.json');
// $data = file_get_contents('http://localhost/interest/samples/webinterface-from-objects?json=true');
$loan = Loan::constructFromJson($data);

$calculation = new LoanCalculation($loan, '2017-12-31');

$html = (new WebPage($calculation))->setWithDaily(true)->serve();
                  // Method 'serve' accepts one argument (bool)
                  // 'false' (or set with 'setWithDaily') with "Daily Log"
echo $html;
