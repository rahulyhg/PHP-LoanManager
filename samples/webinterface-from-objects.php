<?php

use iPublications\Financial\Debtor;

use iPublications\Financial\Loan;
use iPublications\Financial\LoanPart;
use iPublications\Financial\LoanPartMutation;

use iPublications\Financial\LoanCalculation;
use iPublications\Financial\Render\WebPage;

// ini_set('precision', 50);
// ini_set('serialize_precision',50);

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'bootstrap.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

  $loan = new Loan(
      $debtor = new Debtor('Wietse Wind'),
      [
          $loan_1 =   new LoanPart(
                          (new LoanPartMutation())
                              ->setInterestType(LoanPartMutation::INTEREST_COMPOUND)
                              ->setAmount(6000, 'EUR')
                              ->setInterestAmount(423.4312331324)
                              ->setInterestPercentage(5.6),
                          LoanPart::COMPONENT_LOAN,
                          'Deel 1'
                      ),

          $loan_2 =   new LoanPart(
                          (new LoanPartMutation())
                              ->setInterestType(LoanPartMutation::INTEREST_SIMPLE)
                              ->setAmount(100, 'EUR')
                              ->setInterestPercentage(2.3),
                          LoanPart::COMPONENT_GRANT,
                          'Deel 2'
                      ),
      ],
      '2016-01-01'
  );

  /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

 $loan_1->addMutation(
     (new LoanPartMutation('2016-02-01'))
         ->setAmountMutation(1000, 'EUR')
 );

 $loan_1->addMutation(
     (new LoanPartMutation('2016-03-01'))
         ->setAmountMutation(1000, 'EUR')
 );

 $loan_1->addMutation(
     (new LoanPartMutation('2016-12-10'))
         ->setAmountMutation(-1000, 'EUR')
 );

 $loan_1->addMutation(
     (new LoanPartMutation('2016-12-20'))
         ->setInterestAmountMutation(-100, 'EUR')
         ->setAmountMutation(-200, 'EUR')
 );

//
 // $loan_1->addMutation(
 //     (new LoanPartMutation('2016-04-02'))
 //         ->setInterestPercentage(4.2)
 //         // ->setInterestType(LoanPartMutation::INTEREST_COMPOUND)
 // );
//
 // $loan_1->addMutation(
 //     (new LoanPartMutation('2016-01-03'))
 //         ->setInterestType(LoanPartMutation::INTEREST_COMPOUND)
 //         // ->setAmountMutation(-1000, 'EUR')
 //         ->setInterestPercentage(2.9)
 // );
//
 // $loan_1->addMutation(
 //     (new LoanPartMutation('2016-09-03'))
 //         ->setInterestPercentage(1.9)
 // );
//
 // $loan_1->addMutation(
 //     (new LoanPartMutation('2020-01-01'))
 //         ->setInterestPercentage(2.0)
 // );

  /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

  $loan_2->addMutation(
      (new LoanPartMutation('2016-04-03'))
          ->setInterestPercentage(6.8)
  );

  $loan_2->addMutation(
      (new LoanPartMutation('2016-02-01'))
          ->setInterestPercentage(6.9)
          ->setAmountMutation(1000)
  );

  $loan_2->addMutation(
      (new LoanPartMutation('2016-06-01'))
          ->setInterestPercentage(4.2)
          ->setAmountMutation(1000)
  );

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

$calculation = new LoanCalculation($loan, '2016-12-31');

$html = (new WebPage($calculation))->setWithDaily(true)->serve();
                  // Method 'serve' accepts one argument (bool)
                  // 'false' (or set with 'setWithDaily') with "Daily Log"
echo $html;
