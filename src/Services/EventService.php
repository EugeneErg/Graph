<?php declare(strict_types=1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\Collections\CallableMatrix;

class EventService extends AbstractService
{
    private CallableMatrix $listeners;

    protected function created()
    {
        $this->listeners = new CallableMatrix();
    }

    public function listen(string $eventClass, callable $callback): int
    {
        return $this->listeners->setItem($eventClass, null, $callback);
    }

    public function dontListen(string $eventClass, int $key): void
    {
        $this->listeners->unsetItem($eventClass, $key);
    }

    public function send(object $event): void
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
