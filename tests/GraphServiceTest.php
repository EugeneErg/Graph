<?php declare(strict_types=1);
namespace EugeneErg\Tests;

use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Services\GraphService;
use PHPUnit\Framework\TestCase;

class GraphServiceTest extends TestCase
{
    /** @dataProvider splitGraphOnDisconnectedData */
    public function testSplitGraphOnDisconnected(
        array $graph,
        array $expected
    ): void {
        $graphs = GraphService::instance()->splitGraphOnDisconnected(Helper::instance()
            ->createClearGraph(IntegerMatrix::fromRecursiveArray($graph)));
        $this->assertEquals($expected, $graphs->toArrayRecursive());
    }

    public function splitGraphOnDisconnectedData(): array
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
                        'connections' => [
                            0 => [1 => 1, 2 => 1],
                            1 => [0 => 1, 2 => 1],
                            2 => [0 => 1, 1 => 1],
                        ],
                        'vertexes' => [0,1,2],
                    ],
                ],
            ],
            [
                [
                    [0,1,0,0],
                    [1,0,0,0],
                    [0,0,0,1],
                    [0,0,1,0],
                ],
                [
                    [
                        'connections' => [
                            0 => [1 => 1],
                            1 => [0 => 1],
                        ],
                        'vertexes' => [0,1],
                    ],
                    [
                        'connections' => [
                            2 => [3 => 1],
                            3 => [2 => 1],
                        ],
                        'vertexes' => [2,3],
                    ],
                ],
            ],
            [
                [
                    [0,1,0],
                    [1,0,0],
                    [0,0,0],
                ],
                [
                    [
                        'connections' => [
                            0 => [1 => 1],
                            1 => [0 => 1],
                        ],
                        'vertexes' => [0,1],
                    ],
                    [
                        'connections' => [],
                        'vertexes' => [2],
                    ],
                ],
            ],
        ];
    }
}
