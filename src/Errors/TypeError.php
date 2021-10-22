<?php declare(strict_types=1);
namespace EugeneErg\Graph\Errors;

use Throwable;

class TypeError extends \TypeError
{
    public function __construct(
        string $mustType,
        string $givenType,
        string $functionName,
        int $argumentNumber,
        ?string $className = null,
        ?string $argumentName = null,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(sprintf(
            '%sArgument #%s must be of type %s, %s given',
            ($className === null ? '' : "{$className}::") . $functionName,
            $argumentNumber . ($argumentName === null ? '' : " ({$argumentName})"),
            $mustType,
            $givenType
        ), $code, $previous);
    }
}
