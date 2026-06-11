<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Bundle\Tests\Fixtures\Entities;

use stdClass;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Bundle\Tests\Fixtures\Controller\TestGraphqlController;
use TheCodingMachine\GraphQLite\Annotations\Autowire;

#[Type]
class Contact
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    #[Field(name: 'name')]
    public function getName(): string
    {
        return $this->name;
    }

    #[Field]
    public function injectService(
        #[Autowire] ?TestGraphqlController $testService = null,
        #[Autowire('someService')] ?stdClass $someService = null,
        #[Autowire('someAlias')] ?stdClass $someAlias = null
    ): string
    {
        if (!$testService instanceof TestGraphqlController || $someService === null || $someAlias === null) {
            return 'KO';
        }
        return 'OK';
    }

    #[Field(prefetchMethod: 'prefetchData')]
    public function injectServicePrefetch(mixed $prefetchData): string
    {
        return $prefetchData;
    }

    public function prefetchData(iterable $iterable, #[Autowire('someOtherService')] ?stdClass $someOtherService = null): string
    {
        if ($someOtherService === null) {
            return 'KO';
        }
        
        return 'OK';
    }

    #[Field]
    public function getManager(): ?Contact
    {
        return null;
    }
}
