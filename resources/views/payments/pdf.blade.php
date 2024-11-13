<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .center-text {
            text-align: center;
        }
        h1, h2, h3, h4, p {
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
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
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
        <p> {{ $school->address }}</p>
        <p><strong>Phone:</strong> {{ $school->phone }} | <strong>Email:</strong> {{ $school->email }}</p>
    </div>

    <div class="center-text" style="margin-top: 30px;">
        <h2>Payment Receipt</h2>
        <h3>For: {{ $payment->pupil->first_name }} {{ $payment->pupil->last_name }}</h3>
    </div>
    <p><strong>Date: </strong> {{ $payment->created_at->format('d F, Y') }}</p>
    <table>
        <tr>
            <th>Payment Type</th>
            <td>{{ $payment->type }}</td>
        </tr>
        <tr>
            <th>Total Amount</th>
            <td>K {{ number_format($payment->amount, 2) }}</td>
        </tr>
        <tr>
            <th>Amount Paid</th>
            <td>K {{ number_format($payment->amount_paid, 2) }}</td>
        </tr>
        <tr>
            <th>Balance</th>
            <td>K {{ number_format($payment->balance, 2) }}</td>
        </tr>
        <tr>
            <th>Term</th>
            <td>{{ $payment->term }} {{ $payment->created_at->format('Y') }}</td>
        </tr>
    </table>
    <br>
    <h3>Transactions</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Amount</th>
                <th>Mode of Payment</th>
                <th>Deposit Slip ID</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payment->paymenttransactions as $transaction)
                <tr>
                    <td>{{ $transaction->date }}</td>
                    <td>K {{ number_format($transaction->amount, 2) }}</td>
                    <td>{{ $transaction->mode_of_payment }}</td>
                    <td>{{ $transaction->deposit_slip_id ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>
       <p>Signature: ..................</p>
    <br>

    <div class="center-text" style="margin-top: 30px;">
        <p>Thank you for your payment!</p>
    </div>

</body>
</html>
