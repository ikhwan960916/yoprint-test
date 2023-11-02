<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\UserFileUpload;
Use Illuminate\Support\Facades\Log;
use App\Data\FileStatus;
use App\Events\FileStatusNotification;
use App\Models\User;

class ProcessCSV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // column definitions
    const UNIQUE_KEY_COLUMN      = 0;
    const PRODUCT_TITLE_COLUMN   = 1;
    const PRODUCT_DESC_COLUMN    = 2;
    const STYLE_COLUMN           = 3;
    const COLOR_NAME_COLUMN      = 14;
    const SIZE_COLUMN            = 18;
    const PIECE_PRICE_COLUMN     = 21;
    const SANMAR_MAINFRAME_COLOR = 28;

    public UserFileUpload $user_file_upload;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(UserFileUpload $user_file_upload)
    {
        $this->user_file_upload = $user_file_upload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('process csv file START');
        $user = User::findOrFail($this->user_file_upload->user_id);

        $this->sanitizeCSVFile($this->user_file_upload->file_name);

        $user_id = $user->id;

        $this->user_file_upload->status = 'PROCESSING';
        $this->user_file_upload->save();
        $this->broadcastCSVProcessingStatus($this->user_file_upload, 'PROCESSING', 50);

        $this->user_file_upload->status = 'DONE';
        $this->user_file_upload->save();
        $this->broadcastCSVProcessingStatus($this->user_file_upload, 'DONE', 100);
    }

    /**
     * Remove any non-UTF8 character from the csv and write them back to the same file
     */
    private function sanitizeCSVFile($file_name)
    {
        $csv_file_path = storage_path('app/public/uploads/') . $file_name;

        if (($handle = fopen($csv_file_path, 'r')) !== false) {
            $utf8Data = [];

            while (($data = fgetcsv($handle)) !== false) {
                foreach ($data as $key => $value) {
                    if (!mb_check_encoding($value, 'UTF-8')) {
                        $data[$key] = mb_convert_encoding($value, 'UTF-8', 'auto');
                    }
                }
                $utf8Data[] = $data;
            }
            fclose($handle);

            $outputCsvFilePath = $csv_file_path;
            $outputHandle = fopen($outputCsvFilePath, 'w');
            foreach ($utf8Data as $row) {
                fputcsv($outputHandle, $row);
            }
            fclose($outputHandle);
        }
        Log::info('File' . $file_name . ' sanitized.');
    }

    private function broadcastCSVProcessingStatus(UserFileUpload $user_file_upload, $status, $progress_percentage)
    {
        $file_status = new FileStatus(
            $user_file_upload->id, 
            $user_file_upload->uploaded_at, 
            $user_file_upload->file_path,
            $user_file_upload->file_name
        );
        $file_status = $file_status->setStatusAndProgress($status, $progress_percentage);
        broadcast(new FileStatusNotification($user_file_upload->user_id, $file_status));
    }
}
