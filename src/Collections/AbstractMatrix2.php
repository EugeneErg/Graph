<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections;

/**
 * @method AbstractLineCollection getValueByPosition(int $position = -1)
 */
class AbstractMatrix2 extends AbstractCollection2
{
    public function getItem($collectionKey, $itemKey, bool $nullIfNotExists = false)
    {
        $collection = $this->getCollection($collectionKey, $nullIfNotExists);

        return $collection === null ? null : $collection->get($itemKey, $nullIfNotExists);
    }

    public function setItem($collectionKey, $itemKey, $value): void
    {
        if ($collectionKey === null || !$this->isset($collectionKey)) {
            /** @var AbstractLineCollection $class */
            $class = static::ELEMENT_CLASS;
            $collectionKey = $this->setCollection($collectionKey, $class::fromArray());
        }

        $this->getCollection($collectionKey)->set($itemKey, $value);
    }

    public function issetItem($collectionKey, $itemKey): bool
    {
        return $this->issetCollection($collectionKey)
            && $this->getCollection($collectionKey)->isset($itemKey);
    }

    public function unsetItem($collectionKey, $itemKey): void
    {
        if ($this->issetCollection($collectionKey)) {
            $this->getCollection($collectionKey)->unset($itemKey);
        }
    }

    public function getCollection($collectionKey, bool $nullIfNotExists = false): ?AbstractLineCollection
    {
        return $this->get($collectionKey, $nullIfNotExists);
    }

    public function issetCollection($collectionKey): bool
    {
        return $this->isset($collectionKey);
    }

    public function setCollection($collectionKey, AbstractLineCollection $value)
    {
        return $this->set($collectionKey, $value);
    }

    public function unsetCollection($collectionKey): void
    {
        $this->unset($collectionKey);
    }
}
