<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Balance Report</title>
</head>
<body>
    <p>Hello,</p>

    <p>Please find attached the Daily Balance Report for {{ $monthName }} {{ $year }}.</p>

    <p>
        <strong>Total Credit:</strong> {{ number_format($totalCredit, 2) }}<br>
        <strong>Total Debit:</strong> {{ number_format($totalDebit, 2) }}<br>
        <strong>Final Balance:</strong> {{ number_format($finalBalance, 2) }}
    </p>

    <p>Regards,<br>{{ config('app.name') }}</p>
</body>
</html>
