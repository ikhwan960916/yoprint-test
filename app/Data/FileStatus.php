<?php
namespace App\Data;
use App\Data\FileProgress;

class FileStatus {
    public $file_id;
    public $uploaded_time;
    public $file_path;
    public $file_name;
    public FileProgress $file_progress;

    public function __construct($file_id, $uploaded_time, $file_path,$file_name)
    {
        $this->file_id =  $file_id;    
        $this->uploaded_time = $uploaded_time;
        $this->file_path = $file_path;
        $this->file_name = $file_name; 
    }

    public function setStatusAndProgress($status, $progress)
    {
        $this->file_progress = new FileProgress($status, $progress);
        return $this;
    }
}