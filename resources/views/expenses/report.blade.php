<!DOCTYPE html>
<html>
<head>
    <title>Expense Report for {{ $term }} {{ $year }}</title>
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
        @if($school->photo)
            <img src="{{ public_path('storage/' . $school->photo) }}" alt="School Logo" class="school-logo">
        @endif
    </div>

    <div class="center-text">
        <h1>{{ $school->name }}</h1>
        <p><strong>Motto:</strong> {{ $school->motto }}</p>
        <p>{{ $school->address }}</p>
        <p><strong>Contact:</strong> {{ $school->phone }} | <strong>Email:</strong> {{ $school->email }}</p>
    </div>

    <div class="center-text" style="margin-top: 30px;">
        <h2>Expense Report</h2>
        <h3>{{ $term }} {{ $year }}</h3>
    </div>

    @if ($expenses->isEmpty())
        <p>No expenses found for {{ $term }} {{ $year }}.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($expenses as $expense)
                    <tr>
                        <td>{{ $expense->description }}</td>
                        <td>{{ number_format($expense->amount, 2) }}</td>
                        <td>{{ \Carbon\Carbon::parse($expense->date)->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td><strong>Total</strong></td>
                    <td><strong>{{ number_format($totalAmount, 2) }}</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @endif

</body>
</html>
