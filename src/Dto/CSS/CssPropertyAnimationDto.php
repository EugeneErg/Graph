<?php declare(strict_types=1);
namespace EugeneErg\Graph\Dto\CSS;

use EugeneErg\Graph\Enums\CssAnimationFillModeEnum;

class CssPropertyAnimationDto
{
    private string $keyFrames;
    private float $duration;
    private float $delay;
    private CssAnimationFillModeEnum $fillMode;

    public function __construct(string $keyFrames, float $duration, float $delay = 0, CssAnimationFillModeEnum $fillMode = null)
    {
        $this->keyFrames = $keyFrames;
        $this->duration = $duration;
        $this->delay = $delay;
        $this->fillMode = $fillMode ?? CssAnimationFillModeEnum::FORWARDS();
    }

    public function __toString(): string
    {
        return "{$this->keyFrames} {$this->duration}s {$this->delay}s {$this->fillMode} linear";
    }

    public function getDelay(): float
    {
        return $this->delay;
    }

    public function getFillMode(): CssAnimationFillModeEnum
    {
        return $this->fillMode;
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function getKeyFrames(): string
    {
        return $this->keyFrames;
    }
}
