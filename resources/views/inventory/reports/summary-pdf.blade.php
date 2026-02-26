<!DOCTYPE html>
<html>
<head>
    <title>Inventory Summary Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .header-table td {
            border: none;
            vertical-align: middle;
        }

        .logo {
            width: 60px;
            height: 60px;
        }

        .school-details {
            text-align: center;
            line-height: 1.3;
        }

        .report-title {
            text-align: center;
            margin-top: 5px;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 14px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
            font-size: 12px;
        }

        .summary-table td {
            border: 1px solid #000;
            padding: 6px;
        }

        .summary-label {
            font-weight: bold;
            background-color: #f2f2f2;
            width: 30%;
        }

        .summary-value {
            text-align: right;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

    @php
        $logoFullPath = $school->photo
            ? public_path('storage/' . $school->photo)
            : null;
    @endphp

    <table class="header-table">
        <tr>
            <td width="80">
                @if($logoFullPath && file_exists($logoFullPath))
                    <img src="{{ $logoFullPath }}" class="logo">
                @endif
            </td>

            <td class="school-details">
                <strong style="font-size:16px;">{{ $school->name }}</strong><br>
                {{ $school->motto }}<br>
                {{ $school->address }}<br>
                Tel: {{ $school->phone }} | Email: {{ $school->email ?? '-' }}
            </td>

            <td width="80"></td>
        </tr>
    </table>

    <hr style="margin:5px 0;">

    <div class="report-title">
        <strong style="font-size:14px;">Inventory Summary Report - {{ now()->format('d M Y') }}</strong>
    </div>

    {{-- Summary --}}
    <table class="summary-table">
        <tr>
            <td class="summary-label">Total Inventory Items</td>
            <td class="summary-value">{{ number_format($totalItems) }}</td>

            <td class="summary-label">Total Stock Quantity</td>
            <td class="summary-value">{{ number_format($totalQuantity) }}</td>
        </tr>

        <tr>
            <td class="summary-label">Low Stock Items</td>
            <td class="summary-value">{{ number_format($lowStockCount) }}</td>

            <td class="summary-label">Out of Stock Items</td>
            <td class="summary-value">{{ number_format($outOfStockCount) }}</td>
        </tr>
    </table>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Condition</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventories as $item)
            <tr>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->category->name ?? '-' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->condition }}</td>
                <td>{{ $item->location ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
