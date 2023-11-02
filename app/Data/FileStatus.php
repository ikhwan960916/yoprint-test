<?php
namespace App\Data;
use App\Data\FileProgress;

class FileStatus {
    public $file_id;
    public $uploaded_time;
    public $file_name;
    public FileProgress $file_progress;

    public function __construct($file_id, $uploaded_time, $file_name, $file_progress)
    {
        $this->file_id =  $file_id;    
        $this->uploaded_time = $uploaded_time;
        $this->file_name = $file_name;
        $this->file_progress = $file_progress;  
    }
}