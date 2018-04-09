<?php

namespace Cgit;

use Cgit\EventHorizon\FileHandler;

class EventHorizon
{
    public $id;
    
    public $filePath;
    
    public $horizon;
    
    public $timeFormat;
    
    public $time;
    
    public $file;
    
    public function __construct($filePath, $timeLimit = null)
    {
        echo $filePath;
        $this->registerCSV($filePath, $timeLimit);
        $this->timeFormat = 'Y-m-d H:i';
        $this->file = new FileHandler($this->filePath, $this);
    }
    
    public function ensureCompliance() {
        $this->file->iterateRows();
    }
    
    public function setTimeFormat($format = null) {
        if(!is_null($timeFormat)) {
            $this->timeFormat = $format;
        }
    }
    
    public function registerCSV($filePath, $timeLimit = null)
    {
        if(is_null($timeLimit)) {
            $timeLimit = 15780000;
        };
        
        // If it's not a valid file path, or we aren't allowed to touch it, then stop before we do anything odd.
        if (!is_file($filePath)) {
            trigger_error('There was an invalid or unreadable file path detected. Truncation has stopped.', E_USER_WARNING);
            exit();
        }
        
        $this->filePath = $filePath;
        $this->horizon = $timeLimit;
    }
    
    public function retrieveTimeCell($row = null)
    {
      
        foreach($row as $cell) {
            if(strtotime($cell)) {
                return $cell;
            }
        }
     
        trigger_error('There was an invalid or unreadable timestamp detected. Please manually review the
            output at the specified file path. The row will NOT be deleted.', E_USER_WARNING);
        
        return new \DateTime('now');
        
    }
    
    // Returns false if truncation is necessary, true if not.
    public function evalRowTime($row) {
        $nowTime = new \DateTime('now');
        $this->coerceTimeFormat($this->retrieveTimeCell($row));
        /** @noinspection PhpUndefinedMethodInspection */
        $difference = $nowTime->getTimestamp() - $this->time->getTimestamp();
        
        if($difference >= $this->horizon) {
            return false;
        }
        
        return true;
    }
    
    public function coerceTimeFormat($timeCell) {
        $time = \DateTime::createFromFormat($this->timeFormat, $timeCell);
        $this->time = $time;
    }
    
}