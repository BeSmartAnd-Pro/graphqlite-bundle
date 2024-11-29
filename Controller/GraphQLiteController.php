<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Bundle\Controller;

use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use TheCodingMachine\GraphQLite\Bundle\Manager\SchemaManager;
use TheCodingMachine\GraphQLite\Bundle\Manager\ServerConfigManager;
use TheCodingMachine\GraphQLite\Http\HttpCodeDecider;
use TheCodingMachine\GraphQLite\Http\HttpCodeDeciderInterface;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use GraphQL\Upload\UploadMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use TheCodingMachine\GraphQLite\Bundle\Context\SymfonyGraphQLContext;

/**
 * Listens to every single request and forward Graphql requests to Graphql Webonix standardServer.
 */
class GraphQLiteController
{
    private HttpMessageFactoryInterface|PsrHttpFactory $httpMessageFactory;

    private int $debug;

    private HttpCodeDeciderInterface|HttpCodeDecider $httpCodeDecider;

    public function __construct(
        private readonly ServerConfigManager $serverConfigManager,
        private readonly SchemaManager $schemaManager,
        ?HttpMessageFactoryInterface $httpMessageFactory = null,
        ?int $debug = null,
        ?HttpCodeDeciderInterface $httpCodeDecider = null
    ) {
        $this->httpMessageFactory = $httpMessageFactory ?: new PsrHttpFactory(new ServerRequestFactory(), new StreamFactory(), new UploadedFileFactory(), new ResponseFactory());
        $this->debug = $debug ?? $this->serverConfigManager->getDebugFlag();
        $this->httpCodeDecider = $httpCodeDecider ?? new HttpCodeDecider();
    }

    public function loadRoutes(): RouteCollection
    {
        $routes = new RouteCollection();

        $path = '/graphql/{namespace}';
        $defaults = [
            '_controller' => self::class.'::handleRequest',
            'namespace' => 'default'
        ];
        $requirements = [
          'namespace' => '[a-zA-Z0-9_-]*',
        ];
        $route = new Route($path, $defaults, $requirements);

        // add the new route to the route collection
        $routeName = 'graphqlite_endpoint';
        $routes->add($routeName, $route);

        return $routes;
    }

    public function handleRequest(string $namespace = 'default', Request $request): Response
    {
        $psr7Request = $this->httpMessageFactory->createRequest($request);

        if (strtoupper($request->getMethod()) === Request::METHOD_POST && empty($psr7Request->getParsedBody())) {
            $content = $psr7Request->getBody()->getContents();
            $parsedBody = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException('Invalid JSON received in POST body: '.json_last_error_msg());
            }

            if (!is_array($parsedBody)){
                throw new RuntimeException('Expecting associative array from request, got ' . gettype($parsedBody));
            }

            $psr7Request = $psr7Request->withParsedBody($parsedBody);
        }

        // Let's parse the request and adapt it for file uploads.
        if (class_exists(UploadMiddleware::class)) {
            $uploadMiddleware = new UploadMiddleware();
            $psr7Request = $uploadMiddleware->processRequest($psr7Request);
        }

        return $this->handlePsr7Request($namespace, $psr7Request, $request);
    }

    private function handlePsr7Request(string $namespace, ServerRequestInterface $request, Request $symfonyRequest): JsonResponse
    {
        // Let's put the request in the context.
        $serverConfig = $this->serverConfigManager->getConfigByNamespace($namespace);
        $serverConfig->setContext(new SymfonyGraphQLContext($symfonyRequest));

        $serverConfig->setSchema($this->schemaManager->getSchemaByNamespace($namespace));

        $standardService = new StandardServer($serverConfig);
        $result = $standardService->executePsrRequest($request);

        if ($result instanceof ExecutionResult) {
            return new JsonResponse($result->toArray($this->debug), $this->httpCodeDecider->decideHttpStatusCode($result));
        }

        if (is_array($result)) {
            $finalResult = array_map(
                function (ExecutionResult $executionResult): array {
                    return $executionResult->toArray($this->debug);
                },
                $result
            );

            // Let's return the highest result.
            $statuses = array_map([$this->httpCodeDecider, 'decideHttpStatusCode'], $result);
            $status = empty($statuses) ? 500 : max($statuses);

            return new JsonResponse($finalResult, $status);
        }

        throw new RuntimeException('Only SyncPromiseAdapter is supported');
    }
}
