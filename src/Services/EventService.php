<?php declare(strict_types=1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\Collections\CallableCube;
use EugeneErg\Graph\Services\Event\EventInterface;

class EventService extends AbstractService
{
    private CallableCube $listeners;

    protected function created()
    {
        $this->listeners = new CallableCube();
    }

    public function listen(string $eventClass, callable $callback, string $tag = ''): int
    {
        $this->listeners->set($tag, $eventClass, null, $callback);

        return $this->listeners[$tag][$eventClass]->getKeyByPosition();
    }

    public function dontListen(string $eventClass, int $key, string $tag = ''): void
    {
        unset($this->listeners[$tag][$eventClass][$key]);
    }

    public function send(EventInterface $event, string $tag = ''): void
    {
        foreach ($this->listeners[$tag] ?? [] as $eventClass => $listeners) {
            if ($event instanceof $eventClass) {
                foreach ($listeners as $callback) {
                    $callback($event);
                }
            }
        }
    }
}
