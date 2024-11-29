<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Bundle\Tests\NoSecurityBundleFixtures\Controller;

use TheCodingMachine\GraphQLite\Annotations\Query;

class EchoController
{
    #[Query]
    public function echoMsg(string $message): string {
        return $message;
    }
}
