<?php

namespace PHRETS\Interpreters;

class GetObject
{
    public static function ids(mixed $content_ids, mixed $object_ids): array
    {
        $result = [];

        $content_ids = self::split($content_ids, false);
        $object_ids = self::split($object_ids);

        foreach ($content_ids as $cid) {
            $result[] = $cid . ':' . implode(':', $object_ids);
        }

        return $result;
    }

    protected static function split(mixed $value, bool $dash_ranges = true): array
    {
        if (!is_array($value)) {
            if (stripos((string) $value, ':') !== false) {
                $value = array_map('trim', explode(':', (string) $value));
            } elseif (stripos((string) $value, ',') !== false) {
                $value = array_map('trim', explode(',', (string) $value));
            } elseif ($dash_ranges && preg_match('/(\d+)\-(\d+)/', (string) $value, $matches)) {
                $value = range($matches[1], $matches[2]);
            } else {
                $value = [$value];
            }
        }

        return $value;
    }
}
