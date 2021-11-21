<?php declare(strict_types=1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\Services\Assert\Argument;
use TypeError;
use ValueError;

include_once __DIR__ . '/Assert/error_classes.php';

class AssertService extends AbstractService
{
    /**
     * @param array|string $expectedTypes
     * @param mixed $value
     * @param string|null $message
     * @param array|string|null $methodName
     */
    public function type(
        $expectedTypes,
        $value,
        ?string $message = null,
        $methodName = null
    ): void {
        if (!$expectedTypes instanceof Argument) {
            $expectedTypes = (array) $expectedTypes;
        }

        [$expectedTypesName, $expectedTypes] = $this->getNameAndValue($expectedTypes, function ($value): string {
            return implode('|', (array) $value);
        });
        [$argumentName, $value] = $this->getNameAndValue($value);
        $expectedTypes = (array) $expectedTypes;

        if (is_object($value)) {
            foreach ($expectedTypes as $class) {
                if (is_a($value, $class)) {
                   return;
                }
            }

            $type = get_class($value);
        } else {
            $type = gettype($value);

            if (in_array($type, $expectedTypes, true)) {
                return;
            }
        }

        throw new TypeError($this->createMessage($methodName, $argumentName, $message ?? implode(' ', [
            'must be of type',
            $expectedTypesName . ',',
            $type,
            'given',
        ])));
    }

    /**
     * @param $value
     * @param string|null $message
     * @param string|array|null $methodName
     */
    public function notEmpty(
        $value,
        ?string $message = null,
        $methodName = null
    ): void {
        [$argumentName, $value] = $this->getNameAndValue($value);

        if (empty($value)) {
            throw new ValueError(
                $this->createMessage($methodName, $argumentName, $message ?? 'cannot be empty')
            );
        }
    }

    /**
     * @param int|float|Argument $than
     * @param int|float|Argument $value
     * @param string|null $message
     * @param array|string|null $methodName
     */
    public function greater(
        $than,
        $value,
        ?string $message = null,
        $methodName = null
    ): void {
        [$argumentName, $value] = $this->getNameAndValue($value);
        [$thanName, $than] = $this->getNameAndValue($than, null);

        if ($value <= $than) {
            throw new ValueError($this->createMessage(
                $methodName,
                $argumentName,
                $message ?? ('must be greater than ' . $thanName)
            ));
        }
    }

    public function between(
        $from,
        $to,
        $value,
        ?string $message = null,
        $methodName = null
    ): void {
        [$fromName, $from] = $this->getNameAndValue($from, null);
        [$toName, $to] = $this->getNameAndValue($to, null);
        [$valueName, $value] = $this->getNameAndValue($value);

        if ($value < $from || $value > $to) {
            throw new ValueError($this->createMessage($methodName, $valueName, $message ?? implode(' ', [
                'must be between',
                $fromName,
                'and',
                $toName,
            ])));
        }
    }

    public function in($expectedValues, $value, ?string $message = null, $methodName = null): void
    {
        [$valueName, $value] = $this->getNameAndValue($value);
        [$expectedName, $expectedValues] = $this->getNameAndValue($expectedValues, function ($value): string {
            return '(' . implode(', ', (array) $value) . ')';
        });

        if (!in_array($value, $expectedValues, true)) {
            throw new ValueError($this->createMessage(
                $methodName,
                $valueName,
                $message ?? ('must be contained in ' . $expectedName)
            ));
        }
        //version_compare(): Argument #3 ($operator) must be a valid comparison operator
    }

    public function equals($expectedValue, $value, ?string $message = null, $methodName = null): void
    {
        [$valueName, $value] = $this->getNameAndValue($value);
        [$expectedName, $expectedValue] = $this->getNameAndValue($expectedValue, null);

        if ($value != $expectedValue) {
            throw new ValueError($this->createMessage(
                $methodName,
                $valueName,
                $message ?? ('must be equal to ' . $expectedName)
            ));
        }
    }

    public function lessOrEqual($expectedValue, $value, ?string $message = null, $methodName = null): void
    {
        [$valueName, $value] = $this->getNameAndValue($value);
        [$expectedName, $expectedValue] = $this->getNameAndValue($expectedValue, null);

        if ($value > $expectedValue) {
            throw new ValueError($this->createMessage(
                $methodName,
                $valueName,
                $message ?? ('must be less than or equal to ' . $expectedName)
            ));
        }

        //strpos(): Argument #3 ($offset) must be contained in argument #1 ($haystack)
    }

    public function matchesPattern($pattern, $value, ?string $message = null, $methodName = null): void
    {
        [$valueName, $value] = $this->getNameAndValue($value);
        [$patternName, $pattern] = $this->getNameAndValue($pattern, function ($value): string {
            if (is_string($value)) {
                return $value . (is_callable($value) ? '()' : '');
            }

            if (!is_array($value) || !is_callable($value)) {
                return 'pattern';
            }

            $class = array_shift($value);

            if (!is_string($class)) {
                $class = get_class($class);
            }

            array_unshift($value, $class);

            return implode('::', $value) . '()';
        });

        if (!is_callable($pattern)) {
            $pattern = function (string $value) use ($pattern): bool {
                return is_string($pattern) ? preg_match($pattern, $value) : $pattern == $value;
            };
        }

        if (!$pattern($value)) {
            throw new ValueError($this->createMessage(
                $methodName,
                $valueName,
                $message ?? ('must match ' . $patternName)
            ));
        }
    }

    private function createMessage(
        $methodName,
        string $argumentName,
        string $message
    ): string {
        if (is_array($methodName)) {
            $result = [implode('::', $methodName) . '():'];
        } elseif ($methodName === null) {
            $result = [];
        } else {
            $result = [$methodName . '():'];
        }

        $result[] = ucfirst($argumentName);
        $result[] = $message;

        return implode(' ', $result);
    }

    private function getNameAndValue($value, $defaultName = 'Value'): array
    {
        return $value instanceof Argument ? [
            $value->__toString(),
            $value->getValue(),
        ] : [
            $this->valueToString($value, $defaultName),
            $value,
        ];
    }

    private function valueToString($value, $default): string
    {
        if ($default === null) {
            return (string) $value;
        }

        if (is_callable($default)) {
            return $default($value);
        }

        return $default;
    }
}
