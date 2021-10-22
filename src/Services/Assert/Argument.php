<?php declare(strict_types=1);
namespace EugeneErg\Graph\Services\Assert;

class Argument
{
    public const MODE_COUNT = 'count';
    public const MODE_LENGTH = 'length';
    public const MODE_TYPE = 'type';

    private $value;
    /** @var int|null */
    private $number;
    /** @var string|null */
    private $name;
    /** @var string|null */
    private $mode;

    public function __construct($value, ?int $number = null, ?string $name = null, ?string $mode = null)
    {
        $this->value = $value;
        $this->number = $number;
        $this->name = $name;
        $this->mode = $mode;
    }

    public function getValue()
    {
        switch ($this->mode) {
            case self::MODE_COUNT: return count($this->value);
            case self::MODE_LENGTH: return strlen($this->value);
            case self::MODE_TYPE: return is_object($this->value) ? get_class($this->value) : gettype($this->value);
            default: return $this->value;
        }
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function __toString(): string
    {
        $result = [$this->getPrefix()];

        if ($this->number !== null) {
            $result[] = '#' . $this->number;
        }

        if ($this->name !== null) {
            $result[] = '($' . $this->name . ')';
        }

        return implode(' ', $result);
    }

    private function getPrefix(): string
    {
        switch ($this->mode) {
            case self::MODE_COUNT: return 'the number of elements in argument';
            case self::MODE_LENGTH: return 'the length of the value of the argument';
            case self::MODE_TYPE: return 'the type of the argument';
            default: return 'argument';
        }
    }
}
