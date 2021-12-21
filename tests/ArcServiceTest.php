<?php declare(strict_types=1);
namespace EugeneErg\Tests;

use EugeneErg\Graph\Services\ArcService;
use PHPUnit\Framework\TestCase;

class ArcServiceTest extends TestCase
{
    /** @dataProvider createArcsData */
    public function testCreateArcs(array $edges, array $outerEdge, array $expected): void
    {
        $actual = ArcService::instance()->createArcs(
            Helper::instance()->createEdgeCollection($edges),
            Helper::instance()->createEdge($outerEdge)
        );

        var_dump($actual->toArrayRecursive());die;

        $this->assertEquals($expected, $actual->toArrayRecursive());
    }

    public function createArcsData(): array
    {
        return [
            /*[
                [
                    [2,1,0,3],
                    [1,2,0],
                ],
                [2,0,3],
                [
                    [
                        'vertexes' => [
                            [0,1,2]
                        ],
                        'gravity' => [3],
                    ]
                ],
            ],*/
            [
                [
                    [0,4,8,10],
                    [12,13,4,0],
                    [10,15,7,16],
                    [8,13,6],
                    [8,13,12],
                    [6,13,4,11],
                    [7,12,14,5],
                    [1,15,7],
                    [1,10,9],
                    [1,9,10,3,15],
                    [15,3,2],
                    [15,2,3,10],
                    [10,0,12,7,16],
                    [1,10,8,12,14],
                    [7,1,14,5],
                ],
                [6,8,4,11],
                [
                    [
                        'vertexes' => [
                            [],
                            [],
                        ],
                        'gravity' => [],
                    ],
                    [
                        'vertexes' => [
                            [],
                        ],
                        'gravity' => [],
                    ],
                    [
                        'vertexes' => [
                            [],
                        ],
                        'gravity' => [],
                    ],
                    [
                        'vertexes' => [
                            [],
                        ],
                        'gravity' => [],
                    ],
                    [
                        'vertexes' => [
                            [],
                        ],
                        'gravity' => [],
                    ],
                    [
                        'vertexes' => [
                            [],
                        ],
                        'gravity' => [],
                    ],
                    [
                        'vertexes' => [
                            [],
                            [],
                            [],
                        ],
                        'gravity' => [],
                    ],
                    [
                        'vertexes' => [
                            [],
                        ],
                        'gravity' => [],
                    ],
                    [
                        'vertexes' => [
                            [],
                        ],
                        'gravity' => [],
                    ],
                    [
                        'vertexes' => [
                            [],
                        ],
                        'gravity' => [],
                    ],
                    [
                        'vertexes' => [
                            [],
                        ],
                        'gravity' => [],
                    ],
                    [
                        'vertexes' => [
                            [],
                        ],
                        'gravity' => [],
                    ],
                    [
                        'vertexes' => [
                            [],
                        ],
                        'gravity' => [],
                    ],
                    [
                        'vertexes' => [
                            [],
                        ],
                        'gravity' => [],
                    ],
                    [
                        'vertexes' => [
                            [],
                        ],
                        'gravity' => [],
                    ],
                ],
            ],
            /*[
                [
                    [],
                    [],
                    [],
                ],
                [],
                [
                    [],
                    [],
                    [],
                ],
            ],*/
        ];
    }
}
