<?php

namespace AlphaSoft\AsLinkOrm\Types;

if (defined('ASLINKORM_DATE_FORMAT')) {
    define('ASLINKORM_DATE_FORMAT', 'Y-m-d');
}

final class DateType extends Type
{

    public function convertToDatabase($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(ASLINKORM_DATE_FORMAT);
        }

        throw new \LogicException('Could not convert PHP value "' . $value . '" to ' . self::class);
    }

    public function convertToPHP($value): ?\DateTimeInterface
    {
        if ($value === null || $value instanceof \DateTimeInterface) {
            return $value;
        }

        $date = \DateTime::createFromFormat(ASLINKORM_DATE_FORMAT, $value);
        if (!$date instanceof \DateTimeInterface) {
            throw new \LogicException('Could not convert database value "' . $value . '" to ' . self::class);
        }

        return $date;
    }

}
