<?php 

namespace Src\Components;

class Theme 
{
    protected array $values = [];
    
    public function __construct() 
    {}

    public function __get($key) 
    {
        return isset($this->values[$key]) ? $this->values[$key] : null;
    }

    public function __set($key, $value) 
    {
        $this->values[$key] = $value;
    }

    public function getData(): array 
    {
        return $this->values;
    }

    public function loadData(array $data): self 
    {
        foreach($data as $attr => $value) {
            $this->{$attr} = $value;
            $this->values[$attr] = $value;
        }
        return $this;
    }
}