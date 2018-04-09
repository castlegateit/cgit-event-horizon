<?php

namespace Cgit\EventHorizon;

class FileHandler
{
    private $pointer;
    private $delimiter;
    private $length = 0;
    private $instance;
    private $compliantLines = array();
    public $data;
    
    public function __construct($filePath, $instance)
    {
        $this->instance = $instance;
        $this->pointer = fopen($filePath, "r+");
        $this->delimiter = ",";
    }

    public function __destruct()
    {
        if ($this->pointer)
        {
            fclose($this->pointer);
        }
        
    }
    
    public function setLength($length) {
        $this->length = $length;
    }

    public function setDelimiter($delimiter) {
        $this->delimiter = $delimiter;
    }
    
    public function iterateRows()
    {
       
        while (($row = fgetcsv($this->pointer, $this->length, $this->delimiter)) !== FALSE)
        {
            if($this->instance->evalRowTime($row))
            // For each row, get the time data, diff to the current time, and truncate if needed.
            $this->compliantLines[] = $row;
        }
        
        $this->transpose();
    }
    
    public function transpose()
    {
        fclose($this->pointer);
        $this->pointer = fopen($this->instance->filePath, "w+");
        foreach ($this->compliantLines as $line) {
            fputcsv($this->pointer, $line);
        }
        
    }
    
}
