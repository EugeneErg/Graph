<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Services;

use EugeneErg\Graph\New\Collections\CallableCollection;
use EugeneErg\Graph\New\Collections\CallableMatrix;
use EugeneErg\Graph\New\Events\EventInterface;

class EventService
{
    private readonly CallableMatrix $listeners;

    public function __construct()
    {
        $this->listeners = new CallableMatrix(immutable: false);
    }

    public function listen(string $eventClass, callable $callback): int
    {
        if (!isset($this->listeners[$eventClass])) {
            $this->listeners[$eventClass] = new CallableCollection(immutable: false);
        }

        $this->listeners[$eventClass]->set($callback);

        return $this->listeners[$eventClass]->lastKey();
    }

    public function dontListen(array $classKeys): void
    {
        foreach ($classKeys as $eventClass => $key) {
            unset($this->listeners[$eventClass][$key]);
        }
    }

    public function send(EventInterface $event): void
    {
        foreach ($this->listeners as $eventClass => $listeners) {
            if ($event instanceof $eventClass) {
                foreach ($listeners as $callback) {
                    $callback($event);
                }
            }
        }
    }
}
