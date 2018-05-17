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
    
    public $manualTimestampColumn;
    
    /**
     * Constructor
     *
     * Instantiates the class by making sure there's a valid file to read/write to before creating the file-handling object.
     *
     * @return void
     */
    public function __construct($filePath, $timeLimit = null)
    {
        $this->registerCSV($filePath, $timeLimit);
        $this->timeFormat = 'Y-m-d H:i';
        $this->file = new FileHandler($this->filePath, $this);
    }
    
    
    /**
     * Runs the CSV inspection and truncation process.
     */
    public function ensureCompliance()
    {
        if($this->file->getPointer()) {
            $this->file->iterateRows();
        }
        
    }
    
    /**
     * Set the time format for timestamps in the script so that .
     *
     * @param string $format Any valid timestamp format compatible with the DateTime class.
     *
     * @return void
     */
    public function setTimeFormat($timeFormat = null)
    {
        if(!is_null($timeFormat)) {
            $this->timeFormat = $timeFormat;
        }
    }
    
    /**
     * Manually set the column to use for a timestamp.
     *
     * @param $column
     */
    public function setManualTimestampColumn($column)
    {
        $this->manualTimestampColumn = $column;
    }
    
    /**
     * Checks if the provided file exists and sets a default time limit if one is not set.
     *
     * @param string $filePath A path to the file to be operated on.
     * @param int $timeLimit A time limit beyond which to remove rows, expressed in seconds.
     *
     * @return void
     */
    public function registerCSV($filePath, $timeLimit = null)
    {
        if(is_null($timeLimit)) {
            $timeLimit = 15780000;
        };
        
        // If it's not a valid file path, or we aren't allowed to touch it, then stop before we do anything odd.
        if (!is_file($filePath)) {
            trigger_error('There was an invalid or unreadable file path detected. Truncation has stopped.', E_USER_WARNING);
            return false;
        }
        
        $this->filePath = $filePath;
        $this->horizon = $timeLimit;
    }
    
    /**
     * Iterates over a CSV row to find the first cell containing a timestamp. Can be overridden to fetch a specific column.
     *
     * @param $row array CSV row, formatted as an array for iteration.
     *
     * @return \DateTime
     */
    public function retrieveTimeCell($row = null)
    {
        
        if(isset($this->manualTimestampColumn)
           && date_create_from_format($this->timeFormat, $row[$this->manualTimestampColumn])) {
            return $row[$this->manualTimestampColumn];
        }
    
        foreach($row as $cell) {
            if(date_create_from_format($this->timeFormat, $cell)) {
                return $cell;
            }
        }
        
        trigger_error('There was an invalid or unreadable timestamp detected. Please manually review the
            output at the specified file path. The row will NOT be deleted.', E_USER_WARNING);
        
        return date($this->timeFormat);
        
    }
   
    /**
     * Evaluates a given row's timestamp to see if the row should be removed or retained.
     *
     * @param $row array Given CSV row.
     *
     * @return bool True if row is to be kept, false if row should be removed.
     */public function evalRowTime($row) {
        $nowTime = new \DateTime('now');
        $this->coerceTimeFormat($this->retrieveTimeCell($row));
        /** @noinspection PhpUndefinedMethodInspection */
        $difference = $nowTime->getTimestamp() - $this->time->getTimestamp();
        
        if($difference >= $this->horizon) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Takes a string timestamp and converts it into a DateTime object we can use internally.
     *
     * @param $timeCell
     */
    public function coerceTimeFormat($timeCell) {
        $time = \DateTime::createFromFormat($this->timeFormat, $timeCell);
        $this->time = $time;
    }
    
}
