<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

/**
 * @see Solution::getTrouble()
 * @property-read Trouble $trouble
 * @see Solution::getFromVertex()
 * @property-read int $fromVertex
 * @see Solution::getToVertex()
 * @property-read int $toVertex
 * @see Solution::getType()
 * @property-read string $type
 * @see Solution::getFromPosition()
 * @property-read int|null $fromPosition
 * @see Solution::getToPosition()
 * @property-read int|null $toPosition
 */
class Solution extends AbstractValueObject
{
    public const TYPE_EMBEDDING = 'embedding';
    public const TYPE_ABSORPTION = 'absorption';
    public const TYPE_CIRCLE = 'circle';

    private $fromPosition;
    private $toPosition;
    private $type;
    private $trouble;
    private $fromVertex;
    private $toVertex;

    public function __construct(Trouble $trouble, int $fromVertex, int $toVertex)
    {
        $this->fromPosition = $this->getPosition($fromVertex, $trouble);
        $this->toPosition = $this->getPosition($toVertex, $trouble);
        $this->type = $this->calculateType();
        $this->trouble = $trouble;
        $this->fromVertex = $fromVertex;
        $this->toVertex = $toVertex;
    }

    private function getPosition(int $vertex, Trouble $trouble): ?int
    {
        $result = $trouble->vertexes->search($vertex, true);

        return $result === false ? null : $result;
    }

    private function calculateType(): string
    {
        if ($this->fromPosition === null || $this->toPosition === null) {
            return self::TYPE_ABSORPTION;
        }

        if ($this->fromPosition > $this->toPosition) {
            throw new \Exception('new test keys!');
            return self::TYPE_CIRCLE;
        }

        return self::TYPE_EMBEDDING;
    }

    protected function getType(): string
    {
        return $this->type;
    }

    protected function getFromPosition(): ?int
    {
        return $this->fromPosition;
    }

    protected function getToPosition(): ?int
    {
        return $this->toPosition;
    }

    public function getToVertex(): int
    {
        return $this->toVertex;
    }

    public function getFromVertex(): int
    {
        return $this->fromVertex;
    }

    public function getTrouble(): Trouble
    {
        return $this->trouble;
    }

    public function toArray(): array
    {
        return [
            'fromPosition' => $this->fromPosition,
            'toPosition' => $this->toPosition,
            'type' => $this->type,
            'trouble' => $this->trouble,
            'fromVertex' => $this->fromVertex,
            'toVertex' => $this->toVertex,
        ];
    }
}
