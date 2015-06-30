<?php
namespace Authority\Events;

class Event
{
    public function __construct($payload = [])
    {
        $this->payload = $payload;
    }

    public function __get($key)
    {
        return $this->payload[$key];
    }
}
