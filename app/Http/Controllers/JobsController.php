<?php

namespace App\Http\Controllers;

use App\Mail\JobNotificationEmail;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class JobsController extends Controller
{
    // this will show jobs
    public function index(Request $req){

        $categories = Category::where('status', 1)->orderBy('created_at', 'DESC')->get();
        $jobType = JobType::where('status', 1)->get();

        $jobs = Job::where('status', 1);

        // search using keywords
        
        if(!empty($req->keywords)){
            $jobs = $jobs->where(function($query) use ($req) {
                $query->orwhere('title', 'like', '%'.$req->keywords.'%');
                $query->orwhere('keywords', 'like', '%'.$req->keywords.'%');
            });
        }

        // search using location
        if(!empty($req->location)){
            $jobs = $jobs->where('location', $req->location);
        }

        // search using category
        if(!empty($req->category)){
            $jobs = $jobs->where('category_id', $req->category);
        }

        $jobTypeArray =[];
        // search using job type
        if(!empty($req->job_type)){
            // 1,2,3
            $jobTypeArray =  explode(',',$req->job_type);

            $jobs = $jobs->whereIn('job_type_id', $jobTypeArray);
        }

        // search using experience
        if(!empty($req->experience)){
            $jobs = $jobs->where('experience', $req->experience);
        }
        
        $jobs = $jobs->with('jobType');

        if($req->sort == '0' ){
            $jobs = $jobs->orderBy('created_at', 'ASC');
        }else{
            $jobs = $jobs->orderBy('created_at', 'DESC');
        }
        
        $jobs = $jobs->paginate(9);

        return view('front.jobs', [
            'categories' => $categories,
            'jobs' => $jobs,
            'jobType' => $jobType,
            'jobTypeArray' => $jobTypeArray,
        ]);
    }

    public function detail($id){

        $job = Job::where([
                            'id' => $id, 
                            'status' => 1
                          ])->first();

        if($job == null){
            abort(404);
        }

        $savedJobCount = 0;
        if (Auth::user()){
            $savedJobCount = SavedJob::where([
                'job_id' => $id, 
                'user_id' => Auth::user()->id 
            ])->count();
        }

        // fetch applicants
        $applications = JobApplication::where('job_id', $id)->with('user')->get();

        return view('front.jobDetail',[
            'job' => $job,
            'savedJobCount' => $savedJobCount,
            'applications' => $applications,
        ]);
    }

    public function applyJob(Request $req){
        $id = $req->id;
        $job = Job::where('id',$id)->first();

        // if job not found in db
        if($job == null){
            session()->flash('error', 'Job Does not exist!');
            return response()->json([
                'status' => false,
                'message' => 'Job Does not exist!'
            ]);
        }

        // you cant apply on your own job
        $employer_id = $job->user_id;
        if($employer_id == Auth::user()->id){
            session()->flash('error', 'you cant apply on your own job!');
            return response()->json([
                'status' => false,
                'message' => 'you cant apply on your own job!'
            ]);
        }

        // you can not apply on the same job for twice
        $jobApplicationCount = JobApplication::where([
            'user_id' => Auth::user()->id,
            'job_id' => $id,
        ])->count();

        if($jobApplicationCount > 0){
            session()->flash('error', 'You have Already Applied on this job!');
            return response()->json([
                'status' => false,
                'message' => 'You have Already Applied on this job!'
            ]);
        }

        $application = new JobApplication;
        $application->job_id = $id;
        $application->user_id = Auth::user()->id;
        $application->employer_id = $employer_id;
        $application->applied_date = now();
        $application->save();

        // send notification mail to employer
        $employer = User::where('id', $employer_id)->first();
        $mailData = [
            'employer' => $employer,
            'user' => Auth::user(),
            'job' => $job,
        ];
        Mail::to($employer->email)->send(new JobNotificationEmail($mailData));

        session()->flash('success', 'You have Successfully Applied!');
            return response()->json([
                'status' => true,
                'message' => 'You have Successfully Applied!'
            ]);

    }

    public function saveJob(Request $req){
        $id = $req->id;
        $job = Job::find($id);

        if ($job == null) {
            session()->flash('error', 'Job Does not exist!');
            return response()->json(["status" => false]);
        }

        // check if user already saved the job
        $savedJobCount = SavedJob::where([
                    'job_id' => $id, 
                    'user_id' => Auth::user()->id 
                ])->count();

        if ($savedJobCount > 0) {
            session()->flash('error', 'This Job is Already Saved!');
            return response()->json(["status" => false]);
        }

        $savedJob = new SavedJob;
        $savedJob->user_id = Auth::user()->id;
        $savedJob->job_id = $id;
        $savedJob->save();

        session()->flash('success', 'Job has been Saved successfully.');
        return response()->json(["status" => true]);

    }




}
