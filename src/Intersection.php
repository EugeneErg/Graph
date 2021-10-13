<?php declare(strict_types = 1);
namespace EugeneErg\Graphs;

class Intersection
{
    /** @var Vertex[] */
    private $vertexes;
    /** @var int[] */
    private $connections;
    /** @var bool|null */
    private $isOuter;

    /**
     * Intersection constructor.
     * @param int[] $vertexes
     * @param int[] $connections
     * @param bool|null $isOuter
     */
    public function __construct(array $vertexes, array $connections, ?bool $isOuter = null)
    {
        $this->vertexes = $vertexes;
        $this->connections = $connections;
        $this->isOuter = $isOuter;
    }

    /**
     * @param int[] $edge
     * @param Intersection $intersection
     * @return bool
     */
    public function isConflicted(array $edge, Intersection $intersection): bool
    {
        $can = 0;
        $step = 0;

        foreach ($edge as $vertexId) {
            $aIsConnected = $this->connections[$vertexId] ?? false;
            $bIsConnected = $intersection->connections[$vertexId] ?? false;

            if (!$aIsConnected && !$bIsConnected) {
                continue;
            }

            if (!$can) {
                $can = 3 - ($aIsConnected ? 1 : 0) - ($bIsConnected ? 2 : 0);
                $step += $can === 0;
            } elseif ($step === 1) {
                $step += ($can === 1 && $bIsConnected) || ($can === 2 && $aIsConnected);
            } else {
                $step += ($can === 1 && $aIsConnected) || ($can === 2 && $bIsConnected);
            }

            if ($step === 3) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Vertex[]
     */
    public function getVertexes(): array
    {
        return $this->vertexes;
    }

    /**
     * @return int[]
     */
    public function getConnections(): array
    {
        return $this->connections;
    }

    public function isOuter(): ?bool
    {
        return $this->isOuter;
    }

    public function setIsOuter(bool $isOuter = true): void
    {
        $this->isOuter = $isOuter;
    }
}
