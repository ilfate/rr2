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

        // here we will check is some one new can see this unit
        // if so, then we need to send a message about it to them
        $whoCanSeeMeHere = $this->game->map->getWatchman($this->unit->x, $this->unit->x);
        foreach ($whoCanSeeMeHere as $unitId) {
            if (!isset($this->game->getUnit($unitId)->unitsVisible[$this->unit->unitId])) {
                $this->game->unitAppearsOnScreen($this->unit->unitId, $this->game->getUnit($unitId)->userId);
            }
        }
        if ($this->unit->isWizard()) {
            // is this is wizard, than we need to move the screen for user.
            $this->game->addLog([$this->unit->userId], $this->unit->unitId, 'vm', [1, $this->unit->d]);
        }
    }
} 