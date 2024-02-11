<?php
class Foo
{
    public function __construct()
    {
        static::bar();
        self::bar();
        parent::__construct();
    }
}
