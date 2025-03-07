<?php

namespace App\Console\Commands;

use App\Models\EmployeeTraining;
use App\Models\User;
use App\Notifications\TrainingReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTrainingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:training-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for upcoming or due trainings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetDate = Carbon::today()->subDays(75);

        // Fetch trainings 2.5 months or older
        $trainings = EmployeeTraining::whereDate('last_training_at', '<=', $targetDate)
            ->with('employee')
            ->get();

        // Fetch users to be notified
        $usersToBeNotified = User::whereHas('department', function ($query) {
            $query->where(function ($query) {
                $query->where('name', 'PERSONALIA');
            });
        })->get();

        // dd($usersToBeNotified->pluck('email'));
        // dd($trainings->pluck('id'));

        foreach ($trainings as $training) {
            if ($training->employee) {
                foreach ($usersToBeNotified as $user) {
                    $user->notify(new TrainingReminderNotification($training));
                }
            }
        }

        $this->info('Employee Training reminders sent successfully for trainings 2.5 months old.');
    }

}
