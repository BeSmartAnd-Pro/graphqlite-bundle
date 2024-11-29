<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Bundle\Utils;

use GraphQL\Type\Schema as TypeSchema;
use GraphQL\Utils\SchemaPrinter;

class SchemaPrinterForGraphQLite extends SchemaPrinter {
    protected static function hasDefaultRootOperationTypes(TypeSchema $schema): bool
    {
        return $schema->getQueryType() === $schema->getType('Query')
            && $schema->getMutationType() === $schema->getType('Mutation')
            ;
    }
}
