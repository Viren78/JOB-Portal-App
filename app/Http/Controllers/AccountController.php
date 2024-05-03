<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordEmail;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class AccountController extends Controller
{
    //this method show user register page
    public function registration(){
        return view('front.account.registration');
    }

    // save the user method
    public function processRegistration(Request $req){
        $validator = Validator::make($req->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|same:confirm_password',
            'confirm_password' => 'required',
        ]);

        if($validator->passes()){

            $user = new User();
            $user->name = $req->name;
            $user->email = $req->email;
            $user->password = Hash::make($req->password);
            $user->save();

            session()->flash('success', 'You have registerd successfully');

            return response()->json([
                'status' => true,
                'errors' => [],
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    //this method show user login page
    public function login(){
        return view('front.account.login');
    }

    //this method show user login page
    public function authenticate(Request $req){
        $validator = Validator::make($req->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if($validator->passes()){
            if(Auth::attempt(['email' => $req->email, 'password' => $req->password])){
                return redirect()->route('account.profile')->with('success', 'Logged in Successfully.');
            }else{
                return redirect()
                ->route('account.login')
                ->with('error', 'Either Email or Password is Incorrect');
            }
        }else{
            return redirect()->route('account.login')
            ->withInput($req->only('email'))
            ->withErrors($validator);
        }
        return view('front.account.login');
    }

    // profile method
    public function profile(){
        $id = Auth::user()->id;
        // $user = User::where('id', $id)->first();
        $user = User::find($id);
        // dd( $user );
        return view('front.account.profile', ['user' => $user]);
    }

    public function updateProfile(Request $req){
        $id = Auth::user()->id;
        $validator = Validator::make($req->all(),[
            'name' => 'required|min:2|max:20',
            'email' => 'required|email|unique:users,email,'.$id.',id',
            'designation' => 'required|',
            'mobile' => 'required|'
        ]);

        if($validator->passes()){
            $user = User::find($id);
            $user->name = $req->name;
            $user->email = $req->email;
            $user->designation = $req->designation;
            $user->mobile = $req->mobile;
            $user->save();

            session()->flash('success', 'Profile has been updated.');

            return response()->json([
                'status' => true,
                'errors' => [],
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function updateProfilePic(Request $req){
        $id = Auth::user()->id;
        $validator = Validator::make($req->all(),[
            'image' => 'required|image'
        ]);

        if($validator->passes()){

            $image = $req->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = $id.'-'.time().'.'.$ext;
            $image->move(public_path('profile_pic'), $imageName);

            // create image thumbnail
            $sourcePath = public_path('profile_pic/'.$imageName);
            $manager = new ImageManager(new Driver());

            // read image from file system
            $image = $manager->read($sourcePath);

            // resize image proportionally to 300px width
            $image->cover(150, 150);
            // save modified image in new format 
            $image->toPng()->save(public_path('profile_pic/thumb/'.$imageName));

            // delete old profile pic
            File::delete(public_path('profile_pic/'.Auth::user()->image));
            File::delete(public_path('profile_pic/thumb/'.Auth::user()->image));

            User::where('id', $id)->update(['image' => $imageName]);

            session()->flash('success', 'Profile Pic Updated.');

            return response()->json([
                'status' => true,
                'errors' => []
            ]);

        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function logout(){
        Auth::logout();
        return redirect()->route('account.login')->with('success', 'You have been Logged Out');
    }

    public function createJob(){

        $categories = Category::orderBy('name', 'ASC')->where('status', 1)->get();

        $jobTypes = JobType::orderBy('name', 'ASC')->where('status', 1)->get();

        return view('front.account.job.create', [
            'categories' => $categories,
            'jobTypes' => $jobTypes,
        ]);
    }

    public function saveJob(Request $req){
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

            $job = new Job();
            $job->title = $req->title;
            $job->category_id = $req->category;
            $job->job_type_id = $req->jobType;
            $job->user_id = Auth::user()->id;
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
            $job->save();

            session()->flash('success', 'Job saved successfully.');

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

    public function myJob(){

        $jobs = Job::where('user_id', Auth::user()->id)->orderBy('created_at', 'DESC')->with('jobType')->paginate(10);
        return view('front.account.job.my-jobs',[
            'jobs' => $jobs,
        ]);
    }


    public function editJob(Request $req, $id){
        $categories = Category::orderBy('name', 'ASC')->where('status', 1)->get();
        $jobTypes = JobType::orderBy('name', 'ASC')->where('status', 1)->get();

        $job = Job::where([
            'user_id' => Auth::user()->id,
            'id' => $id,
        ])->first();

        if($job == null){
            abort(404);
        }

        return view('front.account.job.edit',[
            'categories' => $categories,
            'jobTypes' => $jobTypes,
            'job' => $job,
        ]);
    }

    public function updateJob(Request $req, $id){
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
            $job->user_id = Auth::user()->id;
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

    public function deleteJob(Request $req){
        $job = Job::where([
            'user_id' =>  Auth::user()->id,
            'id'=>$req->jobId,
        ])->first();

        if($job == null){
            session()->flash('error', 'Either job deleted or not found.');
            // abort(404);
            return response()->json([
                'status' => true,
            ]);
        }
        Job::where('id', $req->jobId)->delete();
        session()->flash('success', 'Job Deleted Successfully!');
        return response()->json([
            'status' => true,
        ]);
        
    }

    public function myJobApplication(){
        $jobApplications = JobApplication::where('user_id', Auth::user()->id)
                        ->with('job','job.jobType', 'job.applications')
                        ->paginate(10);
        return view('front.account.job.my-job-applications', [
            'jobApplications' => $jobApplications,

        ]);
    }

    public function removeJobs(Request $req){
        $jobApplication = JobApplication::where([
                                                    'id' => $req->id, 
                                                    'user_id' => Auth::user()->id 
                                                ])
                                        ->first();

        if($jobApplication == null){
            session()->flash('error', 'job application not found!');
            return response()->json([
                'status' => false,
            ]);
        }
        JobApplication::find($req->id)->delete();
        session()->flash('success', 'Job Application Removed Successfully!');
        return response()->json([
            'status' => true,
        ]);
    }

    public function savedJobs(){
        // $jobApplications = JobApplication::where('user_id', Auth::user()->id)
        //                 ->with('job','job.jobType', 'job.applications')
        //                 ->paginate(10);

        $savedJobs = SavedJob::where([
            'user_id' => Auth::user()->id,
            ])->with('job', 'job.jobType', 'job.applications')
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return view('front.account.job.saved-jobs', [
            'savedJobs' => $savedJobs,

        ]);
    }

    public function removeSavedJob(Request $req){
        $savedJob = SavedJob::where([
                                        'id' => $req->id, 
                                        'user_id' => Auth::user()->id 
                                        ])->first();

        if($savedJob == null){
            session()->flash('error', 'Job not found!');
            return response()->json([
                'status' => false,
            ]);
        }
        SavedJob::find($req->id)->delete();
        session()->flash('success', 'Job Removed Successfully!');
        return response()->json([
            'status' => true,
        ]);
    }

    public function updatePassword(Request $req){
        $validator = Validator::make($req->all(),[
            'old_password'=>'required|min:6',
            'new_password'=>'required|different:old_password|min:6',
            'password_confirmation'=>'required|same:new_password|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

        if (Hash::check($req->old_password, Auth::user()->password) == false) {
            session()->flash('error', 'please enter the old password, your old password is incorrect!');
            return response()->json([
                'status' => true,
            ]);
        }

        $user = User::find(Auth::user()->id);
        $user->password = Hash::make($req->new_password);
        $user->save();

        session()->flash('success', 'Your Password has been Updated!');
        return response()->json([
            'status' => true,
        ]);

    }

    public function forgotPassword(){
        return view('front.account.forgot-password');
    }

    public function processForgotPassword(Request $req){
        $validator = Validator::make($req->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.forgotPassword')->withInput()->withErrors($validator);
        }

        $token = Str::random(60);

        // if this email record exists in db, so first we have to delete it,
        \DB::table('password_reset_tokens')->where('email', $req->email)->delete();

        \DB::table('password_reset_tokens')->insert([
            'email' => $req->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        //  send email
        $user = User::where('email', $req->email)->first();
        $mailData = [
            'token' => $token,
            'user' => $user,
            'subject' => 'You have requested to change your password.'
        ];
        Mail::to($req->email)->send(new ResetPasswordEmail($mailData));

        return redirect()->route('account.forgotPassword')->with('success', 'Reset Password Email has been sent to your inbox.');

    }

    public function resetPassword($tokenString){
        $token = \DB::table('password_reset_tokens')->where('token', $tokenString)->first();
    
        if ($token == null) {
            return redirect()->route('account.forgotPassword')->with('error', 'Invalild token.');
        }

        return view('front.account.reset-password', [
            'tokenString' => $tokenString,
        ]);
        
    }

    public function processResetPassword(Request $req){

        $token = \DB::table('password_reset_tokens')->where('token', $req->token)->first();
    
        if ($token == null) {
            return redirect()->route('account.forgotPassword')->with('error', 'Invalild token.');
        }

        $validator = Validator::make($req->all(),[
            'new_password'=>'required|min:6',
            'confirm_password'=>'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.resetPassword', $req->token)->withErrors($validator);
        }

        //update the user password
        User::where('email', $token->email)->update([
            'password' => Hash::make($req->new_password),
        ]);

        \DB::table('password_reset_tokens')->where('email', $req->email)->delete();
        return redirect()->route('account.login')->with('success','Your Password has been successfully updated. Please login to your account now!');
        //delete the token from database

    }



}
