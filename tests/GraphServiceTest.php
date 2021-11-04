<?php declare(strict_types=1);
namespace EugeneErg\tests;

use EugeneErg\Graph\Collections\Collection;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Services\GraphService;
use EugeneErg\Graph\ValueObjects\ClearGraph;
use EugeneErg\Graphs\Edge;
use PHPUnit\Framework\TestCase;

class GraphServiceTest extends TestCase
{
    /** @dataProvider splitGraphOnDisconnectedData */
    public function testSplitGraphOnDisconnected(
        IntegerMatrix $graph,
        Collection $expectedConnections,
        IntegerMatrix $expectedVertexes
    ): void {
        $graphs = GraphService::instance()->splitGraphOnDisconnected(Helper::instance()->createClearGraph($graph));
        $resultConnections = Collection::fromMap(function (ClearGraph $graph): IntegerMatrix {
            return $graph->connections;
        }, false, $graphs);
        $resultVertexes = IntegerMatrix::fromMap(function (ClearGraph $graph): IntegerCollection {
            return $graph->vertexes;
        }, false, $graphs);
        $this->assertEquals($expectedConnections, $resultConnections);
        $this->assertEquals($expectedVertexes, $resultVertexes);
    }

    /** @dataProvider getEdgesData */
    public function testGetEdges(IntegerMatrix $graph, Collection $expected, bool $hasError): void
    {
        if ($hasError) {
            $this->expectException(\Exception::class);
        }

        $edges = GraphService::instance()->getEdges(new ClearGraph($graph));
        $result = array_map(function (Edge $edge): array {
            return $edge->toArray();
        }, $edges->toArray());
        $this->assertEquals(array_diff($expected, $result), array_diff($result, $expected));
    }

    public function splitGraphOnDisconnectedData(): array
    {
        return [
            'single' => [
                IntegerMatrix::fromRecursiveArray([
                    [0,1,1],
                    [1,0,1],
                    [1,1,0],
                ]),
                new Collection([
                    IntegerMatrix::fromRecursiveArray([
                        0 => [1 => 1, 2 => 1],
                        1 => [0 => 1, 2 => 1],
                        2 => [0 => 1, 1 => 1],
                    ]),
                ]),
                IntegerMatrix::fromRecursiveArray([
                    [0,1,2],
                ]),
            ],
            [
                IntegerMatrix::fromRecursiveArray([
                    [0,1,0,0],
                    [1,0,0,0],
                    [0,0,0,1],
                    [0,0,1,0],
                ]),
                new Collection([
                    IntegerMatrix::fromRecursiveArray([
                        0 => [1 => 1],
                        1 => [0 => 1],
                    ]),
                    IntegerMatrix::fromRecursiveArray([
                        2 => [3 => 1],
                        3 => [2 => 1],
                    ]),
                ]),
                IntegerMatrix::fromRecursiveArray([
                    [0,1],
                    [2,3],
                ]),
            ],
            [
                IntegerMatrix::fromRecursiveArray([
                    [0,1,0],
                    [1,0,0],
                    [0,0,0],
                ]),
                new Collection([
                    IntegerMatrix::fromRecursiveArray([
                        0 => [1 => 1],
                        1 => [0 => 1],
                    ]),
                    IntegerMatrix::fromRecursiveArray(),
                ]),
                IntegerMatrix::fromRecursiveArray([
                    [0,1],
                    [2],
                ]),
            ],
        ];
    }

    public function getEdgesData(): array
    {
        return [
            '' => [
                [
                    [0,1,1],
                    [1,0,1],
                    [1,1,0],
                ],
                [
                    [1,2,3],
                ],
                false,
            ],
        ];
    }
}
