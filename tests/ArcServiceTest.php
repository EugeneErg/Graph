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
                            [8,10,0,12,13],
                            [6],
                        ],
                        'gravity' => [8,4,6],
                    ],
                    [
                        'vertexes' => [
                            [13,4],
                        ],
                        'gravity' => [8,4,6],
                    ],
                    [
                        'vertexes' => [
                            [10,16,7,12],
                        ],
                        'gravity' => [0],
                    ],
                    [
                        'vertexes' => [
                            [10,15,7],
                        ],
                        'gravity' => [16],
                    ],
                    [
                        'vertexes' => [
                            [13,8],
                        ],
                        'gravity' => [6],
                    ],
                    [
                        'vertexes' => [
                            [12,8],
                        ],
                        'gravity' => [13],
                    ],
                    [
                        'vertexes' => [
                            [15,1],
                            [14,12],
                        ],
                        'gravity' => [15,7,12],
                    ],
                    [
                        'vertexes' => [
                            [1,7],
                        ],
                        'gravity' => [15,7,12],
                    ],
                    [
                        'vertexes' => [
                            [7,5,14],
                        ],
                        'gravity' => [15,7,12],
                    ],
                    [
                        'vertexes' => [
                            [1,10],
                        ],
                        'gravity' => [12],
                    ],
                    [
                        'vertexes' => [
                            [10,3,15],
                        ],
                        'gravity' => [15,1,10],
                    ],
                    [
                        'vertexes' => [
                            [1,9,10],
                        ],
                        'gravity' => [15,1,10],
                    ],
                    [
                        'vertexes' => [
                            [4,0],
                        ],
                        'gravity' => [10],
                    ],
                    [
                        'vertexes' => [
                            [15,2,3],
                        ],
                        'gravity' => [10],
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
