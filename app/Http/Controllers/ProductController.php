<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserFileUpload;
use Carbon\Carbon;
use App\Events\FileStatusNotification;
use App\Data\FileStatus;
use App\Data\FileProgress;
use App\Jobs\ProcessCSV;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function showUploadProductView()
    {
        return view('product.upload-csv');
    }

    public function uploadCSV(Request $request)
    {
        $request->validate([
            'file' => 'required'
        ]);

        if ($request->file('file')->getMimeType() != 'text/csv'){
            return back()->withErrors(['file' => 'Only accepts csv file']);
        }

        // Using file content to detect same file
        // using lock to ensure that only 1 instance of file with same content are uploaded.
        $file = $request->file('file');
        $content_hash = hash_file('sha256', $file->getPathname());

        // handling race conditions
        $lock = Cache::lock($content_hash, 20);
        $file_name = time() . '_' . $request->file->getClientOriginalName();
        if ($lock->get()){
            $file_path = $request->file('file')->storeAs('uploads', $file_name, 'public');

            $user_file_upload = new UserFileUpload([
                'user_id' => auth()->user()->id,
                'file_name' => $file_name,
                'file_path' => $file_path,
                'status' => 'PENDING',
                'uploaded_at' => Carbon::now(),
            ]);
            $user_file_upload->save();

            // Broadcast status
            $file_status = new FileStatus(
                $user_file_upload->id, 
                $user_file_upload->uploaded_at, 
                $user_file_upload->file_path,
                $user_file_upload->file_name
            );
            $file_status = $file_status->setStatusAndProgress('PENDING', 0);
            broadcast(new FileStatusNotification($user_file_upload->user_id, $file_status));

            // freeze to show PENDING on upload ui
            sleep(3);

            ProcessCSV::dispatch($user_file_upload);

            $lock->release();

            return 'File ' . $file_path . 'Uploaded';
        } else {
            return response("Same file upload detected.", 400)
                    ->header('Content-Type', 'text/plain');
        }
    }

}
