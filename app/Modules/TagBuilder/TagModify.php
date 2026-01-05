<?php
namespace Mediatag\Modules\TagBuilder;

class TagModify
{

    public $videoInfo;
    public function __construct($VideoInfo)
    {
        $this->VideoInfo = $VideoInfo;


    }
    public function setNetwork($value, $key)
    {
        return $value;
        //   utmdd("", $key, $value);

    }

    public function __call($method, $args)
    {
        utmdd($method, $args);
    }

}
