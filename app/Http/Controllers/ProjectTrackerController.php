<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\ProjectMaster;
use App\Models\ProjectHistory;

class ProjectTrackerController extends Controller
{
    public function index()
    {
        $datas = ProjectMaster::get();
        
        return view("projecttracker.index", compact("datas"));
    }

    public function create()
    {
        $departments = Department::all();

        return view("projecttracker.create", compact("departments"));
    }

    public function store(Request $request)
    {
          // Validate the form data
        $request->validate([
            'project_name' => 'required|string',
            'dept' => 'required|exists:departments,id',
            'request_date' => 'required|date',
            'pic' => 'required|string',
            'description' => 'required|string',
        ]);

        // Create a new project instance
        $project = new ProjectMaster();
        $project->project_name = $request->input('project_name');
        $project->dept = $request->input('dept');
        $project->request_date = $request->input('request_date');
        $project->pic = $request->input('pic');
        $project->description = $request->input('description');
        $project->status = "Initiating";
        // You can also set other attributes here if needed

        // Save the project to the database
        $project->save();

        // Redirect to a success page or back to the form with a success message
        return redirect()->route('pt.index')->with('success', 'Project created successfully');
    }

    
    public function detail($id)
    {
        $project = ProjectMaster::find($id);
        $histories = $project->prohist()->get();
       
        return view("projecttracker.detail", compact("project", "histories"));
    }

    public function updateOngoing(Request $request, $id)
    {
        $request->validate([
            'remark' => 'required|string|max:255', // Validation rule for the remark field
        ]);

        $project = ProjectMaster::findOrFail($id);

        if ($project->start_date == null || $project->status == "Initiating") {
            // Update the status to "Start"
            $project->status = "OnGoing";
            $project->start_date = now();
            $project->save();
    
            // Generate project history data and save it
            $projectHistory = new ProjectHistory();
            $projectHistory->project_id = $project->id;
            $projectHistory->date = now();
            $projectHistory->status = "OnGoing";
            $projectHistory->remarks = $request->input('remark');
           
            $projectHistory->save();
        }
    
        // Redirect back to the project detail page
        return redirect()->route('pt.index', $id);
    }

    public function updateTest(Request $request,$id)
    {
        $request->validate([
            'remark' => 'required|string|max:255', // Validation rule for the remark field
        ]);

        $project = ProjectMaster::findOrFail($id);

        if ($project->status == "OnGoing" || $project->status == "NeedToBeRevised") {
            // Update the status to "Start"
            $project->status = "ReadyToTest";
            $project->save();
    
            // Generate project history data and save it
            $projectHistory = new ProjectHistory();
            $projectHistory->project_id = $project->id;
            $projectHistory->date = now();
            $projectHistory->status = "ReadyToTest";
            $projectHistory->remarks = $request->input('remark');
            // Add other project history data as needed
            $projectHistory->save();
        }
    
        // Redirect back to the project detail page
        return redirect()->route('pt.index', $id);
    }

    public function updateRevision(Request $request, $id)
    {
    //    dd($request->remarks);
        $project = ProjectMaster::findOrFail($id);

        if ($project->status == "ReadyToTest") {
            // Update the status to "Start"
            $project->status = "NeedToBeRevised";
            $project->save();
    
            // Generate project history data and save it
            $projectHistory = new ProjectHistory();
            $projectHistory->project_id = $project->id;
            $projectHistory->date = now();
            $projectHistory->remarks = $request->remarks;
            $projectHistory->status = "NeedToBeRevisied";
            // Add other project history data as needed
            $projectHistory->save();
        }
    
        // Redirect back to the project detail page
        return redirect()->route('pt.index', $id);
    }

    public function updateAccept(Request $request, $id)
    {
        $request->validate([
            'remark' => 'required|string|max:255', // Validation rule for the remark field
        ]);

        $project = ProjectMaster::findOrFail($id);

        if ($project->status == "ReadyToTest") {
            // Update the status to "Start"
            $project->status = "Accept";
            $project->end_date = now();
            $project->save();
    
            // Generate project history data and save it
            $projectHistory = new ProjectHistory();
            $projectHistory->project_id = $project->id;
            $projectHistory->date = now();
            $projectHistory->status = "Accept";
            $projectHistory->remarks = $request->input('remark');
            // Add other project history data as needed
            $projectHistory->save();
        }
    
        // Redirect back to the project detail page
        return redirect()->route('pt.index', $id);
    }
}
