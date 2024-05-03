<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\User;

class HomeController extends Controller
{
    // home page show
    public function index(){

        $categories = Category::where('status', 1)->orderBy('name','ASC')->take(8)->get();

        $newCategories = Category::where('status', 1)->orderBy('name','ASC')->get();

        $featuredjobs = Job::where('status', 1)
                        ->orderBy('created_at','DESC')
                        ->with('jobType')
                        ->where('isFeatured', 1)->take(6)->get();

        $latestjobs = Job::where('status', 1)
                        ->with('jobType')
                        ->orderBy('created_at','DESC')
                        ->take(6)->get();

        return view ('front.home',[
            'categories' => $categories,
            'featuredjobs' => $featuredjobs,
            'latestjobs' => $latestjobs,
            'newCategories' => $newCategories,
        ]);
    }
}
