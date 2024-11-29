<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Bundle\Server;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Server\OperationParams;
use GraphQL\Server\ServerConfig as BaseServerConfig;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\ValidationRule;

/**
 * A slightly modified version of the server config: default validators are added by default when setValidators is called.
 */
class ServerConfig extends BaseServerConfig
{
    /**
     * Set validation rules for this server, AND adds by default all the "default" validation rules provided by Webonyx
     *
     * @param ValidationRule[]|callable $validationRules
     *
     * @api
     */
    public function setValidationRules($validationRules): BaseServerConfig
    {
        parent::setValidationRules(
            static function (OperationParams $params, DocumentNode $doc, string $operationType) use ($validationRules): array {
                $validationRules = is_callable($validationRules)
                    ? $validationRules($params, $doc, $operationType)
                    : $validationRules;

                return array_merge(DocumentValidator::defaultRules(), $validationRules);
            }
        );

        return $this;
    }
}
