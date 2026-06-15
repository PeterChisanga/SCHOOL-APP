<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Income;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

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

    // ── Income Report Shared Data ────────────────────────────────────
    private function getIncomeReportData(Request $request): array {
        $schoolId = auth()->user()->school_id;
        $term     = $request->input('term');
        $year     = $request->input('year');

        $feeIncomes = Payment::where('school_id', $schoolId)
            ->when($term, fn($q) => $q->where('term', $term))
            ->when($year, fn($q) => $q->whereYear('created_at', $year))
            ->selectRaw('type, SUM(amount_paid) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $customIncomes = Income::where('school_id', $schoolId)
            ->when($term, fn($q) => $q->where('term', $term))
            ->when($year, fn($q) => $q->where('year', $year))
            ->selectRaw('source, SUM(amount) as total')
            ->groupBy('source')
            ->pluck('total', 'source');

        $grandTotal = $feeIncomes->sum() + $customIncomes->sum();
        $termLabel  = $term ? ucwords(str_replace('_', ' ', $term)) : 'All Terms';
        $yearLabel  = $year ?: 'All Years';

        return compact('feeIncomes', 'customIncomes', 'grandTotal', 'termLabel', 'yearLabel');
    }

    // ── Income Report PDF (existing, refactored) ─────────────────────
    public function report(Request $request) {
        $data   = $this->getIncomeReportData($request);
        $school = Auth::user()->school;

        $pdf = Pdf::loadView('incomes.report-pdf', array_merge($data, compact('school')));
        return $pdf->download("Income_Report_{$data['termLabel']}_{$data['yearLabel']}.pdf");
    }

    // ── Income Report Excel ──────────────────────────────────────────
    public function reportExcel(Request $request) {
        $data        = $this->getIncomeReportData($request);
        $school      = Auth::user()->school;
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Income Report');

        $blueHeader = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 13],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e40af']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $colHeader = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f2f2f2']],
        ];
        $totalRow = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'dbeafe']],
        ];
        $grandTotalStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803d']],
        ];

        $row = 1;

        // School name
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", strtoupper($school->name));
        $sheet->getStyle("A{$row}")->applyFromArray($blueHeader)->getFont()->setSize(16);
        $row++;

        // Title
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", "INCOME REPORT — {$data['termLabel']} {$data['yearLabel']}");
        $sheet->getStyle("A{$row}")->applyFromArray($blueHeader);
        $row++;

        // Generated on
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'Generated on: ' . now()->format('d F Y'));
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}")->getFont()->setSize(10)->getColor()->setRGB('666666');
        $row += 2;

        // ── Helper ──
        $addSection = function($title, $col1, $col2, $rows, $subtotalLabel, $subtotalValue)
            use ($sheet, &$row, $blueHeader, $colHeader, $totalRow) {

            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->setCellValue("A{$row}", $title);
            $sheet->getStyle("A{$row}")->applyFromArray($blueHeader)->getFont()->setSize(11);
            $row++;

            $sheet->setCellValue("A{$row}", $col1);
            $sheet->setCellValue("B{$row}", $col2);
            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray($colHeader);
            $row++;

            foreach ($rows as $label => $value) {
                $sheet->setCellValue("A{$row}", ucwords(str_replace('_', ' ', $label)));
                $sheet->setCellValue("B{$row}", 'K ' . number_format($value, 2));
                $sheet->getStyle("B{$row}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $row++;
            }

            $sheet->setCellValue("A{$row}", $subtotalLabel);
            $sheet->setCellValue("B{$row}", 'K ' . number_format($subtotalValue, 2));
            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray($totalRow);
            $sheet->getStyle("B{$row}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $row += 2;
        };

        // Fee Incomes
        $addSection(
            'FEE-BASED INCOMES', 'Type', 'Amount (ZMW)',
            $data['feeIncomes']->toArray(),
            'Subtotal (Fees)', $data['feeIncomes']->sum()
        );

        // Custom Incomes
        $addSection(
            'OTHER INCOMES', 'Source', 'Amount (ZMW)',
            $data['customIncomes']->toArray(),
            'Subtotal (Other Incomes)', $data['customIncomes']->sum()
        );

        // Grand Total
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL');
        $sheet->setCellValue("B{$row}", 'K ' . number_format($data['grandTotal'], 2));
        $sheet->getStyle("A{$row}:B{$row}")->applyFromArray($grandTotalStyle);
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row += 2;

        // Footer
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'Generated by E-School Management System');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}")->getFont()->setSize(9)->getColor()->setRGB('999999');

        $sheet->getColumnDimension('A')->setWidth(42);
        $sheet->getColumnDimension('B')->setWidth(26);
        $sheet->getStyle("A1:B{$row}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $filename = "Income_Report_{$data['termLabel']}_{$data['yearLabel']}.xlsx";
        $path     = storage_path("app/public/{$filename}");
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ── Income Report Word ───────────────────────────────────────────
    public function reportWord(Request $request) {
        $data    = $this->getIncomeReportData($request);
        $school  = Auth::user()->school;
        $phpWord = new PhpWord();
        $section = $phpWord->addSection([
            'marginTop' => 800, 'marginBottom' => 800,
            'marginLeft' => 900, 'marginRight' => 900,
        ]);

        $phpWord->addTitleStyle(1, [
            'bold' => true, 'size' => 20, 'color' => '1e40af',
        ], ['alignment' => 'center']);
        $phpWord->addTitleStyle(2, [
            'bold' => true, 'size' => 14, 'color' => '1e40af',
        ], ['alignment' => 'center']);

        $tableStyle    = [
            'borderSize' => 6, 'borderColor' => 'dddddd',
            'cellMargin' => 80, 'width' => 100 * 50, 'unit' => 'pct',
        ];
        $blueRowStyle  = ['bgColor' => '1e40af'];
        $whiteBold     = ['bold' => true, 'color' => 'FFFFFF', 'size' => 11];
        $totalRowStyle = ['bgColor' => 'dbeafe'];
        $grandRowStyle = ['bgColor' => '15803d'];
        $grandBold     = ['bold' => true, 'color' => '0f0f0f', 'size' => 12];
        $boldStyle     = ['bold' => true, 'size' => 11];
        $normalStyle   = ['size' => 11];

        $section->addTitle(strtoupper($school->name), 1);
        $section->addTitle("INCOME REPORT — {$data['termLabel']} {$data['yearLabel']}", 2);
        $section->addText(
            'Generated on: ' . now()->format('d F Y'),
            ['size' => 10, 'color' => '666666'],
            ['alignment' => 'center']
        );
        $section->addTextBreak(1);

        $addWordTable = function($title, $col1, $col2, $rows, $subtotalLabel, $subtotalValue)
            use ($section, $tableStyle, $blueRowStyle, $whiteBold, $totalRowStyle, $boldStyle, $normalStyle) {

            $section->addText($title, ['bold' => true, 'size' => 13, 'color' => '1e40af']);
            $table = $section->addTable($tableStyle);

            $table->addRow(null, $blueRowStyle);
            $table->addCell(4500)->addText($col1, $whiteBold);
            $table->addCell(2000)->addText($col2, $whiteBold, ['alignment' => 'right']);

            foreach ($rows as $label => $value) {
                $table->addRow();
                $table->addCell(4500)->addText(
                    ucwords(str_replace('_', ' ', $label)), $normalStyle
                );
                $table->addCell(2000)->addText(
                    'K ' . number_format($value, 2), $normalStyle, ['alignment' => 'right']
                );
            }

            $table->addRow(null, $totalRowStyle);
            $table->addCell(4500)->addText($subtotalLabel, $boldStyle);
            $table->addCell(2000)->addText(
                'K ' . number_format($subtotalValue, 2), $boldStyle, ['alignment' => 'right']
            );
            $section->addTextBreak(1);
        };

        $addWordTable(
            'Fee-Based Incomes', 'Type', 'Amount (ZMW)',
            $data['feeIncomes']->toArray(),
            'Subtotal (Fees)', $data['feeIncomes']->sum()
        );

        $addWordTable(
            'Other Incomes', 'Source', 'Amount (ZMW)',
            $data['customIncomes']->toArray(),
            'Subtotal (Other Incomes)', $data['customIncomes']->sum()
        );

        // Grand Total
        $grandTable = $section->addTable($tableStyle);
        $grandTable->addRow(null, $grandRowStyle);
        $grandTable->addCell(4500)->addText('GRAND TOTAL', $grandBold);
        $grandTable->addCell(2000)->addText(
            'K ' . number_format($data['grandTotal'], 2),
            $grandBold, ['alignment' => 'right']
        );

        $section->addTextBreak(2);
        $section->addText(
            'Generated by E-School Management System',
            ['size' => 9, 'color' => '999999'],
            ['alignment' => 'center']
        );

        $filename = "Income_Report_{$data['termLabel']}_{$data['yearLabel']}.docx";
        $path     = storage_path("app/public/{$filename}");
        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    // ── Shared data builder ──────────────────────────────────────────
    private function getReportData(Request $request): array {
        $schoolId = auth()->user()->school_id;
        $term     = $request->input('term');
        $year     = $request->input('year');

        $feeIncomes = Payment::where('school_id', $schoolId)
            ->when($term, fn($q) => $q->where('term', $term))
            ->when($year, fn($q) => $q->whereYear('created_at', $year))
            ->selectRaw('type, SUM(amount_paid) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $customIncomes = Income::where('school_id', $schoolId)
            ->when($term, fn($q) => $q->where('term', $term))
            ->when($year, fn($q) => $q->where('year', $year))
            ->selectRaw('source, SUM(amount) as total')
            ->groupBy('source')
            ->pluck('total', 'source');

        $expenses = Expense::where('school_id', $schoolId)
            ->when($term, fn($q) => $q->where('term', $term))
            ->when($year, fn($q) => $q->whereYear('date', $year))
            ->selectRaw('description as category, SUM(amount) as total')
            ->groupBy('description')
            ->get();

        $totalIncome   = $feeIncomes->sum() + $customIncomes->sum();
        $totalExpenses = $expenses->sum('total');
        $netProfit     = $totalIncome - $totalExpenses;
        $profitOrLoss  = $netProfit >= 0 ? 'PROFIT' : 'LOSS';
        $netAmount     = abs($netProfit);
        $termLabel     = $term ? ucwords(str_replace('_', ' ', $term)) : 'All Terms';
        $yearLabel     = $year ?: 'All Years';

        return compact(
            'feeIncomes', 'customIncomes', 'expenses',
            'totalIncome', 'totalExpenses', 'netProfit',
            'profitOrLoss', 'netAmount', 'termLabel', 'yearLabel'
        );
    }

    // ── PDF ──────────────────────────────────────────────────────────
    public function financialReport(Request $request) {
        $data = $this->getReportData($request);

        $pdf = PDF::loadView('reports.financial', array_merge($data, [
            'school' => auth()->user()->school,
        ]));

        return $pdf->download("Financial_Report_{$data['termLabel']}_{$data['yearLabel']}.pdf");
    }

    // ── Excel ────────────────────────────────────────────────────────
    public function financialReportExcel(Request $request) {
        $data        = $this->getReportData($request);
        $school      = auth()->user()->school;
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Financial Report');

        // Styles
        $blueHeader = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 13],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e40af']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $redHeader = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'dc2626']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $colHeader = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f2f2f2']],
        ];
        $totalRow = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'dbeafe']],
        ];
        $grandTotal = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e40af']],
        ];
        $expTotal = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'dc2626']],
        ];
        $profitRow = [
            'font' => ['bold' => true, 'size' => 12,
                'color' => ['rgb' => $data['profitOrLoss'] === 'PROFIT' ? '16a34a' : 'dc2626']],
            'fill' => ['fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $data['profitOrLoss'] === 'PROFIT' ? 'f0fdf4' : 'fef2f2']],
        ];

        $row = 1;

        // School name
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", strtoupper($school->name));
        $sheet->getStyle("A{$row}")->applyFromArray($blueHeader)->getFont()->setSize(16);
        $row++;

        // Report title
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", "INCOME STATEMENT — {$data['termLabel']} {$data['yearLabel']}");
        $sheet->getStyle("A{$row}")->applyFromArray($blueHeader)->getFont()->setSize(13);
        $row++;

        // Generated on
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'Generated on: ' . now()->format('d F Y'));
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}")->getFont()->setSize(10)->getColor()->setRGB('666666');
        $row += 2;

        // ── Helper: section ──
        $addSection = function(
            $title, $titleStyle, $col1, $col2, $rows, $subtotalLabel, $subtotalValue
        ) use ($sheet, &$row, $colHeader, $totalRow) {
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->setCellValue("A{$row}", $title);
            $sheet->getStyle("A{$row}")->applyFromArray($titleStyle);
            $row++;

            $sheet->setCellValue("A{$row}", $col1);
            $sheet->setCellValue("B{$row}", $col2);
            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray($colHeader);
            $row++;

            foreach ($rows as $label => $value) {
                $sheet->setCellValue("A{$row}", ucwords(str_replace('_', ' ', $label)));
                $sheet->setCellValue("B{$row}", 'K ' . number_format($value, 2));
                $sheet->getStyle("B{$row}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $row++;
            }

            $sheet->setCellValue("A{$row}", $subtotalLabel);
            $sheet->setCellValue("B{$row}", 'K ' . number_format($subtotalValue, 2));
            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray($totalRow);
            $sheet->getStyle("B{$row}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $row += 2;
        };

        // Fee Incomes
        $addSection(
            'FEE-BASED INCOMES', $blueHeader, 'Type', 'Amount (ZMW)',
            $data['feeIncomes']->toArray(),
            'Subtotal (Fees)', $data['feeIncomes']->sum()
        );

        // Custom Incomes
        $addSection(
            'OTHER INCOMES', $blueHeader, 'Source', 'Amount (ZMW)',
            $data['customIncomes']->toArray(),
            'Subtotal (Other Incomes)', $data['customIncomes']->sum()
        );

        // Total Income
        $sheet->setCellValue("A{$row}", 'TOTAL INCOME');
        $sheet->setCellValue("B{$row}", 'K ' . number_format($data['totalIncome'], 2));
        $sheet->getStyle("A{$row}:B{$row}")->applyFromArray($grandTotal);
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row += 2;

        // Expenses
        $expenseRows = [];
        foreach ($data['expenses'] as $exp) {
            $expenseRows[$exp->category] = $exp->total;
        }
        $addSection(
            'EXPENSES', $redHeader, 'Description', 'Amount (ZMW)',
            $expenseRows,
            'TOTAL EXPENSES', $data['totalExpenses']
        );

        // Override subtotal style for expenses to red
        $sheet->getStyle("A" . ($row - 2) . ":B" . ($row - 2))->applyFromArray($expTotal);

        // Net Profit / Loss
        $sheet->setCellValue("A{$row}", 'NET ' . $data['profitOrLoss']);
        $sheet->setCellValue("B{$row}", 'K ' . number_format($data['netAmount'], 2));
        $sheet->getStyle("A{$row}:B{$row}")->applyFromArray($profitRow);
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row += 2;

        // Footer
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'Generated by E-School Management System');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}")->getFont()->setSize(9)->getColor()->setRGB('999999');

        // Column widths & borders
        $sheet->getColumnDimension('A')->setWidth(42);
        $sheet->getColumnDimension('B')->setWidth(26);
        $sheet->getStyle("A1:B{$row}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $filename = "Financial_Report_{$data['termLabel']}_{$data['yearLabel']}.xlsx";
        $path     = storage_path("app/public/{$filename}");
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ── Word ─────────────────────────────────────────────────────────
    public function financialReportWord(Request $request) {
        $data   = $this->getReportData($request);
        $school = auth()->user()->school;

        $phpWord = new PhpWord();
        $section = $phpWord->addSection([
            'marginTop' => 800, 'marginBottom' => 800,
            'marginLeft' => 900, 'marginRight' => 900,
        ]);

        $phpWord->addTitleStyle(1, [
            'bold' => true, 'size' => 20, 'color' => '1e40af',
        ], ['alignment' => 'center']);

        $phpWord->addTitleStyle(2, [
            'bold' => true, 'size' => 14, 'color' => '1e40af',
        ], ['alignment' => 'center']);

        $tableStyle = [
            'borderSize' => 6, 'borderColor' => 'dddddd',
            'cellMargin' => 80, 'width' => 100 * 50, 'unit' => 'pct',
        ];
        $blueRowStyle  = ['bgColor' => '1e40af'];
        $redRowStyle   = ['bgColor' => 'dc2626'];
        $whiteCellBold = ['bold' => true, 'color' => 'FFFFFF', 'size' => 11];
        $totalRowStyle = ['bgColor' => 'dbeafe'];
        $boldStyle     = ['bold' => true, 'size' => 11];
        $normalStyle   = ['size' => 11];

        // ── Header ──
        $section->addTitle(strtoupper($school->name), 1);
        $section->addTitle("INCOME STATEMENT — {$data['termLabel']} {$data['yearLabel']}", 2);
        $section->addText(
            'Generated on: ' . now()->format('d F Y'),
            ['size' => 10, 'color' => '666666'],
            ['alignment' => 'center']
        );
        $section->addTextBreak(1);

        // ── Helper: add table section ──
        $addWordTable = function(
            $title, $titleColor, $headerRowStyle,
            $col1Header, $col2Header,
            $rows, $subtotalLabel, $subtotalValue
        ) use ($section, $tableStyle, $whiteCellBold, $totalRowStyle, $boldStyle, $normalStyle) {
            $section->addText($title, ['bold' => true, 'size' => 13, 'color' => $titleColor]);
            $table = $section->addTable($tableStyle);

            $table->addRow(null, $headerRowStyle);
            $table->addCell(4500)->addText($col1Header, $whiteCellBold);
            $table->addCell(2000)->addText($col2Header, $whiteCellBold, ['alignment' => 'right']);

            foreach ($rows as $label => $value) {
                $table->addRow();
                $table->addCell(4500)->addText(
                    ucwords(str_replace('_', ' ', $label)), $normalStyle
                );
                $table->addCell(2000)->addText(
                    'K ' . number_format($value, 2), $normalStyle, ['alignment' => 'right']
                );
            }

            $table->addRow(null, $totalRowStyle);
            $table->addCell(4500)->addText($subtotalLabel, $boldStyle);
            $table->addCell(2000)->addText(
                'K ' . number_format($subtotalValue, 2), $boldStyle, ['alignment' => 'right']
            );

            $section->addTextBreak(1);
        };

        // Fee Incomes
        $addWordTable(
            'Fee-Based Incomes', '1e40af', $blueRowStyle,
            'Type', 'Amount (ZMW)',
            $data['feeIncomes']->toArray(),
            'Subtotal (Fees)', $data['feeIncomes']->sum()
        );

        // Custom Incomes
        $addWordTable(
            'Other Incomes', '1e40af', $blueRowStyle,
            'Source', 'Amount (ZMW)',
            $data['customIncomes']->toArray(),
            'Subtotal (Other Incomes)', $data['customIncomes']->sum()
        );

        // Total Income
        $totalTable = $section->addTable($tableStyle);
        $totalTable->addRow(null, $blueRowStyle);
        $totalTable->addCell(4500)->addText('TOTAL INCOME', $whiteCellBold);
        $totalTable->addCell(2000)->addText(
            'K ' . number_format($data['totalIncome'], 2),
            $whiteCellBold, ['alignment' => 'right']
        );
        $section->addTextBreak(1);

        // Expenses
        $expenseRows = [];
        foreach ($data['expenses'] as $exp) {
            $expenseRows[$exp->category] = $exp->total;
        }
        $addWordTable(
            'Expenses', 'dc2626', $redRowStyle,
            'Description', 'Amount (ZMW)',
            $expenseRows,
            'TOTAL EXPENSES', $data['totalExpenses']
        );

        // Net Profit / Loss
        $netColor   = $data['profitOrLoss'] === 'PROFIT' ? '16a34a' : 'dc2626';
        $netBgColor = $data['profitOrLoss'] === 'PROFIT' ? 'f0fdf4' : 'fef2f2';
        $netTable   = $section->addTable($tableStyle);
        $netTable->addRow(null, ['bgColor' => $netBgColor]);
        $netTable->addCell(4500)->addText(
            'NET ' . $data['profitOrLoss'],
            ['bold' => true, 'size' => 13, 'color' => $netColor]
        );
        $netTable->addCell(2000)->addText(
            'K ' . number_format($data['netAmount'], 2),
            ['bold' => true, 'size' => 13, 'color' => $netColor],
            ['alignment' => 'right']
        );

        $section->addTextBreak(2);
        $section->addText(
            'Generated by E-School Management System',
            ['size' => 9, 'color' => '999999'],
            ['alignment' => 'center']
        );

        $filename = "Financial_Report_{$data['termLabel']}_{$data['yearLabel']}.docx";
        $path     = storage_path("app/public/{$filename}");
        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }


    // Helper
    private function authorizeSchool($model) {
        if ($model->school_id !== auth()->user()->school_id) {
            abort(403);
        }
    }
}
