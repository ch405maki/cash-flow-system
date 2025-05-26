@php
function amountToWords($amount) {
    $whole = (int)$amount;
    $fraction = round(($amount - $whole) * 100);
    
    $words = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen'
    ];
    
    $tens = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 
        'Sixty', 'Seventy', 'Eighty', 'Ninety'
    ];
    
    // Nested function without use
    function convertLessThanOneThousand($number, $words, $tens) {
        if ($number < 20) {
            return $words[$number];
        }
        
        if ($number < 100) {
            return $tens[(int)($number / 10)] . 
                  ($number % 10 ? ' ' . $words[$number % 10] : '');
        }
        
        $hundreds = (int)($number / 100);
        $remainder = $number % 100;
        
        return $words[$hundreds] . ' Hundred' . 
              ($remainder ? ' ' . convertLessThanOneThousand($remainder, $words, $tens) : '');
    }
    
    if ($whole == 0) {
        $result = 'Zero';
    } elseif ($whole < 1000) {
        $result = convertLessThanOneThousand($whole, $words, $tens);
    } elseif ($whole < 1000000) {
        $thousands = (int)($whole / 1000);
        $remainder = $whole % 1000;
        
        $result = convertLessThanOneThousand($thousands, $words, $tens) . ' Thousand' . 
                ($remainder ? ' ' . convertLessThanOneThousand($remainder, $words, $tens) : '');
    } else {
        return 'Amount exceeds conversion limit';
    }
    
    $result = trim($result) . ' Pesos';
    
    if ($fraction > 0) {
        $result .= ' and ' . convertLessThanOneThousand($fraction, $words, $tens) . ' Centavos';
    }
    
    return $result;
}
@endphp

<!DOCTYPE html>
<!--SAMPLE REPORT LAYOUT-->
<html>
<head>
<title>Cash Voucher</title>
<style>
  body {
    font-family: sans-serif;
    margin: 20px;
    font-size: 0.9em;
  }
  table {
    width: 100%;
    border-collapse: collapse;
  }
  td {
    padding: 5px;
    vertical-align: top;
  }
  .header {
    text-align: center;
    font-weight: bold;
    margin-bottom: 20px;
  }
  .section-title {
    font-weight: bold;
    margin-top: 15px;
    margin-bottom: 5px;
  }
  .line-item {
    border-bottom: 1px solid black;
    padding-bottom: 3px;
    margin-bottom: 3px;
  }
  .signature-line {
    border-bottom: 1px solid black;
    width: 70%;
    margin-top: 50px;
  }
  .footer-notes {
    margin-top: 50px;
    font-size: 0.8em;
  }
  .align-right {
    text-align: right;
  }
  .total-box {
    border: 1px solid black;
    padding: 5px;
    width: 150px;
    text-align: right;
    font-weight: bold;
  }
</style>
</head>
<body>

  <div class="header">
    ARELLANO LAW FOUNDATION, INC. <br>
    {{ ucfirst($voucher-> type) }} Voucher
  </div>

  <table>
    <tr>
      <td width="70%">
        Voucher No.: {{ $voucher-> voucher_no }} <br>
        Voucher Date: {{ date('F j, Y', strtotime($voucher->voucher_date)) }}
      </td>
      
    </tr>
  </table>

  <br>

  <table>
    <tr>
      <td>Payee: {{ $voucher-> payee }} </td>
    </tr>
    <tr>
      <td>Check Payable to: {{ $voucher-> check_payable_to }}</td>
    </tr>
    <tr>
      <td>Check No./ Date: {{ date('F j, Y', strtotime($voucher->check_date)) }} </td>
    </tr>
  </table>
  <div class="line-item"></div>
  <div class="line-item"></div>
  <div style="display: flex; justify-content: space-between;">
    <div>Payment for {{ date('F j, Y', strtotime($voucher->payment_date)) }}</div>
    <div>₱{{ number_format($voucher->details->sum('amount'), 2) }}</div>
  </div>
  

<div class="line-item"></div>
<div class="line-item"></div>
<!-- ACCOUNT CHARGED section-->
<div class="section-title">ACCOUNT CHARGED</div>
<table>
    @if($isSalary)
        <!-- Salary Voucher - Detailed Breakdown -->
        <thead>
            <tr>
                <th style="text-align: left; width: 70%;">Account Title</th>
                <th style="text-align: right; width: 30%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($voucher->details as $detail)
            <tr>
                <td style="text-align: left;">{{ $detail->account->account_title ?? 'N/A' }}</td>
                <td style="text-align: right;">₱{{ number_format($detail->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td style="text-align: right;"><strong>TOTAL AMOUNT:</strong></td>
                <td style="text-align: right; border: 1px solid black; padding: 5px; font-weight: bold;">₱{{ number_format($voucher->details->sum('amount'), 2) }}</td>
            </tr>
        </tfoot>
    @else
        <!-- Non-Salary Voucher - General Charges -->
        <tr>
            <td style="text-align: left; width: 70%;">GENERAL CHARGES</td>
            <td style="text-align: right; width: 30%;">₱{{ number_format($voucher->check_amount, 2) }}</td>
        </tr>
        @for($i = 0; $i < 5; $i++)
        <tr>
            <td><br></td>
            <td></td>
        </tr>
        @endfor
        <tr>
            <td style="text-align: right;"><strong>TOTAL:</strong></td>
            <td style="text-align: right; border: 1px solid black; padding: 5px; font-weight: bold;">₱{{ number_format($voucher->check_amount, 2) }}</td>
        </tr>
    @endif
</table>

  <br><br>
  <div class="line-item"></div>
  <div class="line-item"></div>
  <div class="section-title">RECOMMENDING APPROVAL:</div>
  <br>

  <div> 
    {{ $roles['approved_by']->first_name }} 
    @if($roles['approved_by']->middle_name)
    {{ strtoupper(substr($roles['approved_by']->middle_name, 0, 1)) }}. 
    {{ $roles['approved_by']->last_name }}
    @endif
  </div>
  <div><strong>Director, Accounting</strong></div>

  <br>

  <table>
      <tr>
          <td width="50%">
              <div class="section-title" style="text-transform: uppercase;">{{ $voucher->status }}: </div>
              <br><br>
              <div>
                  {{ $roles['exec_director']->first_name }} 
                  @if($roles['exec_director']->middle_name)
                  {{ strtoupper(substr($roles['exec_director']->middle_name, 0, 1)) }}. 
                  {{ $roles['exec_director']->last_name }}
                  @endif
              </div>
              <div><strong>Executive Director</strong></div>
          </td>
          <td width="50%" style="vertical-align: top;">
              I hereby certify to have received from the ARELLANO LAW FOUNDATION the sum of 
              <strong>{{ amountToWords($voucher->check_amount) }} Pesos</strong>
              (₱{{ number_format($voucher->check_amount, 2) }}) as payment for the account specified above.
              <br><br><br><br><br>
              <div><strong>{{ strtoupper("Payee Signature:") }}</strong></div>
          </td>
      </tr>
  </table>

  <br><br>



  <br><br>

  <table>
    <tr>
      <td width="33%">
        PREPARED BY:
        <div class="line-item"> {{ $voucher->user->first_name }} {{ $voucher->user->middle_name }} {{ $voucher->user->last_name }} - {{ date('F j, Y') }}</div>
      </td>
      <td width="33%">
        AUDITED BY:
        <div class="line-item">Name of Person</div>
      </td>
      <td width="33%">
        AUDITED BY:
        <div class="line-item"> ₱{{ $voucher-> check_amount }} </div>
      </td>
    </tr>
  </table>

</body>
</html>