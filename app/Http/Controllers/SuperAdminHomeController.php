<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SuperAdminHomeController extends Controller
{
    public function index()
    {
        return view('superadmin_home');
    }


    public function updateEmailpage()
    {
         // Get the 'to' and 'cc' values from the configuration file
         $to = Config::get('email.to');
         $cc = implode('; ', Config::get('email.cc'));
         
         
        return view('admin.updateemail', compact('to', 'cc'));
    }

    public function updateEmail(Request $request)
    {
         // Update the email settings in the configuration file
        $to = $request->to;
        $cc = explode('; ', $request->cc);

        $config = [
            'to' => $to,
            'cc' => $cc,
        ];

        // Write the updated configuration to the file
        $path = config_path('email.php');
        File::put($path, '<?php return ' . var_export($config, true) . ';');

        // Clear the config cache
        Artisan::call('config:clear');

        // Redirect back to the form with success message
        return redirect()->route('changeemail.page')->withSuccess('Email settings updated successfully!');
    }

}
