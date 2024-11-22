<?php

namespace PHRETS\Strategies;

use PHRETS\Configuration;
use PHRETS\Exceptions\ParserNotFound;
use PHRETS\Parsers\Login\OneEight;
use PHRETS\Parsers\ParserType;

class SimpleStrategy implements Strategy
{
    /**
     * Default components.
     */
    private const CLASSES = [
        ParserType::LOGIN->value => \PHRETS\Parsers\Login\OneFive::class,
        ParserType::OBJECT_SINGLE->value => \PHRETS\Parsers\GetObject\Single::class,
        ParserType::OBJECT_MULTIPLE->value => \PHRETS\Parsers\GetObject\Multiple::class,
        ParserType::SEARCH->value => \PHRETS\Parsers\Search\OneX::class,
        ParserType::SEARCH_RECURSIVE->value => \PHRETS\Parsers\Search\RecursiveOneX::class,
        ParserType::METADATA_SYSTEM->value => \PHRETS\Parsers\GetMetadata\System::class,
        ParserType::METADATA_RESOURCE->value => \PHRETS\Parsers\GetMetadata\Resource::class,
        ParserType::METADATA_CLASS->value => \PHRETS\Parsers\GetMetadata\ResourceClass::class,
        ParserType::METADATA_TABLE->value => \PHRETS\Parsers\GetMetadata\Table::class,
        ParserType::METADATA_OBJECT->value => \PHRETS\Parsers\GetMetadata\BaseObject::class,
        ParserType::METADATA_LOOKUP->value => \PHRETS\Parsers\GetMetadata\Lookup::class,
        ParserType::METADATA_LOOKUPTYPE->value => \PHRETS\Parsers\GetMetadata\LookupType::class,
        ParserType::UPDATE->value => \PHRETS\Parsers\Update\OneEight::class,
        ParserType::OBJECT_POST->value => \PHRETS\Parsers\PostObject\OneEight::class,
        ParserType::XML->value => \PHRETS\Parsers\XML::class,
    ];

    /** @var array<string,class-string> */
    private array $classes;

    /** @var array<string,mixed> */
    private array $instances;

    public function __construct()
    {
        $this->classes = self::CLASSES;
        $this->instances = [];
    }

    /**
     * @param \PHRETS\Parsers\ParserType $parser
     *
     * @throws \PHRETS\Exceptions\ParserNotFound
     */
    public function provide(ParserType $parser): mixed
    {
        if (!array_key_exists($parser->value, $this->instances)) {
            throw new ParserNotFound("Component {$parser->value} not found");
        }

        return $this->instances[$parser->value];
    }

    /**
     */
    public function initialize(Configuration $configuration): void
    {
        if ($configuration->getRetsVersion()->isAtLeast1_8()) {
            $this->classes[ParserType::LOGIN->value] = OneEight::class;
        }

        foreach ($this->classes as $k => $v) {
            $this->instances[$k] = new $v();
        }
    }

    /**
     * Used for tests
     *
     * @param \PHRETS\Parsers\ParserType $parser Parser
     * @param mixed $instance Instance
     */
    public function setInstance(ParserType $parser, mixed $instance): void
    {
        if (!array_key_exists($parser->value, $this->instances)) {
            throw new ParserNotFound("Component {$parser->value} not found");
        }

        $this->instances[$parser->value] = $instance;
    }
}
