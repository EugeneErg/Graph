<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections;

abstract class AbstractCube extends AbstractCollection2
{
    public function getCell($width, $height, $depth, bool $nullIfNotExists = false)
    {
        return $nullIfNotExists === false || $this->isset($width, $height, $depth)
            ? $this->get($width, $height, $depth) : null;
    }

    public function setCell($width, $height, $depth, $value)
    {
        return $this->set($width, $height, $depth, $value);
    }

    public function issetCell($width, $height, $depth): bool
    {
        return $this->isset($width, $height, $depth);
    }

    public function unsetCell($width, $height, $depth): void
    {
        $this->unset($width, $height, $depth);
    }
}
