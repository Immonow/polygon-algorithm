<?php

declare(strict_types=1);

namespace MartinezRueda;

/**
 * Priority queue that holds sweep-events sorted from left to right.
 *
 * Class PriorityQueue
 * @package MartinezRueda
 */
class PriorityQueue
{
    /**
     * Array of SweepEvents sorted from left to right
     *
     * @var array
     */
    public $events = [];

    protected $sorted = false;

    public function size(): int
    {
        return count($this->events);
    }

    public function isEmpty(): bool
    {
        return empty($this->events);
    }

    public function enqueue(SweepEvent $event): void
    {
        if (!$this->isSorted()) {
            $this->events[] = $event;
            return;
        }

        if (count($this->events) <= 0) {
            $this->events[] = $event;
            return;
        }

        // priority queue is sorted, shift elements to the right and find place for event
        for ($i = count($this->events) - 1; $i >= 0 && $this->compare($event, $this->events[$i]); $i--) {
            $this->events[$i + 1] = $this->events[$i];
        }

        $this->events[$i + 1] = $event;
    }

    public function dequeue(): SweepEvent
    {
        if (!$this->isSorted()) {
            $this->sort();
            $this->sorted = true;
        }

        return array_pop($this->events);
    }

    public function sort(): void
    {
        uasort(
            $this->events,
            fn ($event1, $event2): int => $this->compare($event1, $event2) ? -1 : 1
        );

        // We should actualize indexes, because of hash-table nature.
        // array_values() is faster than juggling with indexes.
        $this->events = array_values($this->events);
    }

    public function isSorted(): bool
    {
        return $this->sorted;
    }

    protected function compare(SweepEvent $event1, SweepEvent $event2): bool
    {
        return Helper::compareSweepEvents($event1, $event2);
    }
}
