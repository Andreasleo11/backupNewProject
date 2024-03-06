<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SampleEmail;

class MailController extends Controller
{
    public function sendEmail(Request $request)
    {

        // Get the email address from the form input
        /// harus bikin textbox buat ambil data - diatur di controller 
        $recipientEmail = $request->input('recipient_email');


        // Sample email data
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'do the 6 4 9 8 7 then call the behemot.',
        ];

        // Send the email
        Mail::to($recipientEmail)->send(new SampleEmail($data));

        return "Email sent successfully!";
    }
}