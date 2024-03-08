<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SampleEmail;

class MailController extends Controller
{
    public function sendEmail()
    {
        // Sample email data
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'do the 6 4 9 8 7 then call the behemot.',
        ];

        // Send the email
        Mail::to('andreasleonardo.al@gmail.com')->send(new SampleEmail($data));

        return "Email sent successfully!";
    }
}