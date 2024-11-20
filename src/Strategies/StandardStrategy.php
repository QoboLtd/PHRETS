<?php

namespace PHRETS\Strategies;

use Illuminate\Container\Container;
use PHRETS\Configuration;

class StandardStrategy implements Strategy
{
    /**
     * Default components.
     */
    protected array $default_components = [
        Strategy::PARSER_LOGIN => \PHRETS\Parsers\Login\OneFive::class,
        Strategy::PARSER_OBJECT_SINGLE => \PHRETS\Parsers\GetObject\Single::class,
        Strategy::PARSER_OBJECT_MULTIPLE => \PHRETS\Parsers\GetObject\Multiple::class,
        Strategy::PARSER_SEARCH => \PHRETS\Parsers\Search\OneX::class,
        Strategy::PARSER_SEARCH_RECURSIVE => \PHRETS\Parsers\Search\RecursiveOneX::class,
        Strategy::PARSER_METADATA_SYSTEM => \PHRETS\Parsers\GetMetadata\System::class,
        Strategy::PARSER_METADATA_RESOURCE => \PHRETS\Parsers\GetMetadata\Resource::class,
        Strategy::PARSER_METADATA_CLASS => \PHRETS\Parsers\GetMetadata\ResourceClass::class,
        Strategy::PARSER_METADATA_TABLE => \PHRETS\Parsers\GetMetadata\Table::class,
        Strategy::PARSER_METADATA_OBJECT => \PHRETS\Parsers\GetMetadata\BaseObject::class,
        Strategy::PARSER_METADATA_LOOKUPTYPE => \PHRETS\Parsers\GetMetadata\LookupType::class,
        Strategy::PARSER_XML => \PHRETS\Parsers\XML::class,
    ];

    protected Container $container;

    /**
     * @param $component
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function provide($component): mixed
    {
        return $this->container->make($component);
    }

    /**
     * @return void
     */
    public function initialize(Configuration $configuration)
    {
        // start up the service locator
        $this->container = new Container();

        foreach ($this->default_components as $k => $v) {
            if ($k == 'parser.login' && $configuration->getRetsVersion()->isAtLeast1_8()) {
                $v = \PHRETS\Parsers\Login\OneEight::class;
            }

            $this->container->singleton($k, fn () => new $v());
        }
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}
