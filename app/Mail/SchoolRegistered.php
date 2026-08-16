<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SchoolRegistered extends Mailable
{
    use Queueable, SerializesModels;

    public $schoolName;
    public $adminName;
    public $adminEmail;
    public $adminPhone;

    public function __construct($schoolName, $adminName, $adminEmail, $adminPhone)
    {
        $this->schoolName = $schoolName;
        $this->adminName = $adminName;
        $this->adminEmail = $adminEmail;
        $this->adminPhone = $adminPhone;
    }

    public function build()
    {
        return $this->subject("New school registered on E-School")
            ->view('emails.school_registered');
    }
}
