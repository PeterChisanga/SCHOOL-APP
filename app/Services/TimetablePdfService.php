<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use App\Models\TimeSlot;

class TimetablePdfService
{
    /**
     * Generate a PDF instance for the given timetable collection.
     * @param \Illuminate\Support\Collection $timetable
     * @param array $meta ['role'=>, 'name'=>]
     */
    public function generatePdf($timetable, array $meta, $term, $year)
    {
        // structure timetable into days x slots
        $days = ['Mon','Tue','Wed','Thu','Fri'];

        // derive timeslots from entries
        $timeSlots = $timetable->pluck('timeSlot')->filter()->unique('id')->sortBy('order')->values();

        // fallback: if there are no entries/time slots, use global timeslots
        if ($timeSlots->isEmpty()) {
            $timeSlots = TimeSlot::orderBy('order')->get();
        }

        // allow view to receive grouped data
        $data = [
            'timetable'=>$timetable,
            'meta'=>$meta,
            'term'=>$term,
            'year'=>$year,
            'days'=>$days,
            'timeSlots'=>$timeSlots,
        ];

        $pdf = Pdf::loadView('timetables.pdf', $data)->setPaper('a4', 'landscape');

        return $pdf;
    }
}
