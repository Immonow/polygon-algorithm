<?php

declare(strict_types=1);

namespace MartinezRueda;

/**
 * Class Debug
 * @package MartinezRueda
 */
class Debug
{
    public static $debug_on = false;

    public static function debug(callable $callee): void
    {
        if (self::$debug_on) {
            $callee();
        }
    }

    public static function gatherSweepEventData(SweepEvent $event): string
    {
        $data = [
            'index' => $event->id,
            'is_left' => $event->is_left ? 1 : 0,
            'x' => $event->p->x,
            'y' => $event->p->y,
            'other' => ['x' => $event->other->p->x, 'y' => $event->other->p->y]
        ];

        return json_encode($data);
    }

    public static function gatherConnectorData(Connector $connector): string
    {
        $open_polygons = [];
        $closed_polygons = [];

        foreach ($connector->open_polygons as $chain) {
            $open_polygons[] = self::gatherPointChainData($chain);
        }

        foreach ($connector->closed_polygons as $chain) {
            $closed_polygons[] = self::gatherPointChainData($chain);
        }

        $data = [
            'closed' => $connector->isClosed() ? 1 : 0,
            'open_polygons' => $open_polygons,
            'closed_polygons' => $closed_polygons
        ];

        return json_encode($data);
    }

    protected static function gatherPointChainData(PointChain $chain): array
    {
        $points = [];

        if (!empty($chain->segments)) {
            foreach ($chain->segments as $point) {
                $points[] = ['x' => $point->x, 'y' => $point->y];
            }
        }

        return [
            'closed' => $chain->closed ? 1 : 0,
            'elements' => $points
        ];
    }
}
