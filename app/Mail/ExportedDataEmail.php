<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;

class ExportedDataEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $message;
    public $excelFilePath;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $message, $excelFilePath)
    {
        $this->subject = $subject;
        $this->message = (string) $message;
        $this->excelFilePath = $excelFilePath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Retrieve the file content using Storage::url()
        $excelFileContent = file_get_contents(public_path($this->excelFilePath));

        $customMessage = $this->message;

        return $this->subject($this->subject)
                    ->view('emails.sample_email')
                    ->with('customMessage', $customMessage)
                    ->attachData($excelFileContent, 'exported_data.xlsx', [
                        'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
    }
}
