<?php namespace RiuDB;

class Json {

    public static function create()
    {
        return new \RiuDB\Json\Create();
    }

    public static function read()
    {
        return new \RiuDB\Json\Read();
    }

    public static function update()
    {
        return new \RiuDB\Json\Update();
    }

    public static function delete()
    {
        return new \RiuDB\Json\Delete();
    }

    public static function alter()
    {
        return new \RiuDB\Json\Alter();
    }

    public static function alias()
    {
        return new \RiuDB\Json\Alias();
    }

}
