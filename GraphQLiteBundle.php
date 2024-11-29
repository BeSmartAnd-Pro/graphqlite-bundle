<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Bundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use TheCodingMachine\GraphQLite\Bundle\DependencyInjection\GraphQLiteExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use TheCodingMachine\GraphQLite\Bundle\DependencyInjection\GraphQLiteCompilerPass;

class GraphQLiteBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        
        $container->addCompilerPass(new GraphQLiteCompilerPass());
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new GraphQLiteExtension();
        }

        return $this->extension !== false ? $this->extension : null;
    }
}
