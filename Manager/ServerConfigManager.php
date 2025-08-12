<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Bundle\Manager;

use TheCodingMachine\GraphQLite\Bundle\Server\ServerConfig;

class ServerConfigManager
{
    /** @var array<string, ServerConfig> $serverConfigs */
    protected array $serverConfigs = [];

    protected ?int $debugFlag = null;

    protected array $validationRules = [];

    public function __construct(protected readonly SchemaManager $schemaManager)
    {}

    public function addConfig(string $namespace, ServerConfig $serverConfig): void
    {
        $this->serverConfigs[$namespace] = $serverConfig;
    }

    /** @param array<string, ServerConfig> $configs */
    public function setConfigs(array $configs): void
    {
        $this->serverConfigs = $configs;
    }

    public function getConfigByNamespace(string $namespace): ?ServerConfig
    {
        if (!isset($this->serverConfigs[$namespace])) {
           $this->serverConfigs[$namespace] = (new ServerConfig())->setSchema($this->schemaManager->getSchemaByNamespace($namespace));

           if ($this->debugFlag !== null) {
               $this->serverConfigs[$namespace]->setDebugFlag($this->debugFlag);
           }

            $this->serverConfigs[$namespace]->setValidationRules($this->validationRules);
        }

        return $this->serverConfigs[$namespace];
    }

    public function setDebugFlag(int $flag): void
    {
        $this->debugFlag = $flag;
    }

    public function getDebugFlag(): ?int
    {
        return $this->debugFlag;
    }

    public function setValidationRules(array $rules): void
    {
        $this->validationRules = $rules;
    }
}
