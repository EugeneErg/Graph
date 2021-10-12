<?php namespace EugeneErg\Graphs\ValueObjects;

/**
 * @property-read Trouble $trouble
 * @property-read int $fromVertex
 * @property-read int $toVertex
 * @see Solution::getTypeAttribute()
 * @property-read string $type
 * @see Solution::getFromPositionAttribute()
 * @property-read int|null $fromPosition
 * @see Solution::getToPositionAttribute()
 * @property-read int|null $toPosition
 */
class Solution extends AbstractValueObjectMutable
{
    public const TYPE_EMBEDDING = 'embedding';
    public const TYPE_ABSORPTION = 'absorption';
    public const TYPE_CIRCLE = 'circle';

    private $fromPosition;
    private $toPosition;
    private $type;

    public function __construct(Trouble $trouble, int $fromVertex, int $toVertex)
    {
        $this->fromPosition = $this->getPosition($fromVertex, $trouble);
        $this->toPosition = $this->getPosition($toVertex, $trouble);
        $this->type = $this->getType();

        parent::__construct($trouble, $fromVertex, $toVertex);
    }

    private function getPosition(int $vertex, Trouble $trouble): ?int
    {
        $result = array_search($vertex, $trouble->vertexes, true);

        return $result === false ? null : $result;
    }

    private function getType(): string
    {
        if ($this->fromPosition === null || $this->toPosition === null) {
            return self::TYPE_ABSORPTION;
        }

        if ($this->fromPosition > $this->toPosition) {
            return self::TYPE_CIRCLE;
        }

        return self::TYPE_EMBEDDING;
    }

    protected function getTypeAttribute(): string
    {
        return $this->type;
    }

    protected function getFromPositionAttribute(): ?int
    {
        return $this->fromPosition;
    }

    protected function getToPositionAttribute(): ?int
    {
        return $this->toPosition;
    }
}
