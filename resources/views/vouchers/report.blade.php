<!DOCTYPE html>
<html>
<head>
    <title>Voucher Report - {{ $voucher->voucher_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        @page {
            margin: 1cm;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.8em;
        }
        /* Add other print-specific styles here */
    </style>
</head>
<body>
    <div class="header">
        <h1>Voucher Report</h1>
        <h2>{{ $voucher->voucher_no }}</h2>
    </div>
    
    <div class="content">
        <!-- Your actual voucher report content here -->
        <p>Date: {{ $voucher->date }}</p>
        <p>Amount: {{ $voucher->amount }}</p>
        <!-- Add more fields as needed -->
    </div>
    
    <div class="footer">
        Generated on {{ now()->format('Y-m-d H:i:s') }}
    </div>
</body>
</html>