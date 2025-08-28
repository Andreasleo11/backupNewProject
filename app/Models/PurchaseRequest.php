<?php

namespace App\Models;

use App\Console\Commands\SendPREmailNotification;
use App\Notifications\PurchaseRequestCreated;
use App\Notifications\PurchaseRequestUpdated;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        "user_id_create",
        "date_pr",
        "date_required",
        "remark",
        "to_department",
        "autograph_1",
        "autograph_2",
        "autograph_3",
        "autograph_4",
        "autograph_5",
        "autograph_6",
        "autograph_7",
        "autograph_user_1",
        "autograph_user_2",
        "autograph_user_3",
        "autograph_user_4",
        "autograph_user_5",
        "autograph_user_6",
        "autograph_user_7",
        "attachment_pr",
        "status",
        "pr_no",
        "supplier",
        "description",
        "approved_at",
        "updated_at",
        "pic",
        "type",
        "from_department",
        "is_import",
        "is_cancel",
        "po_number",
        "doc_num",
        "branch",
    ];

    public function itemDetail()
    {
        return $this->hasMany(DetailPurchaseRequest::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, "user_id_create");
    }

    public function files()
    {
        return $this->hasMany(File::class, "doc_id", "doc_num");
    }

    public function scopeApproved($query)
    {
        return $query->where("status", 4);
    }

    public function scopeWaiting($query)
    {
        return $query->where("status", 3);
    }

    public function scopeRejected($query)
    {
        return $query->where("status", 5);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($pr) {
            // Map department names to codes
            $toDepartmentCodes = [
                "Computer" => "CP",
                "Personnel" => "HRD",
                "Maintenance" => "MT",
                "Purchasing" => "PUR",
            ];

            // Map branches to area codes
            $branchCodes = [
                "JAKARTA" => "JKT",
                "KARAWANG" => "KRW",
            ];

            // Get the date portion
            $date = $pr->created_at->format("ymd"); // Day-Month-Year format (e.g., '240819' for August 24, 2019)

            // Get the department code
            $toDepartment = $pr->to_department;
            $toDepartmentCode = $toDepartmentCodes[$toDepartment] ?? "UNK"; // Use 'UNK' for unknown departments

            // Get the area code from the branch
            $branch = $pr->branch;
            $areaCode = $branchCodes[$branch] ?? "UNK"; // Use 'UNK' for unknown branches

            // Fetch the last record's doc_num for the current date and branch code
            $latest = static::where("doc_num", "like", "%/PR/{$areaCode}/{$date}/%")
                ->orderBy("id", "desc")
                ->first();

            if ($latest) {
                // Extract the increment part from the latest doc_num
                $lastIncrement = (int) substr($latest->doc_num, -3); // Assuming the increment is always 3 digits
            } else {
                $lastIncrement = 0; // No records found for today
            }

            // Calculate the next increment number
            $increment = str_pad($lastIncrement + 1, 3, "0", STR_PAD_LEFT);

            // Build the docNum
            $docNum = "{$toDepartmentCode}/PR/{$areaCode}/{$date}/{$increment}";

            $prNo = substr($toDepartment, 0, 4) . "-" . $pr->id;

            $pr->update(["pr_no" => $prNo, "doc_num" => $docNum]);
            $pr->sendNotification("created");
        });

        static::updated(function ($pr) {
            $statusChanged = $pr->isDirty("status");

            if ($statusChanged && $pr->status != 8) {
                $pr->sendNotification("updated");
            }
        });
    }

    private function sendNotification($event)
    {
        $details = $this->prepareNotificationDetails($event);
        $this->notifyUsers($details, $event);
    }

    private function prepareNotificationDetails($event)
    {
        $status = $this->getStatusText($this->status);

        $commonDetails = [
            "greeting" => "Purchase Request Notification",
            "actionText" => "Check Now",
            "actionURL" => route("purchaserequest.detail", $this->id),
        ];

        if ($event == "created" || $event == "updated") {
            $commonDetails["body"] = "Here's the detail : <br>
                - Doc. Num : $this->doc_num <br>
                - PR No. : $this->pr_no <br>
                - Created By : {$this->createdBy->name} <br>
                - Date PR : $this->date_pr <br>
                - Date Required : $this->date_required <br>
                - PIC : $this->pic <br>
                - Remark : $this->remark <br>
                - To Department : $this->to_department <br>
                - Status : $status";
        }

        return $commonDetails;
    }

    private function getStatusText($status)
    {
        switch ($status) {
            case 1:
                return "WAITING FOR DEPT HEAD";
            case 2:
                return "WAITING FOR VERIFICATOR";
            case 3:
                return "WAITING FOR DIRECTOR";
            case 4:
                return "APPROVED";
            case 5:
                return "REJECTED";
            case 6:
                return "WAITING FOR PURCHASER";
            case 7:
                return "WAITING FOR GM";
            default:
                return "NOT DEFINED";
        }
    }

    private function notifyUsers($details, $event)
    {
        $users = [$this->createdBy];

        if ($event == "created") {
            if ($this->to_department === "Maintenance") {
                if (
                    $this->from_department === "PLASTIC INJECTION" &&
                    $this->branch === "KARAWANG"
                ) {
                    $user = null;
                } else {
                    $user = User::where("email", "nur@daijo.co.id")->first();
                }
            }

            $createdNotificationUsers = isset($user) ? array_merge($users, [$user]) : $users;
            Notification::send(
                $createdNotificationUsers,
                new PurchaseRequestCreated($this, $details),
            );
        } else {
            $status = $this->status;
            switch ($status) {
                case 1:
                    if (
                        $this->from_department === "PLASTIC INJECTION" &&
                        $this->branch === "KARAWANG"
                    ) {
                        $deptHead = null;
                    } elseif ($this->from_department === "MOULDING") {
                        if ($this->is_import === 1) {
                            $deptHead = User::where("email", "fang@daijo.co.id")->first();
                        } else {
                            // if is_import is false or null, notification will sent to fang and ong
                            $deptHead = User::where("is_head", 1)
                                ->whereHas("department", function ($query) {
                                    $query->where("name", $this->from_department);
                                })
                                ->get();
                        }
                    } elseif ($this->from_department === "STORE") {
                        $deptHead = User::where("is_head", 1)
                            ->whereHas("department", function ($query) {
                                $query->where("name", "LOGISTIC");
                            })
                            ->first();
                    } else {
                        $deptHead = User::where("is_head", 1)
                            ->whereHas("department", function ($query) {
                                $query->where("name", $this->from_department);
                            })
                            ->first();
                    }

                    $user = $deptHead ?: $this->createdBy;
                    break;
                case 7:
                    if (
                        $this->from_department === "PLASTIC INJECTION" &&
                        $this->branch === "KARAWANG"
                    ) {
                        $gm = User::where("email", "pawarid_pannin@daijo.co.id")->first();
                    } else {
                        $gm = User::whereHas("department", function ($query) {
                            $query->where("name", "!=", "MOULDING")->where("is_gm", 1);
                        })->first();
                    }
                    $user = $gm ?: $this->createdBy;
                    break;
                case 6:
                    if ($this->to_department === "Computer") {
                        $purchaser = User::where("email", "vicky@daijo.co.id")->first();
                    } elseif ($this->to_department === "Purchasing") {
                        $purchaser = User::where("email", "dian@daijo.co.id")->first();
                    } elseif ($this->to_department === "Maintenance") {
                        $purchaser = User::where("email", "nur@daijo.co.id")->first();
                    } elseif ($this->to_department === "Personnel") {
                        $purchaser = User::where("email", "ani_apriani@daijo.co.id")->first();
                    } else {
                        $purchaser = $this->createdBy;
                    }

                    $user = $purchaser;
                    break;
                case 2:
                    $verificator = User::with("specification")
                        ->whereHas("specification", function ($query) {
                            $query->where("name", "VERIFICATOR");
                        })
                        ->where("is_head", 1)
                        ->first();

                    $user = $verificator ?: $this->createdBy;
                    break;
                case 3:
                    $director = User::with("specification")
                        ->whereHas("specification", function ($query) {
                            $query->where("name", "DIRECTOR");
                        })
                        ->first();

                    $user = $director ?: $this->createdBy;
                    break;
                case 4:
                case 5:
                    $user = $this->createdBy;
                    break;
                default:
                    $user = $this->createdBy;
                    break;
            }

            if ($this->to_department === "Purchasing" && $this->status === 4) {
                $purchasingUsers = User::whereHas("department", function ($query) {
                    $query->where("name", "PURCHASING");
                })->get();
                $users = array_merge($users, $purchasingUsers->all());
            } elseif ($this->to_department === "Maintenance") {
                $ccUser = User::where("email", "nur@daijo.co.id")->first();
                if ($ccUser) {
                    $users = array_merge($users, [$ccUser]);
                }
            }

            // If $user is a collection, merge its users; if it's a single object, wrap it in an array
            if ($user instanceof \Illuminate\Support\Collection) {
                $updatedNotificationUsers = array_merge($users, $user->all());
            } else {
                $updatedNotificationUsers = isset($user) ? array_merge($users, [$user]) : $users;
            }

            Notification::send(
                $updatedNotificationUsers,
                new PurchaseRequestUpdated($this, $details),
            );
        }
    }
}
