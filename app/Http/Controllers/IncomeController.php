<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Income;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class IncomeController extends Controller {
    public function index(Request $request) {
        $schoolId = auth()->user()->school_id;
        $term = $request->input('term');
        $year = $request->input('year');
        $isPremium = auth()->user()->isPremium();

        // Fee Incomes (Always visible)
        $feeIncomes = Payment::where('school_id', $schoolId)
            ->when($term, fn($q) => $q->where('term', $term))
            ->when($year, fn($q) => $q->whereYear('created_at', $year))
            ->selectRaw('type, SUM(amount_paid) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        // Custom Incomes — PREMIUM ONLY
        $customIncomes = collect();
        if ($isPremium) {
            $customIncomes = Income::where('school_id', $schoolId)
                ->when($term, fn($q) => $q->where('term', $term))
                ->when($year, fn($q) => $q->where('year', $year))
                ->orderBy('date', 'desc')
                ->get();
        }

        $grandTotal = $feeIncomes->sum() + $customIncomes->sum('amount');

        $years = Payment::where('school_id', $schoolId)
            ->selectRaw('YEAR(created_at) as year')
            ->when($isPremium, fn($q) => $q->union(Income::selectRaw('year')))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('incomes.index', compact(
            'feeIncomes', 'customIncomes', 'grandTotal', 'term', 'year', 'years', 'isPremium'
        ));
    }

    // PREMIUM ONLY: Create, Edit, Delete
    public function create() {
        if (!auth()->user()->isPremium()) abort(403);
        return view('incomes.create');
    }

    public function store(Request $request) {
        if (!auth()->user()->isPremium()) abort(403);

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'source' => 'required|string|max:100',
            'description' => 'nullable|string',
            'term' => 'required|in:term 1,term 2,term 3',
            'date' => 'required|date',
        ]);

        Income::create([
            'school_id' => auth()->user()->school_id,
            'amount' => $request->amount,
            'source' => $request->source,
            'description' => $request->description,
            'term' => $request->term,
            'year' => \Carbon\Carbon::parse($request->date)->year,
            'date' => $request->date,
        ]);

        return redirect()->route('incomes.index')->with('success', 'Income added successfully.');
    }

    public function edit(Income $income) {
        if (!auth()->user()->isPremium()) abort(403);
        $this->authorizeSchool($income);
        return view('incomes.edit', compact('income'));
    }

    public function update(Request $request, Income $income) {
        if (!auth()->user()->isPremium()) abort(403);
        $this->authorizeSchool($income);
            $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'source' => 'required|string|max:100',
                'description' => 'nullable|string',
                'term' => 'required|in:term 1,term 2,term 3',
                'date' => 'required|date',
            ]);

            $income->update([
                'amount' => $request->amount,
                'source' => $request->source,
                'description' => $request->description,
                'term' => $request->term,
                'year' => \Carbon\Carbon::parse($request->date)->year,
                'date' => $request->date,
            ]);

            return redirect()->route('incomes.index')
                ->with('success', 'Income updated successfully.');
    }

    public function destroy(Income $income) {
        if (!auth()->user()->isPremium()) abort(403);
        $this->authorizeSchool($income);
        $income->delete();
        return back()->with('success', 'Income deleted.');
    }

    public function report(Request $request) {
        $schoolId = auth()->user()->school_id;
        $term     = $request->input('term');
        $year     = $request->input('year');

        // Fee-based incomes
        $feeIncomes = Payment::where('school_id', $schoolId)
            ->when($term, fn($q) => $q->where('term', $term))
            ->when($year, fn($q) => $q->whereYear('created_at', $year))
            ->selectRaw('type, SUM(amount_paid) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        // Custom incomes
        $customIncomes = Income::where('school_id', $schoolId)
            ->when($term, fn($q) => $q->where('term', $term))
            ->when($year, fn($q) => $q->where('year', $year))
            ->selectRaw('source, SUM(amount) as total')
            ->groupBy('source')
            ->pluck('total', 'source');

        $grandTotal = $feeIncomes->sum() + $customIncomes->sum();

        $termLabel = $term ? ucwords(str_replace('_', ' ', $term)) : 'All Terms';
        $yearLabel = $year ?: 'All Years';

        $school = Auth::user()->school;

        $pdf = Pdf::loadView('incomes.report-pdf', compact(
            'feeIncomes', 'customIncomes', 'grandTotal', 'termLabel', 'yearLabel', 'school'
        ));

        return $pdf->download("Income_Report_{$termLabel}_{$yearLabel}.pdf");
    }

    public function financialReport(Request $request) {
        $schoolId = auth()->user()->school_id;
        $term = $request->input('term');
        $year = $request->input('year');

        // Fee Incomes
        $feeIncomes = Payment::where('school_id', $schoolId)
            ->when($term, fn($q) => $q->where('term', $term))
            ->when($year, fn($q) => $q->whereYear('created_at', $year))
            ->selectRaw('type, SUM(amount_paid) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        // Custom Incomes
        $customIncomes = Income::where('school_id', $schoolId)
            ->when($term, fn($q) => $q->where('term', $term))
            ->when($year, fn($q) => $q->where('year', $year))
            ->selectRaw('source, SUM(amount) as total')
            ->groupBy('source')
            ->pluck('total', 'source');

        // Expenses (now with term!)
        $expenses = Expense::where('school_id', $schoolId)
            ->when($term, fn($q) => $q->where('term', $term))
            ->when($year, fn($q) => $q->whereYear('date', $year))
            ->selectRaw('description as category, SUM(amount) as total')
            ->groupBy('description')
            ->get();

        $totalIncome = $feeIncomes->sum() + $customIncomes->sum();
        $totalExpenses = $expenses->sum('total');
        $netProfit = $totalIncome - $totalExpenses;
        $profitOrLoss = $netProfit >= 0 ? 'PROFIT' : 'LOSS';
        $netAmount = abs($netProfit);

        $termLabel = $term ? ucwords(str_replace('_', ' ', $term)) : 'All Terms';
        $yearLabel = $year ?: 'All Years';

        $pdf = PDF::loadView('reports.financial', [
            'school'         => auth()->user()->school,
            'feeIncomes'     => $feeIncomes,
            'customIncomes'  => $customIncomes,
            'expenses'       => $expenses,
            'totalIncome'    => $totalIncome,
            'totalExpenses'  => $totalExpenses,
            'netProfit'      => $netProfit,
            'profitOrLoss'   => $profitOrLoss,
            'netAmount'      => $netAmount,
            'termLabel'      => $termLabel,
            'yearLabel'      => $yearLabel,
        ]);

        return $pdf->download("Financial_Report_{$termLabel}_{$yearLabel}.pdf");
    }

    // Helper
    private function authorizeSchool($model) {
        if ($model->school_id !== auth()->user()->school_id) {
            abort(403);
        }
    }
}
