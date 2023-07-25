<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Animators;

use EugeneErg\Graph\New\Animations\Collections\DataTransferObjectCollection;

class SvgAnimator implements AnimatorInterface
{
    public function generateContent(DataTransferObjectCollection $objects): string
    {
        ob_start();
        $this->echoTemplate(__DIR__ . '/Templates/svg.php', [
            'objects' => $objects,
            'width' => 1000,
            'height' => 1000,
            'top' => -500,
            'left' => -500,
        ]);

        return ob_get_clean();
    }

    private function echoTemplate(string $template, array $variables = []): void
    {
        extract($variables);

        require $template;
    }
}
