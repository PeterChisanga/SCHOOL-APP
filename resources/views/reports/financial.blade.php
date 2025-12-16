<!DOCTYPE html>
<html>
<head>
    <title>Financial Report - {{ $termLabel }} {{ $yearLabel }}</title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .center-text { text-align: center; }
        h1, h2, h3, p { margin: 0; }
        .school-logo {
            display: block;
            margin: 0 auto 10px;
            width: 90px;
            height: auto;
            object-fit: cover;
        }
        .header-info {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #1e40af;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .total-row {
            background-color: #e6f3ff;
            font-weight: bold;
            font-size: 15px;
        }
        .grand-total {
            background-color: #1e40af;
            color: white;
            font-size: 18px;
        }
        .profit { color: green; font-weight: bold; }
        .loss { color: red; font-weight: bold; }
        .highlight { background-color: #fff8e1; }
    </style>
</head>
<body>

    <!-- SCHOOL HEADER -->
    <div class="center-text header-info">
        @if($school->photo && file_exists(public_path('storage/' . $school->photo)))
            <img src="{{ public_path('storage/' . $school->photo) }}" alt="School Logo" class="school-logo">
        @endif

        <h1 style="font-size: 28px; color: #1e40af;">{{ $school->name }}</h1>
        <p><strong>Motto:</strong> {{ $school->motto ?? 'Excellence in Education' }}</p>
        <p>{{ $school->address ?? 'Zambia' }}</p>
        <p>
            <strong>Contact:</strong> {{ $school->phone ?? 'N/A' }} |
            <strong>Email:</strong> {{ $school->email ?? 'N/A' }}
        </p>
    </div>

    <!-- REPORT TITLE -->
    <div class="center-text" style="margin: 30px 0;">
        <h2 style="font-size: 24px; color: #1e40af;">INCOME STATEMENT</h2>
        <h3 style="font-size: 18px; color: #555;">
            {{ $termLabel }} - {{ $yearLabel }}
        </h3>
        <p>Generated on: {{ now()->format('d F Y') }}</p>
    </div>

    <!-- INCOME: FEE-BASED -->
    <h3 style="color: #1e40af; margin-top: 30px;">Fee-Based Incomes</h3>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Amount (ZMW)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($feeIncomes as $type => $total)
                <tr>
                    <td>{{ ucwords(str_replace('_', ' ', $type)) }}</td>
                    <td class="text-right">K {{ number_format($total, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>Subtotal (Fees)</strong></td>
                <td class="text-right"><strong>K {{ number_format($feeIncomes->sum(), 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- INCOME: OTHER SOURCES -->
    <h3 style="color: #1e40af;">Other Incomes</h3>
    <table>
        <thead>
            <tr>
                <th>Source</th>
                <th>Amount (ZMW)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customIncomes as $src => $total)
                <tr>
                    <td>{{ ucwords($src) }}</td>
                    <td class="text-right">K {{ number_format($total, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>Subtotal (Other Incomes)</strong></td>
                <td class="text-right"><strong>K {{ number_format($customIncomes->sum(), 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- TOTAL INCOME -->
    <table>
        <tr class="grand-total">
            <td><strong>TOTAL INCOME</strong></td>
            <td class="text-right"><strong>K {{ number_format($totalIncome, 2) }}</strong></td>
        </tr>
    </table>

    <!-- EXPENSES -->
    <h3 style="color: #dc2626; margin-top: 40px;">Expenses</h3>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Amount (ZMW)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $exp)
                <tr>
                    <td>{{ ucfirst($exp->category) }}</td>
                    <td class="text-right">K {{ number_format($exp->total, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>TOTAL EXPENSES</strong></td>
                <td class="text-right"><strong>K {{ number_format($totalExpenses, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- PROFIT / LOSS -->
    <table style="margin-top: 40px; font-size: 20px;">
        <tr class="highlight">
            <td style="width: 70%;"><strong>NET {{ $profitOrLoss }}</strong></td>
            <td class="text-right {{ $profitOrLoss === 'PROFIT' ? 'profit' : 'loss' }}">
                <strong>K {{ number_format($netAmount, 2) }}</strong>
            </td>
        </tr>
    </table>

    <div style="margin-top: 60px; text-align: center; color: #666; font-size: 12px;">
        <p>This financial report was generated by <strong>E-School Management System</strong></p>
        <p>&copy; {{ date('Y') }} {{ $school->name }}. All rights reserved.</p>
    </div>

</body>
</html>
