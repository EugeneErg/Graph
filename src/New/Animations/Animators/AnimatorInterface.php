<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Animators;

use EugeneErg\Graph\New\Animations\Collections\DataTransferObjectCollection;

interface AnimatorInterface
{
    public function generateContent(DataTransferObjectCollection $objects): string;
}