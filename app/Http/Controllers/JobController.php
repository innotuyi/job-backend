<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{

    public function create(Request $request)
    {
        $rules = [
            'title' => 'required|string',
            'photo1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'posted_date' => 'required|date',
            'deadline' => 'required|string',
            'location' => 'required|string',
            'categoryID' => 'nullable|exists:categories,id', // Make sure the category exists
            'description' => 'required|string',
        ];
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $file = $request->file('photo1');

        if ($file && $file->isValid()) {
            $originalName = $file->getClientOriginalName();
            $filenames['photo1'] = $originalName;
            $file->storeAs('public', $originalName);
        }

        $job = Job::create([
            'title' => $request->title,
            'location' => $request->location,
            'posted_date' => $request->posted_date,
            'deadline' => $request->deadline,
            'description' => $request->description,
            'categoryID' => $request->categoryID,
            'photo1' => $filenames['photo1'] ?? null,
          
        ]);


      




        return response()->json(['message' => 'Job created successfully', 'job' => $job], 201);
    }

    public function index()
    {
        $jobs = Job::all();
        return response()->json($jobs);
    }

    public function show($id)
    {
        $job = Job::findOrFail($id);

        return response()->json($job);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'photo1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'posted_date' => 'sometimes|required|date',
            'deadline' => 'sometimes|required|string',
            'location' => 'sometimes|required|string',
            'categoryID' => 'sometimes|nullable|exists:categories,id',
            'description' => 'sometimes|required|string',
        ]);

        $job = Job::findOrFail($id);
        $job->update($data);

        return response()->json(['message' => 'Job updated successfully', 'job' => $job]);
    }

    public function destroy($id)
    {
        $job = Job::findOrFail($id);
        $job->delete();

        return response()->json(['message' => 'Job deleted successfully']);
    }
}
