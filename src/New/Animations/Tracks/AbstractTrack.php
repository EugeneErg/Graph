<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Tracks;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Collections\MixedCollection;
use EugeneErg\Graph\New\Animations\Collections\AbstractSegmentCollection;
use EugeneErg\Graph\New\Animations\Segments\SegmentInterface;

abstract class AbstractTrack
{
    protected function __construct(public readonly mixed $defaultValue, private AbstractSegmentCollection $segments)
    {
    }

    public function addSegment(SegmentInterface $segment, ?int $startMilliSecond = null): int
    {
        if ($startMilliSecond === null) {
            $startMilliSecond = $this->getDurationMilliSecond();
            $this->segments = $this->segments->set($segment, $startMilliSecond);

            return $startMilliSecond + $segment->getDurationMilliSecond();
        }

        if ($startMilliSecond < 0) {
            throw new \LogicException('The beginning cannot be negative.');
        }

        if ($this->segments->isEmpty()) {
            $this->segments = $this->segments->set($segment, $startMilliSecond);

            return $startMilliSecond + $segment->getDurationMilliSecond();
        }

        if (isset($this->segments[$startMilliSecond])) {
            throw new \LogicException('Segment intersection.');
        }

        [$index, $startMilliSecondA, $startMilliSecondB] = $this->get($startMilliSecond);

        $segmentA = $this->segments[$startMilliSecondA] ?? null;

        if ($segmentA !== null) {
            if (
                $this->isCrossed(
                    $startMilliSecondA,
                    $startMilliSecondA + $segmentA->getDurationMilliSecond(),
                    $startMilliSecond,
                )
            ) {
                throw new \LogicException('Segments is crossed.' . " {$startMilliSecondA}, {$segmentA->getDurationMilliSecond()}, {$startMilliSecond}");
            }
        }

        $segmentB = $this->segments[$startMilliSecondB] ?? null;

        if ($segmentB !== null) {
            if (
                $this->isCrossed(
                    $startMilliSecond,
                    $startMilliSecond + $segment->getDurationMilliSecond(),
                    $startMilliSecondB,
                )
            ) {
                throw new \LogicException('Segments is crossed.');
            }
        }

        $this->segments = $this->segments::fromReplace(
            $this->segments->slice(0, $index, preserveKeys: true),
            $this->segments::fromArray([$startMilliSecond => $segment]),
            $this->segments->slice($index, preserveKeys: true),
        );

        return $this->getDurationMilliSecond();
    }

    public function getSegments(): AbstractSegmentCollection
    {
        return (clone $this->segments)->setImmutable();
    }

    public function getValues(): MixedCollection
    {
        $prev = 0;
        $prevValue = $this->defaultValue;
        $result = [$prevValue];

        foreach ($this->segments as $start => $segment) {
            if ($start !== $prev) {
                $result[] = $prevValue;
            }

            $result[] = $segment->getValue();
            $prev = $start + $segment->getDurationMilliSecond();
            $prevValue = $segment->getValue();
        }

        return new MixedCollection($result);
    }

    public function getTimes(): IntegerCollection
    {
        $prev = 0;
        $result = [];

        foreach ($this->segments as $start => $segment) {
            if ($start !== $prev) {
                $result[] = $prev;
            }

            $result[] = $start;
            $prev = $start + $segment->getDurationMilliSecond();
        }

        $result[] = $prev;

        return new IntegerCollection($result);
    }

    public function getLastSegment(): ?SegmentInterface
    {
        return $this->segments->last();
    }

    public function getDurationMilliSecond(): int
    {
        return $this->segments->isEmpty()
            ? 0
            : $this->segments->lastKey() + $this->segments->last()->getDurationMilliSecond();
    }

    public function getDelayMilliSecond(): int
    {
        return $this->segments->isEmpty() ? 0 : $this->segments->firstKey();
    }

    private function isCrossed(int $from, int $to, int $pos): bool
    {
        return $from <= $pos && $pos < $to;
    }

    private function get(int $startMilliSecond): array
    {
        $startMilliSecondA = null;
        $index = 0;

        foreach ($this->segments as $startMilliSecondB => $currentSegment) {
            if ($startMilliSecond <= $startMilliSecondB) {
                return [$index, $startMilliSecondA, $startMilliSecondB];
            }

            $index++;
            $startMilliSecondA = $startMilliSecondB;
        }

        return [$index, $startMilliSecondA, null];
    }
}
