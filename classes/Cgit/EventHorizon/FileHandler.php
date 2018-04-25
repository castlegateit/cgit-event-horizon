<?php

namespace Cgit\EventHorizon;

use Cgit\EventHorizon;

class FileHandler
{
    private $pointer = false;
    private $delimiter;
    private $length = 0;
    private $instance;
    private $compliantLines = array();
    public $data;
    
    /**
     * FileHandler constructor.
     *
     * @param string $filePath
     * @param EventHorizon $instance
     */
    public function __construct($filePath, $instance)
    {
        $this->instance = $instance;
        $this->pointer = fopen($filePath, "r");
        $this->delimiter = ",";
    }
    
    /**
     * FileHandler destructor.
     */
    public function __destruct()
    {
        if ($this->pointer)
        {
            fclose($this->pointer);
        }
    }
    
    /**
     * Sets the maximum line length of CSV columns (for fgetcsv) - defaults to no limit.
     *
     * @param $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }
    
    /**
     * Determines whether the pointer to a file was successfully set.
     *
     */
    public function getPointer() {
        return $this->pointer;
    }
    
    /**
     * Sets the delimiter used in this CSV. Defaults to a comma.
     *
     * @param $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }
    
    /**
     * Iterates over the CSV, triggering evaluation of each row and storing compliant ones.
     * Passes the results to the transposer.
     */
    public function iterateRows()
    {
       
        while (($row = fgetcsv($this->pointer, $this->length, $this->delimiter)) !== FALSE)
        {
            
            if($this->instance->evalRowTime($row)) {
                // For each row, get the time data, diff to the current time, and truncate if needed.
                $this->compliantLines[] = $row;
            }
        }
        
        $this->transpose();
    }
    
    /**
     * Read stored CSV lines from memory and overwrite the existing log file with the truncated version.
     */
    public function transpose()
    {
        fclose($this->pointer);
        $this->pointer = fopen($this->instance->filePath, "w+");
        foreach ($this->compliantLines as $line) {
            fputcsv($this->pointer, $line);
        }
        
    }
    
}
