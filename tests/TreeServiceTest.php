<?php declare(strict_types=1);

namespace EugeneErg\tests;

use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Services\TreeService;
use PHPUnit\Framework\TestCase;

class TreeServiceTest extends TestCase
{
    public function testCreateFromGraph(IntegerMatrix $graph): void
    {
        $result = TreeService::instance()->createFromGraph(Helper::instance()->createClearGraph($graph));



    }
}
