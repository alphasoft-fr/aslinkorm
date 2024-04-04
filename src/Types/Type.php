<?php

namespace AlphaSoft\AsLinkOrm\Types;

abstract class Type
{
    final public function __construct()
    {
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    abstract public function convertToDatabase($value);

    /**
     * @param mixed $value
     * @return mixed
     */
    abstract public function convertToPHP($value);

}
