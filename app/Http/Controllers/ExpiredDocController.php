<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ExpiredDoc;
use App\Models\User;
use App\Notifications\ExpiredDocNotification;

class ExpiredDocController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function expired($id)
    {
        $expiredDoc = ExpiredDoc::create([
            'user_id' => Auth::user()->id,
            'doc_id' => $id
        ]);

        User::find(Auth::user()->id)->notify(new ExpiredDocNotification($expiredDoc->doc_id));
        return redirect()->back()->with('status', 'Notification successfully sent!');
    }

    public function markAsRead(){
        Auth::user()->unreadNotifications->markAsRead();
        return redirect()->back();
    }
}
