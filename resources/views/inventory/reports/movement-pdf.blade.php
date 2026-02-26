<!DOCTYPE html>
<html>
<head>
    <title>Stock Movement Report</title>
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
        <strong style="font-size:14px;">STOCK MOVEMENT REPORT</strong><br>
        @if($from && $to)
            Period: {{ $from }} to {{ $to }}
        @else
            All Records
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Item</th>
                <th>Action</th>
                <th>Old Qty</th>
                <th>Change</th>
                <th>New Qty</th>
                <th>Issued By</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $log->inventory->item_name }}</td>
                <td>{{ ucfirst($log->action_type) }}</td>
                <td>{{ $log->old_quantity }}</td>
                <td>{{ $log->change_amount }}</td>
                <td>{{ $log->new_quantity }}</td>
                <td>{{ $log->user->first_name ?? '-' }}</td>
                <td>{{ $log->note }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
