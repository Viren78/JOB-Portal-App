<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    public function index(){
        $jobs = Job::latest()->with('user', 'applications')->paginate(10);
        return view('admin.jobs.list', [
            'jobs' => $jobs,

        ]);
    }

    public function edit($id){
        $job = Job::findOrFail($id);

        $categories = Category::orderBy('name', 'ASC')->get();

        $jobTypes = JobType::orderBy('name', 'ASC')->get();

        return view('admin.jobs.edit', [
            'job' => $job,
            'categories' => $categories,
            'jobTypes' => $jobTypes,
        ]);
    }

    public function update(Request $req, $id){
        $rules = [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'experience' => 'required',
            'company_name' => 'required|min:3|max:50',
        ];

        $validator = Validator::make($req->all(), $rules);

        if($validator->passes()){

            $job = Job::find($id);
            $job->title = $req->title;
            $job->category_id = $req->category;
            $job->job_type_id = $req->jobType;
            // $job->user_id = Auth::user()->id;
            $job->vacancy = $req->vacancy;
            $job->salary = $req->salary;
            $job->location = $req->location;
            $job->description = $req->description;
            $job->benefits = $req->benefits;
            $job->responsibility = $req->responsibility;
            $job->qualifications = $req->qualifications;
            $job->keywords = $req->keywords;
            $job->experience = $req->experience;
            $job->company_name = $req->company_name;
            $job->company_location = $req->company_location;
            $job->company_website = $req->company_website;

            $job->isFeatured = (!empty($req->isFeatured)) ? $req->isFeatured : 0;
            $job->status = $req->status;
            $job->save();

            session()->flash('success', 'Job Updated successfully.');

            return  response()->json([
                'status' => true,
                'errors' => [],
            ]);

        }else{
            return  response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function destroy(Request $req){
        $id = $req->id;

        $job = Job::find($id);

        if ($job == null) {
            session()->flash('error', 'Job not found !');
            return response()->json([
                'status' => false,
            ]);
        }

        $job->delete();
        session()->flash('success', 'Job Deleted Successfully !');
        return response()->json([
            'status' => true,
        ]);
    }




}
