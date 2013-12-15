<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */


namespace Game\Units\Actions;

class MoveForward extends Action{
    public $code = 'mf';
    public $duration = 1000;

    public function onStart()
    {
        $oldPoint = [$this->unit->x, $this->unit->y];
        list($this->unit->x, $this->unit->y) = $this->game->map->getNextCoords($this->unit->x, $this->unit->y, $this->unit->d);
        $this->game->map->moveUnit($oldPoint[0], $oldPoint[1], $this->unit);
    }
} 