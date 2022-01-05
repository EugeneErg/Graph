<?php declare(strict_types=1);
namespace EugeneErg\Graph\Dto\CSS;

use EugeneErg\Graph\Enums\CssAnimationFillModeEnum;

class CssPropertyAnimationDto
{
    private string $keyFrames;
    private int $duration;
    private int $delay;
    private CssAnimationFillModeEnum $fillMode;

    public function __construct(string $keyFrames, int $duration, int $delay = 0, CssAnimationFillModeEnum $fillMode = null)
    {
        $this->keyFrames = $keyFrames;
        $this->duration = $duration;
        $this->delay = $delay;
        $this->fillMode = $fillMode ?? CssAnimationFillModeEnum::FORWARDS();
    }

    public function __toString(): string
    {
        return "{$this->keyFrames} {$this->duration}s {$this->delay}s {$this->fillMode}";
    }

    public function getDelay(): int
    {
        return $this->delay;
    }

    public function getFillMode(): CssAnimationFillModeEnum
    {
        return $this->fillMode;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getKeyFrames(): string
    {
        return $this->keyFrames;
    }
}
