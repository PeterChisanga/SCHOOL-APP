<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ExpenseController extends Controller {
    public function index() {
        $schoolId = Auth::user()->school_id;
        $expenses = Expense::where('school_id', $schoolId)->get()->map(function ($expense) {
            $date = Carbon::parse($expense->date);
            $month = $date->month;
            $expense->term = match (true) {
                $month >= 1 && $month <= 4 => 'Term 1',
                $month >= 5 && $month <= 8 => 'Term 2',
                $month >= 9 && $month <= 12 => 'Term 3',
                default => 'Unknown'
            };
            $expense->year = $date->year;
            return $expense;
        });

        // Calculate total expenses per term and year
        $totals = $expenses->groupBy(['year', 'term'])->map(function ($yearGroup) {
            return $yearGroup->map(function ($termGroup) {
                return $termGroup->sum('amount');
            });
        });

        // Generate years for report dropdown (2020 to current year)
        $currentYear = Carbon::today()->year;
        $years = range(2020, $currentYear);

        return view('expenses.index', compact('expenses', 'totals', 'years'));
    }

    public function create() {
        return view('expenses.create');
    }

    public function store(Request $request) {
        $this->validate($request, [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        $expense = new Expense($request->all());
        $expense->school_id = Auth::user()->school_id;
        $expense->save();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense recorded successfully!');
    }

    public function show(Expense $expense) {
        if ($expense->school_id !== Auth::user()->school_id) {
            return redirect()->route('expenses.index')->with('error', 'Unauthorized access to this expense.');
        }

        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense) {
        if ($expense->school_id !== Auth::user()->school_id) {
            return redirect()->route('expenses.index')->with('error', 'Unauthorized access to this expense.');
        }

        return view('expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense) {
        $this->validate($request, [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        if ($expense->school_id !== Auth::user()->school_id) {
            return redirect()->route('expenses.index')->with('error', 'Unauthorized access to this expense.');
        }

        $expense->update($request->all());

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully!');
    }

    public function destroy(Expense $expense) {
        if ($expense->school_id !== Auth::user()->school_id) {
            return redirect()->route('expenses.index')->with('error', 'Unauthorized access to this expense.');
        }

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully!');
    }

    public function exportReport(Request $request) {
        $this->validate($request, [
            'term' => 'required|in:Term 1,Term 2,Term 3',
            'year' => 'required|integer|min:2020|max:' . Carbon::today()->year,
        ]);

        $schoolId = Auth::user()->school_id;
        $term = $request->term;
        $year = $request->year;

        // Filter expenses by term and year
        $expenses = Expense::where('school_id', $schoolId)
            ->whereYear('date', $year)
            ->whereRaw('MONTH(date) BETWEEN ? AND ?', match ($term) {
                'Term 1' => [1, 4],
                'Term 2' => [5, 8],
                'Term 3' => [9, 12],
                default => [1, 12]
            })
            ->get();

        $totalAmount = $expenses->sum('amount');
        $school = Auth::user()->school;

        $pdf = Pdf::loadView('expenses.report', compact('expenses', 'term', 'year', 'totalAmount', 'school'));
        return $pdf->download("expense_report_{$term}_{$year}.pdf");
    }

}
