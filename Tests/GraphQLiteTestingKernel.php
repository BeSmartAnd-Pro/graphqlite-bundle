<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Bundle\Tests;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use TheCodingMachine\GraphQLite\Bundle\GraphQLiteBundle;
use Symfony\Component\Security\Core\User\InMemoryUser;

class GraphQLiteTestingKernel extends Kernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    public const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    /**
     * @param string[] $controllersNamespace
     * @param string[] $typesNamespace
     */
    public function __construct(
        private readonly bool $enableSession = true,
        private readonly ?string $enableLogin = null,
        private readonly bool $enableSecurity = true,
        private readonly ?string $enableMe = null,
        private readonly bool $introspection = true,
        private readonly ?int $maximumQueryComplexity = null,
        private readonly ?int $maximumQueryDepth = null,
        private readonly array $controllersNamespace = ['TheCodingMachine\\GraphQLite\\Bundle\\Tests\\Fixtures\\Controller\\'],
        private readonly array $typesNamespace = [
            'TheCodingMachine\\GraphQLite\\Bundle\\Tests\\Fixtures\\Types\\',
            'TheCodingMachine\\GraphQLite\\Bundle\\Tests\\Fixtures\\Entities\\'
        ]
    ) {
        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        $bundles = [ new FrameworkBundle() ];
        
        if (class_exists(SecurityBundle::class)) {
            $bundles[] = new SecurityBundle();
        }
        
        $bundles[] = new GraphQLiteBundle();
        
        return $bundles;
    }

    public function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $loader->load(function(ContainerBuilder $container) {
            $frameworkConf = array(
                'secret' => 'S0ME_SECRET'
            );

            $frameworkConf['cache'] =[
                'app' => 'cache.adapter.array',
            ];

            $frameworkConf['router'] =[
                'utf8' => true,
            ];

            if ($this->enableSession) {
                $frameworkConf['session'] =[
                    'enabled' => true,
                    'storage_factory_id' => 'session.storage.factory.mock_file',
                ];
            }

            $container->loadFromExtension('framework', $frameworkConf);
            if ($this->enableSecurity) {
                $container->loadFromExtension('security', array(
                    'enable_authenticator_manager' => true,
                    'providers' => [
                        'in_memory' => [
                            'memory' => [
                                'users' => [
                                    'foo' => [
                                        'password' => 'bar',
                                        'roles' => 'ROLE_USER',
                                    ],
                               ],
                            ],
                        ],
                        'in_memory_other' => [
                            'memory' => [
                                'users' => [
                                    'foo' => [
                                        'password' => 'bar',
                                        'roles' => 'ROLE_USER',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'firewalls' => [
                        'main' => [
                            'provider' => 'in_memory'
                        ]
                    ],
                    'password_hashers' => [
                        InMemoryUser::class => 'plaintext',
                    ],
                ));
            }

            $graphqliteConf = array(
                'namespace' => [
                    'controllers' => $this->controllersNamespace,
                    'types' => $this->typesNamespace
                ],
            );

            if ($this->enableLogin) {
                $graphqliteConf['security']['enable_login'] = $this->enableLogin;
            }

            if ($this->enableMe) {
                $graphqliteConf['security']['enable_me'] = $this->enableMe;
            }

            if ($this->introspection === false) {
                $graphqliteConf['security']['introspection'] = false;
            }

            if ($this->maximumQueryComplexity !== null) {
                $graphqliteConf['security']['maximum_query_complexity'] = $this->maximumQueryComplexity;
            }

            if ($this->maximumQueryDepth !== null) {
                $graphqliteConf['security']['maximum_query_depth'] = $this->maximumQueryDepth;
            }

            $container->loadFromExtension('graphqlite', $graphqliteConf);
        });
        $confDir = $this->getProjectDir().'/Tests/Fixtures/config';

        $loader->load($confDir.'/{packages}/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{packages}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}_'.$this->environment.self::CONFIG_EXTS, 'glob');
    }

    // Note: typing is disabled because using different classes in Symfony 4 and 5
    protected function configureRoutes(/*RoutingConfigurator*/ $routes): void
    {
        $routes->import(__DIR__.'/../Resources/config/routes.xml');
    }

    public function getCacheDir(): string
    {
        return __DIR__.'/../cache/'.($this->enableSession?'withSession':'withoutSession').$this->enableLogin.($this->enableSecurity?'withSecurity':'withoutSecurity').$this->enableMe.'_'.($this->introspection?'withIntrospection':'withoutIntrospection').'_'.$this->maximumQueryComplexity.'_'.$this->maximumQueryDepth.'_'.md5(serialize($this->controllersNamespace).'_'.md5(serialize($this->typesNamespace)));
    }

    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('security.untracked_token_storage')) {
            $container->getDefinition('security.untracked_token_storage')->setPublic(true);
        }
    }
}
