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
            $filename = time() . '_' . $originalName; // Add timestamp to avoid conflicts
            $filenames['photo1'] = $filename;
            $file->storeAs('public', $filename);
            
            // Log the file storage for debugging
            \Log::info('Image stored: ' . $filename . ' in public disk');
        }

        if ($video && $video->isValid()) {
            $originalName = $video->getClientOriginalName();
            $filename = time() . '_' . $originalName;
            $filenames['video'] = $filename;
            $video->storeAs('public', $filename);
            
            \Log::info('Video stored: ' . $filename . ' in public disk');
        }

        if ($document && $document->isValid()) {
            $originalName = $document->getClientOriginalName();
            $filename = time() . '_' . $originalName;
            $filenames['document'] = $filename;
            $document->storeAs('public', $filename);
            
            \Log::info('Document stored: ' . $filename . ' in public disk');
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
        ->select('jobs.*')
        ->get();
    return response()->json($jobs);
}

public function visibleJobs()
{
    $jobs = DB::table('jobs')
        ->select('jobs.*')
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

    public function serveImage($filename)
    {
        $path = storage_path('app/public/' . $filename);
        
        if (!file_exists($path)) {
            \Log::error('Image not found: ' . $path);
            return response()->json(['error' => 'Image not found: ' . $filename], 404);
        }
        
        $mimeType = mime_content_type($path);
        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    public function serveDocument($filename)
    {
        $path = storage_path('app/public/' . $filename);
        
        if (!file_exists($path)) {
            \Log::error('Document not found: ' . $path);
            return response()->json(['error' => 'Document not found: ' . $filename], 404);
        }
        
        $mimeType = mime_content_type($path);
        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($filename) . '"',
        ]);
    }

    public function listFiles()
    {
        $files = [];
        $storagePath = storage_path('app/public/');
        
        if (is_dir($storagePath)) {
            $fileList = scandir($storagePath);
            foreach ($fileList as $file) {
                if ($file != '.' && $file != '..' && !is_dir($storagePath . $file)) {
                    $files[] = [
                        'name' => $file,
                        'size' => filesize($storagePath . $file),
                        'modified' => date('Y-m-d H:i:s', filemtime($storagePath . $file)),
                        'url' => url('storage/' . $file),
                        'api_url' => url('api/images/' . $file)
                    ];
                }
            }
        }
        
        return response()->json($files);
    }

    public function cleanStorage()
    {
        $storagePath = storage_path('app/public/');
        $files = [];
        
        if (is_dir($storagePath)) {
            $fileList = scandir($storagePath);
            foreach ($fileList as $file) {
                if ($file != '.' && $file != '..' && !is_dir($storagePath . $file)) {
                    $files[] = $file;
                }
            }
        }
        
        return response()->json([
            'message' => 'Storage directory contents',
            'files' => $files,
            'count' => count($files),
            'path' => $storagePath
        ]);
    }

    public function fixStorage()
    {
        $basePath = base_path();
        $storagePath = storage_path('app/public/');
        $publicStoragePath = public_path('storage');
        
        $results = [];
        
        // 1. Remove existing storage link if it exists
        if (is_link($publicStoragePath) || is_dir($publicStoragePath)) {
            if (is_link($publicStoragePath)) {
                unlink($publicStoragePath);
                $results[] = "✓ Removed existing symbolic link";
            } else {
                rmdir($publicStoragePath);
                $results[] = "✓ Removed existing directory";
            }
        } else {
            $results[] = "✓ No existing storage link found";
        }
        
        // 2. Create storage directory if it doesn't exist
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
            $results[] = "✓ Created storage directory";
        } else {
            $results[] = "✓ Storage directory already exists";
        }
        
        // 3. Create storage link
        if (symlink($storagePath, $publicStoragePath)) {
            $results[] = "✓ Storage link created successfully";
        } else {
            $results[] = "✗ Failed to create storage link";
        }
        
        // 4. Set permissions
        chmod($storagePath, 0755);
        if (is_link($publicStoragePath)) {
            chmod($publicStoragePath, 0755);
        }
        $results[] = "✓ Permissions set";
        
        // 5. List current files
        $files = [];
        if (is_dir($storagePath)) {
            $fileList = scandir($storagePath);
            foreach ($fileList as $file) {
                if ($file != '.' && $file != '..' && !is_dir($storagePath . $file)) {
                    $files[] = [
                        'name' => $file,
                        'size' => filesize($storagePath . $file),
                        'modified' => date('Y-m-d H:i:s', filemtime($storagePath . $file))
                    ];
                }
            }
        }
        
        // 6. Test the link
        $linkTest = is_link($publicStoragePath) ? "✓ Link exists" : "✗ Storage link not found";
        $results[] = $linkTest;
        
        return response()->json([
            'success' => true,
            'message' => 'Storage setup completed',
            'results' => $results,
            'files' => $files,
            'file_count' => count($files),
            'storage_path' => $storagePath,
            'public_storage_path' => $publicStoragePath,
            'base_url' => url('/'),
            'test_urls' => [
                'storage_link' => url('storage/'),
                'api_images' => url('api/images/'),
                'api_documents' => url('api/documents/'),
                'list_files' => url('api/files'),
                'storage_info' => url('api/storage-info')
            ]
        ]);
    }
}
