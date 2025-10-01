<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
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

        $to = Config::get('email.feature_qc.to');
        $cc = implode(';', array_map('trim', Config::get('email.feature_qc.cc')));
        $allConfigurations = config('email');
        $featureNames = array_keys($allConfigurations);

        return view('admin.updateemail', compact('to', 'cc', 'featureNames'));
    }

    public function getEmailSettings(Request $request, $feature)
    {
        // Fetch the email settings based on the selected feature
        $emailSettings = config("email.$feature");

        // Return the email settings as JSON response
        return response()->json($emailSettings);
    }

    public function updateEmail(Request $request)
    {
        // Update the email settings in the configuration file
        $config = config('email');
        $to = $request->to;

        $cc = explode(';', trim($request->cc));
        $feature = $request->feature;

        $config[$feature] = [
            'to' => $to,
            'cc' => $cc,
        ];

        // Write the updated configuration to the file
        $path = config_path('email.php');
        File::put($path, '<?php return '.var_export($config, true).';');

        // Clear the config cache
        Artisan::call('config:clear');

        // Redirect back to the form with success message
        return redirect()
            ->route('changeemail.page')
            ->withSuccess('Email settings updated successfully!');
    }
}
