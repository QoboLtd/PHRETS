<?php

namespace PHRETS\Parsers\Search;

use PHRETS\Exceptions\AutomaticPaginationError;
use PHRETS\Http\Response;
use PHRETS\Models\Search\Results;
use PHRETS\Parsers\ParserType;
use PHRETS\Session;

class RecursiveOneX
{
    /**
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     * @throws \PHRETS\Exceptions\AutomaticPaginationError
     */
    public function parse(Session $rets, Response $response, $parameters): Results
    {
        // we're given the first response automatically, so parse this and start the recursion

        /** @var \PHRETS\Parsers\Search\OneX $parser */
        $parser = $rets->getConfiguration()->getStrategy()->provide(ParserType::SEARCH);
        $rs = $parser->parse($rets, $response, $parameters);

        while ($this->continuePaginating($rets, $parameters, $rs)) {
            $pms = $parameters;

            $rets->debug('Continuing pagination...');
            $rets->debug('Current count collected already: ' . $rs->count());

            $resource = $pms['SearchType'];
            $class = $pms['Class'];
            $query = $pms['Query'] ?? null;

            $pms['Offset'] = $this->getNewOffset($rets, $parameters, $rs);

            unset($pms['SearchType']);
            unset($pms['Class']);
            unset($pms['Query']);

            $inner_rs = $rets->Search($resource, $class, $query, $pms, false);
            $rs->setTotalResultsCount($inner_rs->getTotalResultsCount());
            $rs->setMaxRowsReached($inner_rs->isMaxRowsReached());

            // test if we're actually paginating
            if ($this->isPaginationBroken($rs, $inner_rs)) {
                throw new AutomaticPaginationError("Automatic pagination doesn't not appear to be supported by the server");
            }

            foreach ($inner_rs as $ir) {
                $rs->addRecord($ir);
            }
        }

        return $rs;
    }

    /**
     * @param $parameters
     */
    protected function continuePaginating(Session $rets, $parameters, Results $rs): bool
    {
        return $rs->isMaxRowsReached();
    }

    /**
     * @param $parameters
     */
    protected function getNewOffset(Session $rets, $parameters, Results $rs): int
    {
        return $rs->getReturnedResultsCount() + 1;
    }

    protected function isPaginationBroken(Results $big, Results $small): bool
    {
        $big_first = $big->first();
        $small_first = $small->first();

        if ($big_first && $small_first) {
            return $big_first->toArray() === $small_first->toArray();
        }

        return false;
    }
}
