<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Bundle\Manager;

use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\GraphQLite\SchemaFactory;

class SchemaManager
{
    /** @var SchemaFactory[] $schemaFactories */
    protected array $schemaFactories = [];

    public function addSchemaFactory(string $namespace, SchemaFactory $schemaFactory): void
    {
        $this->schemaFactories[] = $schemaFactory;
    }

    /** @param <string, SchemaFactory> $factories */
    public function setFactories(array $factories): void
    {
        $this->schemaFactories = $factories;
    }

    public function getSchemaByNamespace(string $namespace): ?Schema
    {
        return
            $this->schemaFactories[$namespace]
                ? $this->schemaFactories[$namespace]->createSchema()
                : null;
    }
}
