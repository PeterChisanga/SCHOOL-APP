<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Pupil;
use App\Models\PaymentTransaction;
use App\Models\ClassModel;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller {
    public function index(Request $request) {
        $schoolId = auth()->user()->school_id;

        $years = Payment::where('school_id', $schoolId)
                    ->selectRaw('YEAR(created_at) as year')
                    ->distinct()
                    ->orderBy('year', 'desc')
                    ->pluck('year');

        $query = Payment::with('pupil')->where('school_id', $schoolId);

        if ($request->term) {
            $query->where('term', $request->term);
        }

        if ($request->year) {
            $query->whereYear('created_at', $request->year);
        }

        $payments = $query->orderBy('updated_at', 'desc')
                      ->orderBy('created_at', 'desc')
                      ->get();

        return view('payments.index', compact('payments', 'years'));
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

    public function createPayBalance(Payment $payment)
    {
        $payment = Payment::with('paymenttransactions', 'pupil')->findOrFail($payment->id);
        return view('payments.pay-balance', compact('payment'));
    }

    public function payBalance(Request $request, Payment $payment)
    {
        $this->validate($request, [
            'amount_paid' => 'required|numeric|min:1',
            'mode_of_payment' => 'required|string',
            'date' => 'required|date',
            'deposit_slip_id' => 'nullable|string|max:255',
        ]);

        PaymentTransaction::create([
            'payment_id' => $payment->id,
            'amount' => $request->amount_paid,
            'mode_of_payment' => $request->mode_of_payment,
            'date' => $request->date,
            'deposit_slip_id' => $request->deposit_slip_id ?? null,
        ]);

        $payment->amount_paid += $request->amount_paid;
        $payment->balance = $payment->amount - $payment->amount_paid;
        $payment->save();

        return redirect()->route('payments.show',$payment)->with('success', 'Payment balance updated successfully!');
    }

    public function store(Request $request)
    {
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

        $transaction = PaymentTransaction::create([
            'payment_id' => $payment->id,
            'amount' => $request->amount_paid,
            'mode_of_payment' => $request->mode_of_payment ?? null,
            'date' => $request->date,
            'deposit_slip_id' => $request->deposit_slip_id ?? null,
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
