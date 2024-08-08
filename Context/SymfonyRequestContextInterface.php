<?php

namespace TheCodingMachine\GraphQLite\Bundle\Context;

use Symfony\Component\HttpFoundation\Request;

interface SymfonyRequestContextInterface
{
    public function getRequest(): Request;
}
