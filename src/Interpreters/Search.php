<?php namespace PHRETS\Interpreters;

class Search
{
    public static function dmql($query)
    {
        // automatically surround the given query with parentheses if it doesn't have them already
        if (!empty($query) && $query != "*" && !preg_match('/^\((.*)\)$/', (string) $query)) {
            $query = '(' . $query . ')';
        }

        return $query;
    }
}
