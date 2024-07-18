<?php

namespace App\Console\Commands;

use App\Mail\PRMail;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPREmailNotification extends Command
{
    public $purchaseRequest;

    public function __construct(PurchaseRequest $purchaseRequest)
    {
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
                if ($newPr->from_department === 'MOULDING') {
                    if ($newPr->is_import === 1) {
                        $to = 'fang@daijo.co.id';
                    } else {
                        // if is_import is false or null, notification will sent to fang and ong
                        $to = User::where('is_head', 1)
                            ->whereHas('department', function ($query) use ($newPr) {
                                $query->where('name', $newPr->from_department);
                            })
                            ->pluck('email')
                            ->toArray();
                    }
                } elseif ($newPr->from_department === 'STORE') {
                    $user = User::where('is_head', 1)->whereHas('department', function ($query) {
                        $query->where('name', 'LOGISTIC');
                    })->first();

                    $to = $user ? $user->email : $newPr->created->email;
                } else {
                    $user = User::where('is_head', 1)
                        ->whereHas('department', function ($query) use ($newPr) {
                            $query->where('name', $newPr->from_department);
                        })
                        ->first();
                    $to = $user ? $user->email : $newPr->createdBy->email;
                }
                break;
            case 7:
                $user = User::whereHas('department', function ($query) {
                    $query->where('name', '!=', 'MOULDING')->where('is_gm', 1);
                })
                    ->first();
                $to = $user ? $user->email : $newPr->createdBy->email;
                break;
            case 6:
                if ($newPr->to_department === "Computer") {
                    $purchaser = 'vicky@daijo.co.id';
                } elseif ($newPr->to_department === "Purchasing") {
                    $purchaser = 'dian@daijo.co.id';
                } elseif ($newPr->to_department === "Maintenance") {
                    $purchaser = 'nur@daijo.co.id';
                } elseif ($newPr->to_department === "Personnel") {
                    $purchaser = 'ani_apriani@daijo.co.id';
                } else {
                    $purchaser = $newPr->createBy->email;
                }

                $to = $purchaser;
                break;
            case 2:
                $user = User::with('specification')
                    ->whereHas('specification', function ($query) {
                        $query->where('name', 'VERIFICATOR');
                    })
                    ->where('is_head', 1)
                    ->first();
                $to = $user ? $user->email : $newPr->createdBy->email;
                break;
            case 3:
                $user = User::with('department')
                    ->whereHas('department', function ($query) {
                        $query->where('name', 'DIRECTOR');
                    })
                    ->first();
                $to = $user ? $user->email : $newPr->createdBy->email;
                break;
            case 4:
                $to = $newPr->createdBy->email;
                break;
            case 5:
                $to = $newPr->createdBy->email;
                break;
            default:
                $to = $newPr->createdBy->email;
                break;
        }

        $to = 'raymondlay023@gmail.com';

        $newPr->status !== 1 ? $title = "There's PR Changed!" : $title = "There's a New PR!";
        $cc = [$newPr->createdBy->email];
        if ($newPr->to_department === 'Maintenance') {
            array_push($cc, 'nur@daijo.co.id');
        } elseif ($newPr->status === 4 && $newPr->to_department === 'Purchasing') {
            $purchasingUsers = User::with('department')
                ->whereHas('department', function ($query) {
                    $query->where('name', 'PURCHASING');
                })->pluck('email')->toArray();
            $cc = array_merge($cc, $purchasingUsers);
        }
        $status = $this->checkStatus($newPr->status);
        $from = 'pt.daijoindustrial@daijo.co.id';
        $url = 'http://116.254.114.93:2420/' . 'purchaserequest/detail/' . $newPr->id;

        $mailData = [
            'title' => $title,
            'to' => $to,
            'cc' => $cc,
            'subject' => 'PR Notification',
            'from' => $from,
            'url' => $url,
            'newPr' => $newPr,
            'status' => $status
        ];

        try {
            Mail::send(new PRMail($mailData));
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error sending PR email notification', [
                'purchaseRequest' => $this->purchaseRequest->id,
                'error' => $e->getMessage(),
            ]);
            abort(500);
        }
        // $this->info('PR notification sent successfully.');
    }

    private function checkStatus($statusNum)
    {
        switch ($statusNum) {
            case 5:
                $status = "REJECTED";
                break;
            case 1:
                $status = "WAITING FOR DEPT HEAD";
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
