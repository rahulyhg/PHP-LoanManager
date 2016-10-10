# PHP LoanManager
###### _Wietse Wind, iPublications Software, 2016_

Allows managing and calculating **Loans** using only **mutations**. Mutations don't contain absolute values, besides percentages and absolute amounts (payout / receive). 

## Usage

Please check the `samples` folder. When using the WebInterface, use GET-parameters to set data in a PHP session to debug. Sample URL:

`index.php?till=2016-W03&web=0&daily=0&url=https://raw.githubusercontent.com/iPublications/PHP-LoanManager/master/samples/assets/Loan_Serialized.json`

#### Parameters

- `till=` (string, strtotime)
End date calculation render
- `web=` (int, 0/1)
Display WebInterface (HTML) if `1`, or JSON feed if `0`
- `daily=` (int, 0/1) 
Render the daily mutations if `1`
- `url=` (string, file_get_contents)
Enter the remote URL containing a JSON serialized `Loan` (see _Short description_ below). Instead of an URL, the HTTP POST body can be used (`php://input`) when rendering JSON output (`web=0`)

## Short description

Use either PHP code or a JSON serialized `Loan` to set-up loans. 

A `Loan` contains one or more _LoanParts_. Each `LoanPart` requires an initial `LoanPartMutation` with the initial values. **This is the only time the `setAmount` method can be used.** Once the `LoanPart` is constructed, all new `LoanPartMutation` (set using the `addMutation` method on the `LoanPart`) records can only **mutate** amounts, using the `setAmountMutation` method.

To calculate the `Loan` over time, use the `LoanCalculation` object. This objects requires a `Loan` object and an enddate. 

To calculate and output the calculation, use the `Render\Json` class, or the `Render\WebPage` class.

## Todo

- Journalizing (performance)
- Managing multiple currencies
  - Calculate currency using exchange rates

# License

GPLv3 - Using this class? We'd appreciate it if you [inform us](https://ipublications.nl/contact).
