<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Bundle\Context;

use Symfony\Component\HttpFoundation\Request;

interface SymfonyRequestContextInterface
{
    public function getRequest(): Request;
}
