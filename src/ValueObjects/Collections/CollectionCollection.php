<?php namespace EugeneErg\Graphs\ValueObjects\Collections;

class CollectionCollection extends AbstractObjectCollection
{
    public static function chunk(AbstractCollection $collection, int $size, bool $preserveKeys = false): self
    {
        return static::create(self::arraysToCollections(array_chunk($collection->records, $size, $preserveKeys)));
    }

    public function mergeRecursive(self ...$arrays): self
    {
        return static::map(static function(self ...$collections) {

        }, $this, ...$arrays);
        //todo array_merge_recursive('');
    }

    public function replaceRecursive(self ...$arrays): self
    {
        //todo array_replace_recursive('','');
    }

    public function walkRecursive(self ...$arrays): self
    {
        //todo array_walk_recursive('','');
    }

    private static function arraysToCollections(array ...$arrays): array
    {
        return array_map(static function (array $array): self {
            return static::create($array);
        }, $arrays);
    }
}