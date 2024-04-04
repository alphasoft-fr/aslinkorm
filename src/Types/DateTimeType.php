<?php

namespace AlphaSoft\AsLinkOrm\Types;

if (defined('ASLINKORM_DATETIME_FORMAT')) {
    define('ASLINKORM_DATETIME_FORMAT', 'Y-m-d H:i:s');
}

final class DateTimeType extends Type
{

    public function convertToDatabase($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(ASLINKORM_DATETIME_FORMAT);
        }

        throw new \LogicException('Could not convert PHP value "' . $value . '" to ' . self::class);
    }

    public function convertToPHP($value): ?\DateTimeInterface
    {
        if ($value === null || $value instanceof \DateTimeInterface) {
            return $value;
        }

        $date = \DateTime::createFromFormat(ASLINKORM_DATETIME_FORMAT, $value);
        if (!$date instanceof \DateTimeInterface) {
            throw new \LogicException('Could not convert database value "' . $value . '" to ' . self::class);
        }

        return $date;
    }

}
