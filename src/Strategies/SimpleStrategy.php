<?php

namespace PHRETS\Strategies;

use PHRETS\Configuration;
use PHRETS\Exceptions\ParserNotFound;
use PHRETS\Parsers\Login\OneEight;

class SimpleStrategy implements Strategy
{
    /**
     * Default components.
     */
    private const CLASSES = [
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
        Strategy::PARSER_METADATA_LOOKUP => \PHRETS\Parsers\GetMetadata\Lookup::class,
        Strategy::PARSER_METADATA_LOOKUPTYPE => \PHRETS\Parsers\GetMetadata\LookupType::class,
        Strategy::PARSER_UPDATE => \PHRETS\Parsers\Update\OneEight::class,
        Strategy::PARSER_XML => \PHRETS\Parsers\XML::class,
    ];

    private array $classes;
    private array $instances;

    public function __construct()
    {
        $this->classes = self::CLASSES;
        $this->instances = [];
    }

    /**
     * @param string $component
     *
     * @throws \PHRETS\Exceptions\ParserNotFound
     */
    public function provide(string $component): mixed
    {
        if (!array_key_exists($component, $this->instances)) {
            throw new ParserNotFound("Component {$component} not found");
        }

        return $this->instances[$component];
    }

    /**
     * @return void
     */
    public function initialize(Configuration $configuration)
    {
        if ($configuration->getRetsVersion()->isAtLeast1_8()) {
            $this->classes[self::PARSER_LOGIN] = OneEight::class;
        }

        foreach ($this->classes as $k => $v) {
            $this->instances[$k] = new $v();
        }
    }

    /**
     * Used for tests
     *
     * @param string $component Component
     * @param mixed $instance Instance
     * @return void
     */
    public function setInstance(string $component, mixed $instance): void
    {
        if (!array_key_exists($component, $this->instances)) {
            throw new ParserNotFound("Component {$component} not found");
        }

        $this->instances[$component] = $instance;
    }
}
