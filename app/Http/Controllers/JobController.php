<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; // Import the Str facade


class JobController extends Controller
{


    public function create(Request $request)
    {
        $status = ($request->status == 'yes') ? true : false;
    
        $rules = [
            'title' => 'required|string',
            'photo1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // 'video' => 'nullable|file|mimes:mp4,mov,avi|max:20480', // Example rules
            'posted_date' => 'required|date',
            //'status'=>'required|boolean',
            'deadline' => 'required|string',
            'location' => 'required|string',
            // 'document' => 'nullable|file|mimes:pdf,doc,docx|max:20480', // Nullable field, accepts document files (file type), allowed file extensions are pdf, doc, and docx, maximum file size is 20 MB
            'categoryID' => 'nullable|exists:categories,id', // Make sure the category exists
            'description' => 'required|string',
        ];
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
    
        // Generate slug from the title
        $slug = Str::slug($request->title);
    
        $file = $request->file('photo1');
        $video = $request->file('video');
        $document = $request->file('document');
    
        $filenames = [];
    
        if ($file && $file->isValid()) {
            $originalName = $file->getClientOriginalName();
            $filenames['photo1'] = $originalName;
            $file->storeAs('public', $originalName);
        }
    
        if ($video && $video->isValid()) {
            $originalName = $video->getClientOriginalName();
            $filenames['video'] = $originalName;
            $video->storeAs('public', $originalName);
        }
    
        if ($document && $document->isValid()) {
            $originalName = $document->getClientOriginalName();
            $filenames['document'] = $originalName;
            $document->storeAs('public', $originalName);
        }
    
        DB::table('jobs')->insert([
            'title' => $request->title,
            'slug' => $slug, // Insert the generated slug into the database
            'location' => $request->location,
            'posted_date' => $request->posted_date,
            'deadline' => $request->deadline,
            'description' => $request->description,
            'categoryID' => $request->categoryID,
            'photo1' => $filenames['photo1'] ?? null,
            'document' => $filenames['document'] ?? null,
            'video' => $filenames['video'] ?? null,
            'status' => $status,
        ]);
    
        $job = DB::table('jobs')->orderByDesc('id')->first();
    
        return response()->json(['message' => 'Job created successfully', 'job' => $job], 201);
    }
    
    public function index()
    {
        $jobs = DB::table('jobs')
            ->where('deadline', '>=', Carbon::today()->toDateString())
            ->get();

        return response()->json($jobs);;
    }

    public function visibleJobs()
    {
        $jobs = DB::table('jobs')
            ->where('deadline', '>=', Carbon::today()->toDateString())
            ->where('status', true) // Add condition for status equal to true
            ->get();
            return response()->json($jobs);


    }

    public function show($slug)
    {
        $job = DB::table('jobs')->where('slug', $slug)->first();
    
        if (!$job) {
            return response()->json(['error' => 'Job not found'], 404);
        }
    
        return response()->json($job);
    }
    

    public function update(Request $request, $id)
    {

        $status = ($request->status == 'yes') ? true : false;

        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'photo1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'posted_date' => 'sometimes|required|date',
            'deadline' => 'sometimes|required|string',
            'location' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            //'status' => 'sometimes|required|boolean', // Include status field in validation rules
        ]);

        // Add status field to the data array
        $data['status'] = $status;


        $job = DB::table('jobs')->where('id', $id)->update($data);

        return response()->json(['message' => 'Job updated successfully', 'job' => $job]);
    }

    public function destroy($id)
    {
        DB::table('jobs')->where('id', $id)->delete();

        return response()->json(['message' => 'Job deleted successfully']);
    }


    public function incrementViews(Request $request, $id)
    {
        // Retrieve the job from the database
        $job = DB::table('jobs')->where('id', $id)->first();

        // Check if the job exists
        if ($job) {
            // Increment the views count
            $newViewsCount = $job->views_count + 1;

            // Update the views count in the database
            DB::table('jobs')->where('id', $id)->update(['views_count' => $newViewsCount]);

            return response()->json(['message' => 'View count incremented successfully'], 200);
        } else {
            // Job not found, return error response
            return response()->json(['error' => 'Job not found'], 404);
        }
    }
}
