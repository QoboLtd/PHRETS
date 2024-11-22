<?php

namespace PHRETS\Interpreters;

class GetObject
{
    /**
     * @param list<string|int>|string|int $content_ids Content IDs
     * @param list<string|int>|string|int $object_ids Object IDs
     * @return list<string>
     */
    public static function ids(array|string|int $content_ids, array|string|int $object_ids): array
    {
        $result = [];

        $content_ids = self::split((string)$content_ids, false);
        $object_ids = self::split((string)$object_ids);

        foreach ($content_ids as $cid) {
            $result[] = $cid . ':' . implode(':', $object_ids);
        }

        return $result;
    }

    /**
     * @param list<string|int>|string|int $value Value
     * @return list<string>
     */
    protected static function split(string|int|array $value, bool $dash_ranges = true): array
    {
        if (!is_array($value)) {
            $value = (string)$value;

            if (stripos($value, ':') !== false) {
                $value = array_map('trim', explode(':', $value));
            } elseif (stripos($value, ',') !== false) {
                $value = array_map('trim', explode(',', $value));
            } elseif ($dash_ranges && preg_match('/(\d+)\-(\d+)/', $value, $matches)) {
                $value = range($matches[1], $matches[2]);
            } else {
                $value = [$value];
            }
        } else {
            $value = array_map(fn ($v) => (string)$v, $value);
        }

        return $value;
    }
}
