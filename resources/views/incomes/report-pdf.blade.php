<!DOCTYPE html>
<html>
<head>
    <title>Expense Report for {{ $termLabel }} {{ $yearLabel }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .center-text {
            text-align: center;
        }
        h1, h2, h3, p {
            margin: 0;
        }
        .school-logo {
            display: block;
            margin: 0 auto;
            width: 100px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .total-row {
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="center-text">
    @php
        $logoFullPath = $school->photo
            ? public_path('storage/' . $school->photo)
            : null;
    @endphp

    @if($logoFullPath && file_exists($logoFullPath))
        <img src="{{ $logoFullPath }}"
             alt="School Logo"
             class="school-logo">
    @else
        <!-- Fallback when no logo -->
        <div style="width:100px; height:100px; background:#f3f4f6; border:3px dashed #94a3b8; border-radius:50%; margin:0 auto 15px; display:flex; align-items:center; justify-content:center;">
            <span style="color:#64748b; font-weight:bold;">LOGO</span>
        </div>
    @endif
</div>



    <div class="center-text">
        <h1>{{ $school->name }}</h1>
        <p><strong>Motto:</strong> {{ $school->motto }}</p>
        <p>{{ $school->address }}</p>
        <p><strong>Contact:</strong> {{ $school->phone }} | <strong>Email:</strong> {{ $school->email }}</p>
    </div>

    <div class="center-text" style="margin-top: 30px;">
        <h2>Income Report</h2>
        <h3>{{ $termLabel }} {{ $yearLabel }}</h3>
    </div>

    <h3>Fee-Based Incomes</h3>
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
                    <td class="text-right">{{ number_format($total, 2) }}</td>
                </tr>
            @endforeach
            <tr class="table-active fw-bold">
                <td><strong>Subtotal (Fees)</strong></td>
                <td class="text-end"><strong>K {{ number_format($feeIncomes->sum(), 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <h3>Other Incomes</h3>
    <table>
        <tr>
            <th>Source</th>
            <th>Amount (ZMW)</th>
        </tr>
        @foreach($customIncomes as $src => $total)
        <tr>
            <td>{{ $src }}</td>
            <td class="text-right">{{ number_format($total, 2) }}</td>
        </tr>
        @endforeach
        <tr class="table-active fw-bold">
            <td><strong>Subtotal (Other Incomes)</strong></td>
            <td class="text-end"><strong>K {{ number_format($customIncomes->sum(), 2) }}</strong></td>
        </tr>
    </table>

    <div class="total text-right">
        <strong>Grand Total: K {{ number_format($grandTotal, 2) }}</strong>
    </div>
</body>
</html>
