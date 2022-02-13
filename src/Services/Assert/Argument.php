<?php declare(strict_types=1);
namespace EugeneErg\Graph\Services\Assert;

class Argument
{
    private $value;
    private ?int $number;
    private ?string $name;
    private ?ArgumentModeInterface $mode;

    public function __construct(
        $value,
        ?int $number = null,
        ?string $name = null,
        ArgumentModeInterface $mode = null
    ) {
        $this->value = $value;
        $this->number = $number;
        $this->name = $name;
        $this->mode = $mode;
    }

    public function getValue()
    {
        return $this->mode
            ? $this->mode->getValue($this->value)
            : $this->value;
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
        return $this->mode
            ? $this->mode->getPrefix($this->value)
            : 'argument';
    }
}
