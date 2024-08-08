<?php

namespace TheCodingMachine\GraphQLite\Bundle\Context;

use Symfony\Component\HttpFoundation\Request;
use TheCodingMachine\GraphQLite\Context\Context;

class SymfonyGraphQLContext extends Context implements SymfonyRequestContextInterface
{
    private Request $request;

    public function __construct(Request $request)
    {
        parent::__construct();
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
