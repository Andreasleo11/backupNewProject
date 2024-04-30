<?php

namespace App\Console\Commands;

use App\Mail\PRMail;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SendPREmailNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-pr-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify if there\'s a new PR created/modified';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $newPR = PurchaseRequest::whereDate('created_at', now())->get();
        $newPr = PurchaseRequest::with('createdBy', 'createdBy.department')->latest()->first();

        switch ($newPr->status) {
            case 1:
                if($newPr->to_department === "Computer"){
                    $purchaser = User::with(['department', 'specification'])
                                    ->whereHas('department', function ($query) {
                                        $query->where('name', 'COMPUTER');
                                    })
                                    ->whereHas('specification', function ($query) {
                                        $query->where('name', 'PURCHASER');
                                    })
                                    ->first()->email;
                } elseif($newPr->to_department === "Purchasing") {
                    $purchaser = User::with(['department', 'specification'])
                                    ->whereHas('department', function ($query) {
                                        $query->where('name', 'PURCHASING');
                                    })
                                    ->whereHas('specification', function ($query) {
                                        $query->where('name', 'PURCHASER');
                                    })
                                    ->first()->email;
                } elseif($newPr->to_department === "Personnel" || $newPr->to_department === "Maintenance") {
                    $purchaser = User::with(['department', 'specification'])
                                    ->whereHas('department', function ($query) {
                                        $query->where('name', 'PERSONALIA');
                                    })
                                    ->whereHas('specification', function ($query) {
                                        $query->where('name', 'PURCHASER');
                                    })
                                    ->first()->email;
                } else {
                    $purchaser = 'andreasleonardo.al@gmail.com';
                }
                $to = $purchaser;
                break;
            case 6:
                // Retrieve the user who is a head and belongs to the same department as the creator of the latest PurchaseRequest
                $user = User::where('is_head', 1)
                            ->whereHas('department', function($query) use ($newPr) {
                                $query->where('name', $newPr->createdBy->department->name);
                            })
                            ->first();
                $to = $user->email;
                break;
            case 2:
                $to = User::with('specification')
                        ->whereHas('specification', function ($query) {
                            $query->where('name', 'VERIFICATOR');
                        })
                        ->where('is_head', 1)
                        ->first();
                break;
            case 3:
                $to = User::with('department')
                        ->whereHas('department', function ($query) {
                            $query->where('name', 'DIRECTOR');
                        })
                        ->first();
                break;
            default:
                $to = 'raymondlay023@gmail.com';
                break;
            }

        if($newPr->status === 4 || $newPr->status === 5){
            $to = $newPr->createdBy->email;
        }

        $cc = ['raymondlay023@gmail.com', 'andreasleonardo.al@gmail.com'];
        $mailData = [
            'to' => $to,
            'cc' => $cc,
            'subject' => 'PR Notification',
            'from' => 'pt.daijoindustrial@daijo.co.id',
            'url' => 'http://116.254.114.93:2420',
            'newPr' => $newPr
        ];

        Mail::send(new PRMail($mailData));
        $this->info('PR notification sent successfully.');
    }
}
