<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Effects;

use EugeneErg\Graph\New\Animations\Collections\AbstractTrackCollection;

interface EffectInterface
{
    public function apply(AbstractTrackCollection $tracks, ?int $startMilliSeconds = null): int;
}