<?php declare(strict_types=1);

namespace EugeneErg\Tests;

use EugeneErg\Graph\Services\TreeService;
use PHPUnit\Framework\TestCase;

class TreeServiceTest extends TestCase
{
    /** @dataProvider createFromGraphData */
    public function testCreateFromGraph(array $graph, array $expected): void
    {
        $trees = TreeService::instance()->createFromGraph(Helper::instance()
            ->createClearGraph($graph));
        $this->assertEquals($expected, $trees->toArrayRecursive());
    }

    public function createFromGraphData(): array
    {
        return [
            'one graph' => [
                [
                    [0, 1, 1],
                    [1, 0, 1],
                    [1, 1, 0],
                ],
                [
                    [
                        'graph' => [
                            'connections' => [
                                0 => [1 => 1, 2 => 1],
                                1 => [0 => 1, 2 => 1],
                                2 => [0 => 1, 1 => 1],
                            ],
                            'vertexes' => [0, 1, 2],
                        ],
                        'branches' => [],
                        'connections' => [
                            'connections' => [],
                            'vertexes' => [],
                        ],
                    ],
                ],
            ],
            'two graphs' => [
                [
                    [0, 1, 0],
                    [1, 0, 1],
                    [0, 1, 0],
                ],
                [
                    [
                        'graph' => [
                            'connections' => [
                                0 => [1 => 1],
                                1 => [0 => 1, 2 => 1],
                                2 => [1 => 1],
                            ],
                            'vertexes' => [0, 1, 2],
                        ],
                        'branches' => [
                            [
                                'connections' => [
                                    0 => [1 => 1],
                                    1 => [0 => 1],
                                ],
                                'vertexes' => [0, 1],
                            ],
                            [
                                'connections' => [
                                    2 => [1 => 1],
                                    1 => [2 => 1],
                                ],
                                'vertexes' => [2, 1],
                            ]
                        ],
                        'connections' => [
                            'connections' => [
                                0 => [0 => 0],
                                1 => [1 => 1],
                                2 => [2 => 2],
                            ],
                            'vertexes' => [],
                        ],
                    ],
                ],
            ],
            'two trees' => [
                [
                    [0, 1, 0],
                    [1, 0, 0],
                    [0, 0, 0],
                ],
                [
                    [
                        'graph' => [
                            'connections' => [
                                0 => [1 => 1],
                                1 => [0 => 1],
                            ],
                            'vertexes' => [0, 1],
                        ],
                        'branches' => [],
                        'connections' => [
                            'connections' => [
                            ],
                            'vertexes' => [],
                        ],
                    ],
                    [
                        'graph' => [
                            'connections' => [],
                            'vertexes' => [2],
                        ],
                        'branches' => [],
                        'connections' => [
                            'connections' => [
                            ],
                            'vertexes' => [],
                        ],
                    ],
                ],
            ],
        ];
    }
}
