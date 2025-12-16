<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ClassModel;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Services\TimetablePdfService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateTimetablePdfs extends Command
{
    protected $signature = 'timetables:generate-sample-pdfs {--term=Term 1} {--year=}';

    protected $description = 'Generate PDFs for all classes and teachers for given term and year';

    public function handle()
    {
        $term = $this->option('term');
        $year = $this->option('year') ?: date('Y');

        $pdfService = new TimetablePdfService();

        // classes
        $classes = ClassModel::all();
        foreach ($classes as $c) {
            $timetable = Timetable::with(['subject','teacher','timeSlot'])
                ->where('class_id',$c->id)->where('term',$term)->where('year',$year)->get();

            $meta = ['role'=>'Class','name'=>$c->name];
            $pdf = $pdfService->generatePdf($timetable, $meta, $term, $year);

            $folder = "timetables/".Str::slug($term)."/{$year}";
            $filename = "class_{$c->id}_{$term}_{$year}.pdf";
            $path = "{$folder}/{$filename}";

            Storage::put($path, $pdf->output());
            $this->info("Wrote {$path}");
        }

        // teachers
        $teachers = Teacher::all();
        foreach ($teachers as $t) {
            $timetable = Timetable::with(['subject','class','timeSlot'])
                ->where('teacher_id',$t->id)->where('term',$term)->where('year',$year)->get();

            $meta = ['role'=>'Teacher','name'=>$t->first_name.' '.$t->last_name];
            $pdf = $pdfService->generatePdf($timetable, $meta, $term, $year);

            $folder = "timetables/".Str::slug($term)."/{$year}";
            $filename = "teacher_{$t->id}_{$term}_{$year}.pdf";
            $path = "{$folder}/{$filename}";

            Storage::put($path, $pdf->output());
            $this->info("Wrote {$path}");
        }

        $this->info('Done');

        return 0;
    }
}
