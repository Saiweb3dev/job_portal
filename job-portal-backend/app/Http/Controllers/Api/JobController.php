<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $query = Job::with(['user', 'category']);

        // filtering options
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status') && $request->status === 'open') {
            $query->where('status', 'open')
                  ->where('deadline', '>=', now()->toDateString());
        }

        $jobs = $query->latest()->paginate(10);
        return response()->json($jobs);
    }

    public function show($id)
    {
        $job = Job::with(['user', 'category'])->findOrFail($id);
        return response()->json($job);
    }

    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'type' => 'required|in:full-time,part-time,remote,contract',
            'category_id' => 'required|exists:categories,id',
            'deadline' => 'required|date|after:today',
        ]);

        // Create job
        $job = new Job($validated);
        $job->user_id = Auth::id();
        $job->status = 'open';
        $job->applications_count = 0;
        $job->save();

        return response()->json([
            'message' => 'Job created successfully',
            'job' => $job->load('category', 'user')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        // Find job
        $job = Job::findOrFail($id);

        // Check if user owns this job
        if ($job->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate request
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'location' => 'sometimes|required|string|max:255',
            'salary' => 'sometimes|nullable|numeric|min:0',
            'type' => 'sometimes|required|in:full-time,part-time,remote,contract',
            'category_id' => 'sometimes|required|exists:categories,id',
            'deadline' => 'sometimes|required|date|after:today',
            'status' => 'sometimes|required|in:open,closed',
        ]);

        // Update job
        $job->update($validated);

        return response()->json([
            'message' => 'Job updated successfully',
            'job' => $job->fresh()->load('category', 'user')
        ]);
    }

    public function destroy($id)
    {
        // Find job
        $job = Job::findOrFail($id);

        // Check if user owns this job
        if ($job->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if job has applications
        if ($job->applications_count > 0) {
            return response()->json([
                'message' => 'Cannot delete job with existing applications'
            ], 400);
        }

        // Delete job
        $job->delete();

        return response()->json([
            'message' => 'Job deleted successfully'
        ]);
    }
}
