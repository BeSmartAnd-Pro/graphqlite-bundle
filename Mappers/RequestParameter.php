<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Bundle\Mappers;

use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\HttpFoundation\Request;
use TheCodingMachine\GraphQLite\Bundle\Context\SymfonyRequestContextInterface;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException as GraphQLException;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

class RequestParameter implements ParameterInterface
{
    /**
     * @param array<string, mixed> $args
     */
    public function resolve(?object $source, array $args, mixed $context, ResolveInfo $info): Request
    {
        if (!$context instanceof SymfonyRequestContextInterface) {
            throw new GraphQLException('Cannot type-hint on a Symfony Request object in your query/mutation/field. The request context must implement SymfonyRequestContextInterface.');
        }
        
        return $context->getRequest();
    }
}
