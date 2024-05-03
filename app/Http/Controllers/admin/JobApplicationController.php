<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function index(){
        $applications = JobApplication::latest()
                        ->with('user', 'employer', 'job')
                        ->paginate(10);
        return view('admin.job-applications.list', [
            'applications' => $applications,
        ]);
    }

    public function destroy(Request $req){

        $id = $req->id;

        $application = JobApplication::findOrFail($id);
        
        $application->delete();
        session()->flash('success', 'Job Application Deleted Successfully !');
        return response()->json([
            'success' => true
        ]);
    }
}
