<!DOCTYPE html>
<html>
<head>
    <title>Daily Balance Report - Travel & trip</title>
    <style>
        @page {
            margin: 100px 25px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #444;
            line-height: 1.5;
        }
        .header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 60px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            float: left;
        }
        .report-title {
            float: right;
            text-align: right;
            color: #7f8c8d;
        }
        .footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 40px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 9px;
            color: #999;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .dashboard {
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .card {
            width: 30%;
            float: left;
            background: #fdfdfd;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 15px;
            margin-right: 3%;
            text-align: center;
        }
        .card:last-child {
            margin-right: 0;
        }
        .card-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        .card-value {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        .chart-container {
            margin: 20px 0;
            text-align: center;
        }
        .chart-img {
            max-width: 100%;
            height: auto;
            border: 1px solid #eee;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #2c3e50;
            color: white;
            padding: 10px 5px;
            text-align: left;
            text-transform: uppercase;
            font-size: 10px;
        }
        td {
            padding: 8px 5px;
            border-bottom: 1px solid #eee;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .positive { color: #27ae60; }
        .negative { color: #c0392b; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header clearfix">
        <div class="company-name">Travel & trip</div>
        <div class="report-title">
            <div style="font-size: 16px; font-weight: bold;">Daily Balance Report</div>
            <div>Period: {{ $monthName }} {{ $year }}</div>
        </div>
    </div>

    <div class="footer">
        © {{ date('Y') }} Travel & trip | Generated on {{ now()->format('M d, Y H:i:s') }} | Page 1
    </div>

    <div class="dashboard clearfix">
        <div class="card">
            <div class="card-label">Total Credit</div>
            <div class="card-value">{{ number_format($totalCredit, 2) }}</div>
        </div>
        <div class="card">
            <div class="card-label">Total Debit</div>
            <div class="card-value">{{ number_format($totalDebit, 2) }}</div>
        </div>
        <div class="card">
            <div class="card-label">Net Balance</div>
            <div class="card-value bold {{ $finalBalance >= 0 ? 'positive' : 'negative' }}">
                {{ number_format($finalBalance, 2) }}
            </div>
        </div>
    </div>

    <div class="chart-container">
        <img src="{{ $chartUrl }}" class="chart-img" alt="Balance Trend Chart">
    </div>

    <div style="page-break-before: always;"></div>

    <h3>Detailed Transaction Summary</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th class="text-right">Txs</th>
                <th class="text-right">Credit</th>
                <th class="text-right">Debit</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}</td>
                    <td class="text-right">{{ $row->tx_count }}</td>
                    <td class="text-right">{{ number_format($row->total_credit, 2) }}</td>
                    <td class="text-right">{{ number_format($row->total_debit, 2) }}</td>
                    <td class="text-right bold">{{ number_format($row->balance, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px;">
        <h4>Report Methodology & Summary</h4>
        <p>This report provides a consolidated view of daily accounting activities for the period of <strong>{{ $monthName }} {{ $year }}</strong>. All values are represented in the base currency of the <strong>Travel & trip</strong> accounting system.</p>
        <ul>
            <li><strong>Total Credit:</strong> Aggregate of all incoming transactions during the period.</li>
            <li><strong>Total Debit:</strong> Aggregate of all outgoing transactions during the period.</li>
            <li><strong>Closing Balance:</strong> Final net position at the end of the reporting period.</li>
        </ul>
        <p><em>Note: This is an automatically generated system report.</em></p>
    </div>
</body>
</html>
