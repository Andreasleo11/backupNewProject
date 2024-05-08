<?php

namespace App\Console\Commands;

use App\Mail\PRMail;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPREmailNotification extends Command
{
    public $purchaseRequest;

    public function __construct(PurchaseRequest $purchaseRequest) {
        parent::__construct();
        $this->purchaseRequest = $purchaseRequest;
    }
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
        // $newPr = PurchaseRequest::with('createdBy', 'createdBy.department')->whereDate('updated_at', now())->first();
        $newPr = $this->purchaseRequest;

        switch ($newPr->status) {
            case 1:
                // Retrieve the user who is a head and belongs to the same department as the creator of the latest PurchaseRequest
                $to = User::where('is_head', 1)
                            ->whereHas('department', function($query) use ($newPr) {
                                $query->where('name', $newPr->createdBy->department->name);
                            })
                            ->first()->email;

                break;
            case 6:
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
                } elseif($newPr->to_department === "Maintenance") {
                    $purchaser = 'nur@daijo.co.id';
                } elseif($newPr->to_department === "Personnel") {
                    $purchaser = 'ani_apriani@daijo.co.id';
                } else {
                    $purchaser = 'andreasleonardo.al@gmail.com';
                }
                $to = $purchaser;

                break;
            case 7:
                 // Initial assignment of $to
                 $to = User::whereHas('department', function ($query) {
                    $query->where('name', '!=', 'MOULDING')->where('is_gm', 1);
                })
                ->first()
                ->email;

                 // Additional condition for user department name is 'MOULDING' and createdBy department name is 'MOULDING'
                 if ($newPr->createdBy->department->name === 'MOULDING') {
                     $to = User::whereHas('department', function ($query) {
                        $query->where('name', 'MOULDING')->where('is_gm', 1);
                    })
                    ->first()
                    ->email;
                 }
                break;
            case 2:
                $to = User::with('specification')
                        ->whereHas('specification', function ($query) {
                            $query->where('name', 'VERIFICATOR');
                        })
                        ->where('is_head', 1)
                        ->first()->email;

                break;
            case 3:
                $to = User::with('department')
                        ->whereHas('department', function ($query) {
                            $query->where('name', 'DIRECTOR');
                        })
                        ->first()->email;

                break;
            case 4:
                $to = $newPr->createdBy->email;
                break;
            case 5:
                $to = $newPr->createdBy->email;
                break;
            default:
                $to = 'raymondlay023@gmail.com';
                break;
        }

        $cc = $newPr->createdBy->email;
        $status = $this->checkStatus($newPr->status);
        $mailData = [
            'to' => $to,
            'cc' => $cc,
            'subject' => 'PR Notification',
            'from' => 'pt.daijoindustrial@daijo.co.id',
            'url' => 'http://116.254.114.93:2420/purchaserequest/detail/' . $newPr->id,
            'newPr' => $newPr,
            'status' => $status
        ];

        Mail::send(new PRMail($mailData));
        // $this->info('PR notification sent successfully.');
    }

    private function checkStatus($statusNum){
        switch ($statusNum) {
            case 5:
                $status = "REJECTED";
                break;
            case 1:
                $status = "WAITING FOR DEPT";
                break;
            case 6:
                $status = "WAITING FOR PURCHASER";
                break;
            case 7:
                $status = "WAITING FOR GM";
                break;
            case 2:
                $status = "WAITING FOR VERIFICATOR";
                break;
            case 3:
                $status = "WAITING FOR DIRECTOR";
                break;
            case 4:
                $status = "APPROVED";
                break;
            default:
                $status = 'NOT DEFINED';
                break;
        }
        return $status;
    }
}
