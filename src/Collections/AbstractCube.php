<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections;

abstract class AbstractCube extends AbstractCollection2
{
    public function getItem($matrixKey, $collectionKey, $itemKey, bool $nullIfNotExists = false)
    {
        $matrix = $this->getMatrix($matrixKey, $nullIfNotExists);

        if ($matrix === null) {
            return null;
        }

        return $matrix->getItem($collectionKey, $itemKey, $nullIfNotExists);
    }

    public function unsetItem($matrixKey, $collectionKey, $itemKey): void
    {
        if ($this->issetMatrix($matrixKey)) {
            $this->getMatrix($matrixKey)->unsetItem($collectionKey, $itemKey);
        }
    }

    public function unsetMatrix($matrixKey): void
    {
        $this->unset($matrixKey);
    }

    public function setMatrix($matrixKey, AbstractMatrix2 $value)
    {
        return $this->set($matrixKey, $value);
    }

    public function unsetCollection($matrixKey, $collectionKey): void
    {
        if ($this->issetMatrix($matrixKey)) {
            $this->getMatrix($matrixKey)->unsetCollection($collectionKey);
        }
    }

    public function issetCollection($matrixKey, $collectionKey): bool
    {
        return $this->issetMatrix($matrixKey)
            && $this->getMatrix($matrixKey)->issetCollection($collectionKey);
    }

    public function getCollection($matrixKey, $collectionKey, bool $nullIfNotExists = false): ?AbstractLineCollection
    {
        $matrix = $this->getMatrix($matrixKey, $nullIfNotExists);

        if ($matrix === null) {
            return null;
        }

        return $matrix->getCollection($collectionKey, $nullIfNotExists);
    }

    public function getMatrix($matrixKey, bool $nullIfNotExists = false): ?AbstractMatrix2
    {
        return $nullIfNotExists === false || $this->isset($matrixKey)
            ? $this->get($matrixKey) : null;
    }

    public function setItem($matrixKey, $collectionKey, $itemKey, $value): void
    {
        if ($matrixKey === null || !$this->isset($matrixKey)) {
            /** @var AbstractMatrix2 $class */
            $class = static::ELEMENT_CLASS;
            $matrixKey = $this->setMatrix($matrixKey, $class::fromArray());
        }

        $this->getMatrix($matrixKey)->setItem($collectionKey, $itemKey, $value);
    }

    private function issetMatrix($matrixKey): bool
    {
        return $this->isset($matrixKey);
    }
}
