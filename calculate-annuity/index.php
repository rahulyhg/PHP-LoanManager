<?php

@include('Financial_AnnuityLoan.php');

if(!isset($_GET["monthcount"],$_GET["interest"],$_GET["debt"])){
    header('Content-type: text/plain; Charset=UTF-8');
    echo "Enter param: monthcount, interest, debt";
    exit(1);
}

$periodsPerAnnual = 12;
$monthCount = (int) $_GET["monthcount"];
$interestPct = ((float) $_GET["interest"]);
$debt = (float) $_GET["debt"];
$respitePeriods = (int) @$_GET["respitePeriods"];

$v = new Financial_AnnuityLoan($periodsPerAnnual, $monthCount, $interestPct/100, $debt, $respitePeriods);

$out = [
    '_meta' => [
      'periods_per_year' => $periodsPerAnnual,
        'month_count'      => $monthCount,
        'interest_pct'     => $interestPct,
        'debt'             => $debt
    ],
    'monthly_pay' => $v->monthlyPayment(),
    'specification' => $v->periodSpecification(),
];

header('Content-type: application/json; Charset=UTF-8');
echo json_encode($out);
