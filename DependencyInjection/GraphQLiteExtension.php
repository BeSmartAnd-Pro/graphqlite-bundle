<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Bundle\DependencyInjection;

use Exception;
use GraphQL\Error\DebugFlag;
use TheCodingMachine\GraphQLite\Bundle\Manager\ServerConfigManager;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperFactoryInterface;
use GraphQL\Server\ServerConfig;
use GraphQL\Type\Definition\ObjectType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class GraphQLiteExtension extends Extension
{
    public function getAlias(): string
    {
        return 'graphqlite';
    }

    /**
     * Loads a specific configuration.
     *
     * @param mixed[] $configs
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/container'));

        $controllers = [];
        $types;

        if (isset($config['namespaces'])) {
            foreach ($config['namespaces'] as $name => $namespace) {
                //$name to nazwa namespace, jakos to ogarnac
                if (!isset($controllers[$name])) {
                    $controllers[$name] = [];
                }

                foreach ($namespace['controllers'] as $controller) {
                    $controllers[$name][] = rtrim($controller, '\\'); //$controller;
                }

                foreach ($namespace['types'] as $type) {
                    $types[$name][] = rtrim($type, '\\');
                }
            }
        }

        $enableLogin = $config['security']['enable_login'] ?? 'auto';
        $enableMe = $config['security']['enable_me'] ?? 'auto';

        $container->setParameter('graphqlite.namespaces.controllers', $controllers);
        $container->setParameter('graphqlite.namespaces.types', $types);
        $container->setParameter('graphqlite.security.enable_login', $enableLogin);
        $container->setParameter('graphqlite.security.enable_me', $enableMe);
        $container->setParameter('graphqlite.security.disableIntrospection', !($config['security']['introspection'] ?? true));
        $container->setParameter('graphqlite.security.maximum_query_complexity', $config['security']['maximum_query_complexity'] ?? null);
        $container->setParameter('graphqlite.security.maximum_query_depth', $config['security']['maximum_query_depth'] ?? null);
        $container->setParameter('graphqlite.security.firewall_name', $config['security']['firewall_name'] ?? 'main');

        $loader->load('graphqlite.xml');

        $definition = $container->getDefinition(ServerConfigManager::class);

        if (isset($config['debug'])) {
            $debugCode = $this->toDebugCode($config['debug']);
        } else {
            $debugCode = DebugFlag::RETHROW_UNSAFE_EXCEPTIONS;
        }

        $definition->addMethodCall('setDebugFlag', [$debugCode]);

        $container->registerForAutoconfiguration(ObjectType::class)
            ->addTag('graphql.output_type');
        $container->registerForAutoconfiguration(RootTypeMapperFactoryInterface::class)
            ->addTag('graphql.root_type_mapper_factory');
    }

    /**
     * @param array<string, int> $debug
     */
    private function toDebugCode(array $debug): int
    {
        $code = 0;
        $code |= ($debug['INCLUDE_DEBUG_MESSAGE'] ?? 0) * DebugFlag::INCLUDE_DEBUG_MESSAGE;
        $code |= ($debug['INCLUDE_TRACE'] ?? 0) * DebugFlag::INCLUDE_TRACE;
        $code |= ($debug['RETHROW_INTERNAL_EXCEPTIONS'] ?? 0) * DebugFlag::RETHROW_INTERNAL_EXCEPTIONS;
        $code |= ($debug['RETHROW_UNSAFE_EXCEPTIONS'] ?? 0) * DebugFlag::RETHROW_UNSAFE_EXCEPTIONS;

        return $code;
    }
}
