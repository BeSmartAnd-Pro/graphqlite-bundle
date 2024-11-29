<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Bundle\Tests\Fixtures\Controller;

use Exception;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLExceptionInterface;

class MyException extends Exception implements GraphQLExceptionInterface
{
    /**
     * Returns true when exception message is safe to be displayed to a client.
     *
     * @api
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    public function getExtensions(): array
    {
        return ['category' => 'foobar'];
    }
}
