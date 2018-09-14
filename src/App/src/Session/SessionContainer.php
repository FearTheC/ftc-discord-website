<?php

declare(strict_types=1);

namespace App\Session;

class SessionContainer
{
    
    private $data;
    
    
    private $originalData;
    
    
    public static function newEmptySession() : self
    {
        $instance = new self();
        $instance->originalData = $instance->data = [];
        
        return $instance;
    }
    
    
    public static function fromDecodedTokenPayload(array $data) : self
    {
        $instance = new self();
        $instance->data = [];
        
        foreach ($data as $key => $value)
        {
            $instance->set($key, $value);
        }
        
        $instance->originalData = $instance->data;
        
        return $instance;
    }
    
        
    public function set(string $key, $value) : void
    {
        $this->data[$key] = $value;
    }
    
    
    public function get(string $key, $default = null)
    {
        if (!$this->has($key)) {
            return $default;
        }
        
        return $this->data[$key];
    }
    
    
    public function has(string $key) : bool
    {
        return array_key_exists($key, $this->data);
    }
    
    
    public function clear() : void
    {
        $this->data = [];
    }
    
    
    public function remove(string $key) : void
    {
        unset($this->data[$key]);
    }
    
    
    public function isEmpty() : bool
    {
        return empty($this->data);
    }
    
    
    public function hasChanged() : bool
    {
        return $this->data !== $this->originalData;
    }
    
}
