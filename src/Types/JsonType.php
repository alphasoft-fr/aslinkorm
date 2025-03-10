<?php

namespace AlphaSoft\AsLinkOrm\Types;

final class JsonType extends Type
{

    public function convertToDatabase($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $encoded = json_encode($value);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \LogicException('Could not convert PHP value "' . $value . '" to ' . self::class);
        }

        return $encoded;
    }

    public function convertToPHP($value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        /**
         * @todo $value can be a string or an array or another type , not only array !!!! JSON Decode
         * @todo Fix this as soon as possible
         */
        $array = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \LogicException('Could not convert  database value "' . $value . '" to ' . self::class);
        }

        return $array;
    }
}
