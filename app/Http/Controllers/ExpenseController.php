<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

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

    // public function exportReport(Request $request) {
    //     $this->validate($request, [
    //         'term' => 'required|in:Term 1,Term 2,Term 3',
    //         'year' => 'required|integer|min:2020|max:' . Carbon::today()->year,
    //     ]);

    //     $schoolId = Auth::user()->school_id;
    //     $term = $request->term;
    //     $year = $request->year;

    //     // Filter expenses by term and year
    //     $expenses = Expense::where('school_id', $schoolId)
    //         ->whereYear('date', $year)
    //         ->whereRaw('MONTH(date) BETWEEN ? AND ?', match ($term) {
    //             'Term 1' => [1, 4],
    //             'Term 2' => [5, 8],
    //             'Term 3' => [9, 12],
    //             default => [1, 12]
    //         })
    //         ->get();

    //     $totalAmount = $expenses->sum('amount');
    //     $school = Auth::user()->school;

    //     $pdf = Pdf::loadView('expenses.report', compact('expenses', 'term', 'year', 'totalAmount', 'school'));
    //     return $pdf->download("expense_report_{$term}_{$year}.pdf");
    // }

    // ── Shared data builder ──────────────────────────────────────────
    private function getExpenseReportData(Request $request): array {
        $this->validate($request, [
            'term' => 'required|in:Term 1,Term 2,Term 3',
            'year' => 'required|integer|min:2020|max:' . Carbon::today()->year,
        ]);

        $schoolId = Auth::user()->school_id;
        $term     = $request->term;
        $year     = $request->year;

        $expenses = Expense::where('school_id', $schoolId)
            ->whereYear('date', $year)
            ->whereRaw('MONTH(date) BETWEEN ? AND ?', match ($term) {
                'Term 1' => [1, 4],
                'Term 2' => [5, 8],
                'Term 3' => [9, 12],
                default  => [1, 12],
            })
            ->get();

        $totalAmount = $expenses->sum('amount');
        $school      = Auth::user()->school;

        return compact('expenses', 'term', 'year', 'totalAmount', 'school');
    }

    // ── PDF (existing, refactored) ───────────────────────────────────
    public function exportReport(Request $request) {
        $data = $this->getExpenseReportData($request);
        $pdf  = Pdf::loadView('expenses.report', $data);
        return $pdf->download("expense_report_{$data['term']}_{$data['year']}.pdf");
    }

    // ── Excel ────────────────────────────────────────────────────────
    public function exportReportExcel(Request $request) {
        $data        = $this->getExpenseReportData($request);
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Expense Report');

        $redHeader = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 13],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'dc2626']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $colHeader = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f2f2f2']],
        ];
        $totalRowStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'dc2626']],
        ];

        $row = 1;

        // School name
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", strtoupper($data['school']->name));
        $sheet->getStyle("A{$row}")->applyFromArray($redHeader)->getFont()->setSize(16);
        $row++;

        // Title
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", "EXPENSE REPORT — {$data['term']} {$data['year']}");
        $sheet->getStyle("A{$row}")->applyFromArray($redHeader);
        $row++;

        // Generated on
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'Generated on: ' . now()->format('d F Y'));
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}")->getFont()->setSize(10)->getColor()->setRGB('666666');
        $row += 2;

        // Column headers
        $sheet->setCellValue("A{$row}", 'Description');
        $sheet->setCellValue("B{$row}", 'Amount (ZMW)');
        $sheet->setCellValue("C{$row}", 'Date');
        $sheet->getStyle("A{$row}:C{$row}")->applyFromArray($colHeader);
        $row++;

        if ($data['expenses']->isEmpty()) {
            $sheet->mergeCells("A{$row}:C{$row}");
            $sheet->setCellValue("A{$row}", "No expenses found for {$data['term']} {$data['year']}.");
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        } else {
            foreach ($data['expenses'] as $expense) {
                $sheet->setCellValue("A{$row}", ucfirst($expense->description));
                $sheet->setCellValue("B{$row}", 'K ' . number_format($expense->amount, 2));
                $sheet->setCellValue("C{$row}", Carbon::parse($expense->date)->format('d/m/Y'));
                $sheet->getStyle("B{$row}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("C{$row}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }

            // Total row
            $sheet->setCellValue("A{$row}", 'TOTAL');
            $sheet->setCellValue("B{$row}", 'K ' . number_format($data['totalAmount'], 2));
            $sheet->setCellValue("C{$row}", '');
            $sheet->getStyle("A{$row}:C{$row}")->applyFromArray($totalRowStyle);
            $sheet->getStyle("B{$row}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $row += 2;
        }

        // Footer
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'Generated by E-School Management System');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}")->getFont()->setSize(9)->getColor()->setRGB('999999');

        $sheet->getColumnDimension('A')->setWidth(38);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getStyle("A1:C{$row}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $filename = "Expense_Report_{$data['term']}_{$data['year']}.xlsx";
        $path     = storage_path("app/public/{$filename}");
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ── Word ─────────────────────────────────────────────────────────
    public function exportReportWord(Request $request) {
        $data    = $this->getExpenseReportData($request);
        $phpWord = new PhpWord();
        $section = $phpWord->addSection([
            'marginTop' => 800, 'marginBottom' => 800,
            'marginLeft' => 900, 'marginRight' => 900,
        ]);

        $phpWord->addTitleStyle(1, [
            'bold' => true, 'size' => 20, 'color' => 'dc2626',
        ], ['alignment' => 'center']);
        $phpWord->addTitleStyle(2, [
            'bold' => true, 'size' => 14, 'color' => 'dc2626',
        ], ['alignment' => 'center']);

        $tableStyle    = [
            'borderSize' => 6, 'borderColor' => 'dddddd',
            'cellMargin' => 80, 'width' => 100 * 50, 'unit' => 'pct',
        ];
        $redRowStyle   = ['bgColor' => 'dc2626'];
        $whiteBold     = ['bold' => true, 'color' => '0f0f0f', 'size' => 11];
        $totalRowStyle = ['bgColor' => 'dc2626'];
        $boldStyle     = ['bold' => true, 'size' => 11];
        $normalStyle   = ['size' => 11];

        $section->addTitle(strtoupper($data['school']->name), 1);
        $section->addTitle("EXPENSE REPORT — {$data['term']} {$data['year']}", 2);
        $section->addText(
            'Generated on: ' . now()->format('d F Y'),
            ['size' => 10, 'color' => '666666'],
            ['alignment' => 'center']
        );
        $section->addTextBreak(1);

        if ($data['expenses']->isEmpty()) {
            $section->addText(
                "No expenses found for {$data['term']} {$data['year']}.",
                ['size' => 11, 'color' => '666666']
            );
        } else {
            $table = $section->addTable($tableStyle);

            // Header row
            $table->addRow(null, $redRowStyle);
            $table->addCell(3500)->addText('Description', $whiteBold);
            $table->addCell(2000)->addText('Amount (ZMW)', $whiteBold, ['alignment' => 'right']);
            $table->addCell(1500)->addText('Date', $whiteBold, ['alignment' => 'center']);

            // Data rows
            foreach ($data['expenses'] as $expense) {
                $table->addRow();
                $table->addCell(3500)->addText(ucfirst($expense->description), $normalStyle);
                $table->addCell(2000)->addText(
                    'K ' . number_format($expense->amount, 2),
                    $normalStyle, ['alignment' => 'right']
                );
                $table->addCell(1500)->addText(
                    Carbon::parse($expense->date)->format('d/m/Y'),
                    $normalStyle, ['alignment' => 'center']
                );
            }

            // Total row
            $table->addRow(null, $totalRowStyle);
            $table->addCell(3500)->addText('TOTAL', $whiteBold);
            $table->addCell(2000)->addText(
                'K ' . number_format($data['totalAmount'], 2),
                $whiteBold, ['alignment' => 'right']
            );
            $table->addCell(1500)->addText('', $whiteBold);
        }

        $section->addTextBreak(2);
        $section->addText(
            'Generated by E-School Management System',
            ['size' => 9, 'color' => '999999'],
            ['alignment' => 'center']
        );

        $filename = "Expense_Report_{$data['term']}_{$data['year']}.docx";
        $path     = storage_path("app/public/{$filename}");
        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

}
