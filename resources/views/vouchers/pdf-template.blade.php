<!DOCTYPE html>
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
    {{ $voucher-> type }} Voucher
  </div>

  <table>
    <tr>
      <td width="70%">
        Voucher No.: {{ $voucher-> voucher_no }} <br>
        Voucher Date: {{ $voucher-> voucher_date }}
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
      <td>Check No./ Date: {{ $voucher-> check_date }} </td>
    </tr>
    <tr>
      <td>Payment for {{ $voucher-> payment_date }} </td>
    </tr>
  </table>

  <br>

  <div class="section-title">ACCOUNT CHARGED</div>
  <table>
    <tr>
      <td>GENERAL CHARGES</td>
      <td class="align-right"> {{ $voucher-> check_amount }} </td>
    </tr>
    <tr>
      <td><br></td>
      <td></td>
    </tr>
    <tr>
      <td><br></td>
      <td></td>
    </tr>
    <tr>
      <td><br></td>
      <td></td>
    </tr>
    <tr>
      <td><br></td>
      <td></td>
    </tr>
    <tr>
      <td><br></td>
      <td></td>
    </tr>
    <tr>
      <td class="align-right">TOTAL: </td>
      <td class="total-box">{{ $voucher-> check_amount }} </td>
    </tr>
  </table>

  <br><br>

  <div class="section-title">RECOMMENDING APPROVAL:</div>
  <div class="line-item">Ma. Jasmin P. Horlina </div>
  <div>Director, Accounting </div>

  <br>

  <div class="section-title"> {{ $voucher-> status }}</div>
  <div class="line-item">Gabriel P. Dela Peña </div>
  <div>Executive Director </div>

  <br><br>

  <div class="line-item"> {{ $voucher-> check_amount }} </div>
  <div>
    I hereby certify to have received from the ARELLANO LAW FOUNDATION the sum of Two Thousand One Hundred Pesos ({{ $voucher-> check_amount }}) as payment for the account specified above.
  </div>

  <br><br>

  <div class="line-item">Payee Signature: </div>

  <br><br>

  <table>
    <tr>
      <td width="33%">
        PREPARED BY:
        <div class="line-item">JOMARI-05/23/2025 [cite: 5]</div>
      </td>
      <td width="33%">
        AUDITED BY:
        <div class="line-item"></div>
      </td>
      <td width="33%">
        <div class="line-item"> {{ $voucher-> check_amount }} </div>
      </td>
    </tr>
  </table>

</body>
</html>