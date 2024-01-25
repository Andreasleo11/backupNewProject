<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\hrd\ImportantDoc;
use App\Models\ExpiredDoc;
use App\Notifications\ExpiredDocNotification;

class ExpiredNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expired-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update expired important docs notification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiryThreshold = Carbon::now()->addMonths(2);

        $user = User::find(5); // Replace with the user ID you want to authenticate
        Auth::login($user);

        // Check if a user is authenticated
        if ($user = Auth::user()) {

        $importantDocs = ImportantDoc::where('expired_date', '<', $expiryThreshold)->get();

        foreach ($importantDocs as $importantDoc) {
            // Assuming the user_id should be associated with the logged-in user
            // Adjust this part based on your actual relationship
            // // $expiredDoc = ExpiredDoc::create([
            // //     'user_id' => $user->id,
            // //     'doc_id' => $importantDoc->id,
            // // ]);

            $user->notify(new ExpiredDocNotification($importantDoc));
        }

        $this->info('Notification updated!');
        } else {
            $this->info('No authenticated user found.');
        }
    }
}
