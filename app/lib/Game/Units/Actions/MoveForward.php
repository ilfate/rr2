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
        // the list of all who was able to see me at the old point
        $whoWasAbleToSeeMe = $this->game->map->getWatchman($oldPoint[0], $oldPoint[1]);

        $this->game->map->moveUnit($oldPoint[0], $oldPoint[1], $this->unit);

        // here we will check is some one new can see this unit
        // if so, then we need to send a message about it to them
        $whoCanSeeMeHere = $this->game->map->getWatchman($this->unit->x, $this->unit->x);
        foreach ($whoCanSeeMeHere as $userId) {
            $wizardWhoSeeMe = $this->game->getUnit($this->game->getWizardIdByUserId($userId));
            if (!isset($wizardWhoSeeMe->unitsVisible[$this->unit->unitId])) {
                $this->game->unitAppearsOnScreen($this->unit, $userId, 'inc');
            }
        }
        // here we will check who is now dont see me. I need to disappear for their screen
        $whoCanSeeMeHere = array_unique(array_merge($whoCanSeeMeHere, $whoWasAbleToSeeMe));

        if ($this->unit->isWizard()) {
            // is this is wizard, than we need to move the screen for user.
            $this->game->addLog([$this->unit->userId], $this->unit->unitId, 'vm', [1, $this->unit->d]);
        }
        self::log(['whoCanSeeMeHere' => $whoCanSeeMeHere]);
    }

    public function log($params = null)
    {
        // we are saving current watchers list. But We need to log all user who also was able to see me before

        $this->watchers = $this->game->map->getWatchman($this->unit->x, $this->unit->y);

        $this->game->addLog($params['whoCanSeeMeHere'], $this->unit->unitId, $this->code, $this->data);
    }
} 