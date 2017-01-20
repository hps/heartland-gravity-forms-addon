<?php

class HpsEmptyLogger implements HpsLoggerInterface
{
    public function log($message, $object = null)
    {
        file_put_contents(ABSPATH . "/response.xml", print_r($object,true));
        return;
    }
}
