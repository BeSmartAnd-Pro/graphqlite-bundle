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
    #[Autowire(['for' => '$testService', 'identifier' => 'someService'])]
    #[Autowire(['for' => '$someService', 'identifier' => 'someService'])]
    #[Autowire(['for' => '$someAlias', 'identifier' => 'someAlias'])]
    public function injectService(?TestGraphqlController $testService = null, ?stdClass $someService = null, ?stdClass $someAlias = null): string
    {
        if (!$testService instanceof TestGraphqlController || $someService === null || $someAlias === null) {
            return 'KO';
        }
        return 'OK';
    }

    #[Field(prefetchMethod: 'prefetchData')]
    public function injectServicePrefetch($prefetchData): string
    {
        return $prefetchData;
    }

    #[Autowire(['for' => '$someOtherService', 'identifier' => 'someOtherService'])]
    public function prefetchData(iterable $iterable, ?stdClass $someOtherService = null): string
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
