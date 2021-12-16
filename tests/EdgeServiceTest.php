<?php declare(strict_types=1);

namespace EugeneErg\Tests;

use EugeneErg\Graph\Services\EdgeService;
use PHPUnit\Framework\TestCase;

class EdgeServiceTest extends TestCase
{
    /** @dataProvider createEdgesFromGraphData */
    public function testCreateEdgesFromGraph(array $graph, array $expected): void
    {
        $actual = EdgeService::instance()->createEdgesFromGraph(Helper::instance()->createClearGraph($graph));
        $this->assertEquals($expected, $actual->toArrayRecursive());
    }

    public function createEdgesFromGraphData(): array
    {
        return [
            'single' => [
                [
                    [0,1,1],
                    [1,0,1],
                    [1,1,0],
                ],
                [
                    [
                        [
                            [0,1,2],
                        ],
                    ],
                ],
            ],
            'two line' => [
                [
                    [0,1,0,0],
                    [1,0,0,0],
                    [0,0,0,1],
                    [0,0,1,0],
                ],
                [
                    [
                        [
                            [0,1],
                        ],
                    ],
                    [
                        [
                            [2,3],
                        ],
                    ],
                ],
            ],
            'big graph' => [
                [
                    [0,1,0,0,0,0,1,0],
                    [1,0,1,0,1,0,0,0],
                    [0,1,0,1,1,1,0,0],
                    [0,0,1,0,0,1,0,0],
                    [0,1,1,0,0,0,1,1],
                    [0,0,1,1,0,0,0,1],
                    [1,0,0,0,1,0,0,1],
                    [0,0,0,0,1,1,1,0],
                ],
                [
                    [
                        [
                            [0,6,7,5,2,1],
                            [0,6,4,1],
                            [6,4,7],
                            [4,1,2],
                            [2,5,3],
                            [7,5,3,2,4],
                        ],
                    ],
                ],
            ],
        ];
    }
}
