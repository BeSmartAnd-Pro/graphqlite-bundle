<?php

declare(strict_types=1);

use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\CacheClearer\Psr6CacheClearer;
use TheCodingMachine\GraphQLite\AggregateControllerQueryProviderFactory;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\Bundle\Command\DumpSchemaCommand;
use TheCodingMachine\GraphQLite\Bundle\Controller\GraphQL\LoginController;
use TheCodingMachine\GraphQLite\Bundle\Controller\GraphQL\MeController;
use TheCodingMachine\GraphQLite\Bundle\Controller\GraphQLiteController;
use TheCodingMachine\GraphQLite\Bundle\Manager\SchemaManager;
use TheCodingMachine\GraphQLite\Bundle\Manager\ServerConfigManager;
use TheCodingMachine\GraphQLite\Bundle\Mappers\RequestParameterMiddleware;
use TheCodingMachine\GraphQLite\Bundle\Security\AuthenticationService;
use TheCodingMachine\GraphQLite\Bundle\Security\AuthorizationService;
use TheCodingMachine\GraphQLite\Bundle\Types\SymfonyUserInterfaceType;
use TheCodingMachine\GraphQLite\Mappers\StaticClassListTypeMapperFactory;
use TheCodingMachine\GraphQLite\Mappers\StaticTypeMapper;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Validator\Mappers\Parameters\AssertParameterMiddleware;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('graphqlite.annotations.error_mode', 'LAX_MODE');

    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->private();

    $services->set(AggregateControllerQueryProviderFactory::class)
        ->args([
            [],
            service('service_container'),
        ])
        ->tag('graphql.queryprovider_factory');

    $services->set(SchemaManager::class);
    $services->set(ServerConfigManager::class);
    $services->set(AnnotationReader::class);

    $services->set(AuthenticationService::class)
        ->arg('$tokenStorage', service('security.token_storage')->nullOnInvalid());
    $services->alias(AuthenticationServiceInterface::class, AuthenticationService::class);

    $services->set(AuthorizationService::class)
        ->arg('$authorizationChecker', service('security.authorization_checker')->nullOnInvalid())
        ->arg('$tokenStorage', service('security.token_storage')->nullOnInvalid());
    $services->alias(AuthorizationServiceInterface::class, AuthorizationService::class);

    $services->set(DisableIntrospection::class)
        ->arg('$enabled', '%graphqlite.security.disableIntrospection%');
    $services->set(QueryComplexity::class);
    $services->set(QueryDepth::class);

    $services->set(StaticTypeMapper::class)
        ->tag('graphql.type_mapper');

    $services->set(GraphQLiteController::class)
        ->public()
        ->tag('routing.route_loader');

    $services->set(RequestParameterMiddleware::class)
        ->tag('graphql.parameter_middleware');

    $services->set(AssertParameterMiddleware::class)
        ->arg('$constraintValidatorFactory', service('validator.validator_factory'))
        ->tag('graphql.parameter_middleware');

    $services->set(LoginController::class)
        ->public()
        ->arg('$firewallName', '%graphqlite.security.firewall_name%');

    $services->set(MeController::class)->public();
    $services->set(SymfonyUserInterfaceType::class)->public();

    $services->set(StaticClassListTypeMapperFactory::class)
        ->args([
            [],
        ])
        ->tag('graphql.type_mapper_factory');

    $services->set('graphqlite.phpfilescache', PhpFilesAdapter::class)
        ->args([
            'graphqlite',
            0,
            '%kernel.cache_dir%',
        ]);

    $services->set('graphqlite.apcucache', ApcuAdapter::class)
        ->arg('$namespace', 'graphqlite');

    $services->set('graphqlite.psr16cache', Psr16Cache::class)
        ->arg('$pool', service('graphqlite.cache'));

    $services->set('graphqlite.cacheclearer', Psr6CacheClearer::class)
        ->arg('$pools', [
            service('graphqlite.cache'),
        ])
        ->tag('kernel.cache_clearer');

    $services->set(DumpSchemaCommand::class);
};
