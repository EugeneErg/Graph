<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Processes\SvgAnimation\ValueObject;

use EugeneErg\Graph\Collections\AbstractCollection2;
use EugeneErg\Graph\Processes\Collections\OptionCollection;
use EugeneErg\Graph\Processes\Collections\StateCollection;
use EugeneErg\Graph\Services\AbstractService;
use EugeneErg\Graph\Services\Assert\Argument;
use EugeneErg\Graph\Services\Assert\ArgumentMode;
use EugeneErg\Graph\Services\AssertService;
use EugeneErg\Graph\ValueObjects\AbstractValueObject;
use EugeneErg\Graph\ValueObjects\Options\VisibleOption;

class State extends AbstractValueObject
{
    private int $delay;
    private int $duration;
    private OptionCollection $options;
    private ?self $parent;
    private self $root;

    public function __construct(
        OptionCollection $options,
        int $duration,
        int $delay = 0,
        ?self $parent = null
    ) {
        AssertService::instance()->notEmpty(new Argument($options, 1, 'options', new ArgumentMode(
            fn (AbstractCollection2 $options): array => $options->toArray(),
            'the number of elements in argument'
        )));
        $this->delay = $delay;
        $this->duration = $duration;
        $this->options = $options;
        $this->parent = $parent;

        if ($parent === null && $this->delay !== 0) {
            $parent = $this->createVisibleParent($this->delay);
        }

        $this->root = $parent->root ?? $this;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getRoot(): self
    {
        return $this->root;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getOptions(): OptionCollection
    {
        return $this->options;
    }

    public function getHistory(): StateCollection
    {
        $result = new StateCollection();
        $current = $this;

        while ($current !== null) {
            $result->unshift($current);
            $current = $current->parent;
        }

        return $result;
    }

    public function getAbsoluteDelay(): int
    {
        $result = 0;
        $current = $this;

        while ($current !== null) {
            $result += $current->delay + $current->duration;
            $current = $current->parent;
        }

        return $result;
    }

    public function __clone()
    {
        if ($this->parent !== null) {
            $this->parent = $this->createVisibleParent($this->parent->getAbsoluteDelay());
        }
    }

    private function createVisibleParent(int $delay): self
    {
        return new self(
            new OptionCollection([new VisibleOption(true)]),
            0,
            0,
            new self(
                new OptionCollection([new VisibleOption(true)]),
                $delay
            )
        );
    }
}
