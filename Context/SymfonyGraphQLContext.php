<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Bundle\Context;

use Symfony\Component\HttpFoundation\Request;
use TheCodingMachine\GraphQLite\Context\Context;

class SymfonyGraphQLContext extends Context implements SymfonyRequestContextInterface
{
    public function __construct(private readonly Request $request)
    {
        parent::__construct();
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
