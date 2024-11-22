<?php

namespace PHRETS\Parsers\Search;

use PHRETS\Http\Response;
use PHRETS\Models\Search\Record;
use PHRETS\Models\Search\Results;
use PHRETS\Parsers\ParserType;
use PHRETS\Session;
use SimpleXMLElement;

class OneX
{
    /**
     * @param array<string,mixed> $parameters
     */
    public function parse(Session $rets, Response $response, array $parameters): Results
    {
        $parser = $rets->getConfiguration()->getStrategy()->provide(ParserType::XML);
        $xml = $parser->parse($response);
        assert($xml instanceof SimpleXMLElement);

        $rs = new Results();
        $rs->setSession($rets)
            ->setResource($parameters['SearchType'])
            ->setClass($parameters['Class']);

        if ($this->getRestrictedIndicator($parameters)) {
            $rs->setRestrictedIndicator($this->getRestrictedIndicator($parameters));
        }

        $rs->setHeaders($this->getColumnNames($rets, $xml));
        $rets->debug(count($rs->getHeaders()) . ' column headers/fields given');

        $this->parseRecords($rets, $xml, $rs);

        if ($this->getTotalCount($xml) !== null) {
            $rs->setTotalResultsCount($this->getTotalCount($xml));
            $rets->debug($rs->getTotalResultsCount() . ' total results found');
        }
        $rets->debug($rs->getReturnedResultsCount() . ' results given');

        if ($this->foundMaxRows($xml)) {
            // MAXROWS tag found.  the RETS server withheld records.
            // if the server supports Offset, more requests can be sent to page through results
            // until this tag isn't found anymore.
            $rs->setMaxRowsReached();
            $rets->debug('Maximum rows returned in response');
        }

        unset($xml);

        return $rs;
    }

    /**
     * @param $xml
     * @param $parameters
     */
    protected function getDelimiter(Session $rets, SimpleXMLElement $xml): string
    {
        if (property_exists($xml, 'DELIMITER') && $xml->DELIMITER !== null) {
            // delimiter found so we have at least a COLUMNS row to parse
            return chr("{$xml->DELIMITER->attributes()->value}");
        } else {
            // assume tab delimited since it wasn't given
            $rets->debug('Assuming TAB delimiter since none specified in response');

            return chr(9);
        }
    }

    /**
     * @param array<string,mixed> $parameters
     */
    protected function getRestrictedIndicator(array $parameters): ?string
    {
        if (array_key_exists('RestrictedIndicator', $parameters)) {
            return $parameters['RestrictedIndicator'];
        } else {
            return null;
        }
    }

    /**
     * @return list<string>
     */
    protected function getColumnNames(Session $rets, SimpleXMLElement $xml): array
    {
        $delim = $this->getDelimiter($rets, $xml);
        $delimLength = strlen($delim);

        // break out and track the column names in the response
        $column_names = "{$xml->COLUMNS[0]}";

        // Take out the first delimiter
        if (substr($column_names, 0, $delimLength) === $delim) {
            $column_names = substr($column_names, $delimLength);
        }

        // Take out the last delimiter
        if (substr($column_names, -$delimLength) === $delim) {
            $column_names = substr($column_names, 0, -$delimLength);
        }

        // parse and return the rest
        return explode($delim, $column_names);
    }

    protected function parseRecords(Session $rets, SimpleXMLElement $xml, Results $rs): void
    {
        if (property_exists($xml, 'DATA') && $xml->DATA !== null) {
            foreach ($xml->DATA as $line) {
                $rs->addRecord($this->parseRecordFromLine($rets, $xml, $line, $rs));
            }
        }
    }

    protected function parseRecordFromLine(
        Session $rets,
        SimpleXMLElement $xml,
        SimpleXMLElement $line,
        Results $rs
    ): Record {
        $delim = $this->getDelimiter($rets, $xml);
        $delimLength = strlen($delim);

        $r = new Record();
        $field_data = (string)$line;

        // Take out the first delimiter
        if (substr($field_data, 0, $delimLength) === $delim) {
            $field_data = substr($field_data, $delimLength);
        }

        // Take out the last delimiter
        if (substr($field_data, -$delimLength) === $delim) {
            $field_data = substr($field_data, 0, -$delimLength);
        }

        $field_data = explode($delim, $field_data);

        foreach ($rs->getHeaders() as $key => $name) {
            // assign each value to it's name retrieved in the COLUMNS earlier
            $r->set($name, $field_data[$key]);
        }

        return $r;
    }

    protected function getTotalCount(SimpleXMLElement $xml): ?int
    {
        if (property_exists($xml, 'COUNT') && $xml->COUNT !== null) {
            return (int) "{$xml->COUNT->attributes()->Records}";
        } else {
            return null;
        }
    }

    protected function foundMaxRows(SimpleXMLElement $xml): bool
    {
        return property_exists($xml, 'MAXROWS') && $xml->MAXROWS !== null;
    }
}
