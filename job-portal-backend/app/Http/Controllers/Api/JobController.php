<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $jobs = Job::with(['user', 'category'])
            ->latest()
            ->paginate(10);

        return response()->json($jobs);
    }

    public function show($id)
    {
        $job = Job::with(['user', 'category'])->findOrFail($id);
        return response()->json($job);
    }
}
