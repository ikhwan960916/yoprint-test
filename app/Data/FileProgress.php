<?php
namespace App\Data;

class FileProgress {
    public $status;
    public $progress_percentage;

    public function __construct($status, $progress_percentage){
        $this->status = $status;
        $this->progress_percentage = $progress_percentage;
    }
}