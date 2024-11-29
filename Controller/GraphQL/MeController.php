<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Bundle\Controller\GraphQL;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use TheCodingMachine\GraphQLite\Annotations\Query;

class MeController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    #[Query]
    public function me(): ?UserInterface
    {
        $token = $this->tokenStorage->getToken();
        
        if ($token === null) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            // getUser() can be the "anon." string.
            return null;
        }

        return $user;
    }
}
