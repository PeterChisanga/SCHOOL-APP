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
            width: 40%;
        }
        .paid-stamp {
            display: inline-block;
            border: 3px solid #0f5132;
            color: #0f5132;
            font-weight: bold;
            font-size: 14px;
            letter-spacing: 2px;
            padding: 6px 18px;
            border-radius: 6px;
            margin-top: 20px;
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
        <p><strong>Phone:</strong> {{ $school->phone }} | <strong>Email:</strong> {{ $school->email }}</p>
    </div>

    <div class="center-text" style="margin-top: 30px;">
        <h2>Payment Receipt</h2>
        <h3>For: {{ $pupil->first_name }} {{ $pupil->last_name }}</h3>
    </div>

    <div style="display: flex; justify-content: space-between; margin-top: 20px;">
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($transaction->date)->format('d F, Y') }}</p>
        <p><strong>Receipt No:</strong> {{ $transaction->receipt_number }}</p>
    </div>

    <table>
        <tr>
            <th>Paid For</th>
            <td>{{ $payment->type ?? 'School Fees' }} @if($payment->term)({{ $payment->term }})@endif</td>
        </tr>
        <tr>
            <th>Amount Paid</th>
            <td>K {{ number_format($transaction->amount, 2) }}</td>
        </tr>
        <tr>
            <th>Payment Method</th>
            <td>{{ $transaction->mode_of_payment }}</td>
        </tr>
        @if($transaction->parent_reference)
        <tr>
            <th>Reference Given</th>
            <td>{{ $transaction->parent_reference }}</td>
        </tr>
        @endif
        @if($transaction->verified_at)
        <tr>
            <th>Verified On</th>
            <td>{{ \Carbon\Carbon::parse($transaction->verified_at)->format('d F, Y') }}</td>
        </tr>
        @endif
        <tr>
            <th>Remaining Balance</th>
            <td>K {{ number_format($payment->balance, 2) }}</td>
        </tr>
    </table>

    <div class="center-text">
        <div class="paid-stamp">PAID</div>
    </div>

    <div class="center-text" style="margin-top: 30px;">
        <p>Thank you for your payment!</p>
        <p style="font-size: 11px; color: #888; margin-top: 6px;">This is a system-generated receipt and does not require a signature.</p>
    </div>

</body>
</html>
