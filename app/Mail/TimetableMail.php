<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class TimetableMail extends Mailable
{
    use Queueable, SerializesModels;

    public $path;

    /**
     * Create a new message instance.
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $email = $this->subject('School Timetable')
            ->view('emails.timetable')
            ->with(['message'=>'Please find attached the timetable PDF.']);

        if (Storage::exists($this->path)) {
            $email->attach(Storage::path($this->path));
        }

        return $email;
    }
}

