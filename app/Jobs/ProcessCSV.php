<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
Use Illuminate\Support\Facades\Log;
use App\Data\FileStatus;
use App\Events\FileStatusNotification;
use Illuminate\Support\Facades\Cache;
use App\Models\UserFileUpload;
use App\Models\Product;
use App\Models\User;

class ProcessCSV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 0;

    // column definitions
    const UNIQUE_KEY_COLUMN      = 0;
    const PRODUCT_TITLE_COLUMN   = 1;
    const PRODUCT_DESC_COLUMN    = 2;
    const STYLE_COLUMN           = 3;
    const COLOR_NAME_COLUMN      = 14;
    const SIZE_COLUMN            = 18;
    const PIECE_PRICE_COLUMN     = 21;
    const SANMAR_MAINFRAME_COLOR_COLUMN = 28;

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
        $this->sanitizeCSVFile($this->user_file_upload->file_name);
        $this->updateProducts($this->user_file_upload->file_name);
        Log::info('process csv file COMPLETED');
    }

    /**
     * Update products by csv
     */
    private function updateProducts($file_name)
    {
        $this->user_file_upload->status = 'PROCESSING';
        $this->user_file_upload->save();
        $this->broadcastCSVProcessingStatus($this->user_file_upload, 'PROCESSING', 0);

        $row_count = $this->countCSVRows($file_name);

        $csv_file_path = storage_path('app/public/uploads/') . $file_name;

        $prev_percentage = 0;
        if (($handle = fopen($csv_file_path, 'r')) !== false) {

            $current_row = 1;
            while (($data = fgetcsv($handle)) !== false) {
                
                // critical section
                $unique_key = intval($data[self::UNIQUE_KEY_COLUMN]);
                Cache::lock("$unique_key", 5)->block(5, function() use ($unique_key, $data, &$current_row, &$prev_percentage, $row_count){
                    try{

                        // Upsert operation
                        $product = Product::where('unique_key', $unique_key)->first() ?? new Product;
                        $product->unique_key = $unique_key;
                        $product->title = $data[self::PRODUCT_TITLE_COLUMN];
                        $product->description = $data[self::PRODUCT_DESC_COLUMN];
                        $product->style = $data[self::STYLE_COLUMN];
                        $product->sanmar_mainframe_size = $data[self::SANMAR_MAINFRAME_COLOR_COLUMN];
                        $product->size = $data[self::SIZE_COLUMN];
                        $product->color_name = $data[self::COLOR_NAME_COLUMN];
                        $product->piece_price = (float) $data[self::PIECE_PRICE_COLUMN];

                        /**
                         * Database operation will be performed if the 
                         * product model is dirty (i.e has changes on columns).
                         */
                        $product->save();

                    } catch (\Exception $e){
                        $this->broadcastCSVProcessingStatus($this->user_file_upload, 'FAILED', 0);
                        throw $e;
                    }

                    $current_row++;

                    $percentage_progress = round(($current_row / $row_count) * 100);
                    if ($percentage_progress < 100 && $prev_percentage != $percentage_progress){
                        $prev_percentage = $percentage_progress;
                        $this->broadcastCSVProcessingStatus($this->user_file_upload, 'PROCESSING', $percentage_progress);
                    }
                    
                });

                /**
                 * Simulating delays for the test file or else would be too fast, i
                 * In production this code should not be there
                 */
                if ($row_count < 20)
                {
                    sleep(1);
                }

            }
            fclose($handle);
        }

        $this->user_file_upload->status = 'DONE';
        $this->user_file_upload->save();
        $this->broadcastCSVProcessingStatus($this->user_file_upload, 'DONE', 100);
    }

    private function countCSVRows($file_name)
    {
        $csv_file_path = storage_path('app/public/uploads/') . $file_name;
        if (($handle = fopen($csv_file_path, 'r')) !== false) {
            $current_row = 1;
            $row_count = 0;
            while (!feof($handle)) {
                fgetcsv($handle);
                $row_count++;
            }
            $row_count = $row_count - 1;
        }

        return $row_count;
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
