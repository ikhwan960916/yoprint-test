<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserFileUpload;
use Carbon\Carbon;
use App\Events\FileStatusNotification;
use App\Data\FileStatus;
use App\Data\FileProgress;

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

        $file_name = time() . '_' . $request->file->getClientOriginalName();
        $file_path = $request->file('file')->storeAs('uploads', $file_name, 'public');

        $user_file_upload = new UserFileUpload([
            'user_id' => auth()->user()->id,
            'file_path' => $file_path,
            'status' => 'PENDING',
            'uploaded_at' => Carbon::now(),
        ]);
        $user_file_upload->save();

        return 'File ' . $file_path . 'Uploaded';
    }

    public function test()
    {
        $file_progress = new FileProgress('pending', '11%');
        $file_status = new FileStatus('1', 'time', 'test_file_name', $file_progress);
        broadcast(new FileStatusNotification(auth()->user(), $file_status));
        
        return 'Ok';
    }
}
