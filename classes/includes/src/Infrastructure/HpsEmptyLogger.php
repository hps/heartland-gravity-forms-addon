<?php

class HpsEmptyLogger implements HpsLoggerInterface
{
    public function log($message, $object = null)
    {
        file_put_contents(ABSPATH . "/response.xml", $object);
        return;
    }
}
