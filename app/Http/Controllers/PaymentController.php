<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Pupil;
use App\Models\PaymentTransaction;
use App\Models\ClassModel;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendResultsSmsJob;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller {
    public function index(Request $request) {
        $schoolId = auth()->user()->school_id;

        $years = Payment::where('school_id', $schoolId)
                    ->selectRaw('YEAR(created_at) as year')
                    ->distinct()
                    ->orderBy('year', 'desc')
                    ->pluck('year');

        $payments = $this->filteredPayments($request)
                      ->orderBy('updated_at', 'desc')
                      ->orderBy('created_at', 'desc')
                      ->get();

        return view('payments.index', compact('payments', 'years'));
    }

    /**
     * Base query for the fee collection list, honouring the term/year/search
     * filters. Shared by the list view and the balance-SMS action so both act
     * on exactly the same set of records.
     */
    private function filteredPayments(Request $request)
    {
        $query = Payment::with('pupil')->where('school_id', auth()->user()->school_id);

        if ($request->term) {
            $query->where('term', $request->term);
        }

        if ($request->year) {
            $query->whereYear('created_at', $request->year);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('pupil', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Send each parent a single SMS for their child's total outstanding
     * balance across the currently filtered fee records (one SMS per pupil).
     */
    public function sendBalanceSms(Request $request)
    {
        $sent = 0;

        $this->filteredPayments($request)
            ->where('balance', '>', 0)
            ->with('pupil.parent', 'pupil.school')
            ->get()
            ->groupBy('pupil_id')
            ->each(function ($payments) use (&$sent) {
                $pupil = $payments->first()->pupil;

                if (!$pupil || !$pupil->parent || empty($pupil->parent->phone)) {
                    return;
                }

                $name    = trim($pupil->first_name . ' ' . $pupil->last_name);
                $balance = number_format($payments->sum('balance'), 2);
                $school  = $pupil->school->name ?? 'School';

                $message = "Dear parent, {$name} has an outstanding fee balance of K{$balance}. Kindly settle the balance. - {$school}";

                SendResultsSmsJob::dispatch($pupil->parent->phone, $message);
                $sent++;
            });

        return redirect()->back()->with('success', "{$sent} balance alert(s) queued for SMS delivery.");
    }

    public function create(Pupil $pupil)
    {
        $schoolId = Auth::user()->school_id;

        $pupil = Pupil::with(['school', 'class'])->where('school_id', $schoolId)->where('id', $pupil->id)->first();

        return view('payments.create', compact('pupil'));
    }

    public function selectPupil(Request $request)
    {
        $schoolId = Auth::user()->school_id;

        $classes = ClassModel::where('school_id', $schoolId)->get();

        $classId = $request->input('class_id');

        $pupils = Pupil::with(['school', 'class'])
                    ->where('school_id', $schoolId)
                    ->when($classId, function ($query, $classId) {
                        return $query->where('class_id', $classId);
                    })
                    ->get();

        return view('payments.select-pupil', compact('pupils', 'classes'));
    }

    public function show(Payment $payment) {
        $payment = Payment::with('paymenttransactions', 'pupil')->findOrFail($payment->id);
        return view('payments.show', compact('payment'));
    }

    public function createPayBalance(Payment $payment) {
        $payment = Payment::with('paymenttransactions', 'pupil')->findOrFail($payment->id);
        return view('payments.pay-balance', compact('payment'));
    }

    public function payBalance(Request $request, Payment $payment) {
        $this->validate($request, [
            'amount_paid' => 'required|numeric|min:1',
            'mode_of_payment' => 'required|string',
            'date' => 'required|date',
            'deposit_slip_id' => 'nullable|string|max:255',
        ]);

        // Generate receipt number: YEAR + TIME + SCHOOL ID (zero-padded)
        $receiptNumber = date('YHis') . str_pad(auth()->user()->school_id, 2, '0', STR_PAD_LEFT);

        PaymentTransaction::create([
            'payment_id' => $payment->id,
            'amount' => $request->amount_paid,
            'mode_of_payment' => $request->mode_of_payment,
            'date' => $request->date,
            'deposit_slip_id' => $request->deposit_slip_id ?? null,
            'receipt_number' => $receiptNumber,
        ]);

        $payment->amount_paid += $request->amount_paid;
        $payment->balance = $payment->amount - $payment->amount_paid;
        $payment->save();

        return redirect()->route('payments.show',$payment)->with('success', 'Payment balance updated successfully!');
    }

    public function store(Request $request) {
        $this->validate($request, [
            'amount' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:1',
            'mode_of_payment' => 'required|string',
            'type' => 'required|string',
            'pupil_id' => 'required|exists:pupils,id',
            'term' => 'required|string',
            'date' => 'required|date',
            'deposit_slip_id' => 'nullable|string|max:255',
        ]);

        $payment = Payment::create([
            'amount' => $request->amount,
            'amount_paid' => 0,
            'balance' => $request->amount,
            'type' => $request->type,
            'school_id' => auth()->user()->school_id,
            'pupil_id' => $request->pupil_id,
            'term' => $request->term,
        ]);

        // Generate receipt number: YEAR + TIME + SCHOOL ID (zero-padded)
        $receiptNumber = date('YHis') . str_pad(auth()->user()->school_id, 2, '0', STR_PAD_LEFT);

        $transaction = PaymentTransaction::create([
            'payment_id' => $payment->id,
            'amount' => $request->amount_paid,
            'mode_of_payment' => $request->mode_of_payment ?? null,
            'date' => $request->date,
            'deposit_slip_id' => $request->deposit_slip_id ?? null,
            'receipt_number' => $receiptNumber,
        ]);

        $payment->amount_paid = $request->amount_paid;
        $payment->balance = $request->amount - $request->amount_paid;
        $payment->save();

        return redirect()->route('payments.show',$payment)->with('success', 'Payment and transaction created successfully!');
    }

    public function exportPdf(Payment $payment) {
        $schoolId = Auth::user()->school_id;
        $school = School::find($schoolId);

        if ($payment->school_id !== $schoolId) {
            return redirect()->route('payments.index')
                ->with('error', 'You are not authorized to export this payment receipt.');
        }

        $payment = Payment::with('paymenttransactions', 'pupil')->findOrFail($payment->id);
        $pdf = PDF::loadView('payments.pdf', compact('payment', 'school'));

        return $pdf->download('payment_receipt_' . $payment->pupil->first_name . '.pdf');
    }
}
