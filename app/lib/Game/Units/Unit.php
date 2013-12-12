<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */


namespace Game\Units;

use Game\Game;
use Game\Geometry\Point;
use Game\Map\MapObject;

abstract class Unit {

    /** @var Point  */
    public $point;

    /** @var integer 0 is up. Clockwise */
    public $d;

    /** @var string can be 'player' or 'neutral' */
    public $ownerType = 'neutral';

    /** @var integer */
    public $level;

    /** @var Action */
    public $action;

    /** @var array */
    public $actionsHistory;


    /**
     * @param MapObject $map
     * @param Wizard[]  $wizards
     * @param Unit[]    $objects
     *
     * @return Action|mixed
     */
    public function action(MapObject $map, $wizards, $objects)
    {
        if ($this->action) {
            $this->action->timeSpended += Game::TIME_STEP;
            $this->action->progressCheck();
            if ($this->action->timeSpended >= $this->action->duration || $this->action->stop) {
                // this action is just ended
                $this->action->onEnd();
                $this->actionsHistory[] = $this->action->code;
                $this->action = null;
            } else {
                return false;
            }
        }
        if (!$this->action) {
            // now we do not have action in progress.
            // here we need to make decision about next action
            $this->action = $this->makeDecision($this, $map, $wizards, $objects);
            $this->action->onStart();
        }
    }

    /**
     * @param Unit      $unit
     * @param MapObject $map
     * @param Wizard[]  $wizards
     * @param Unit[]    $objects
     *
     * @return Action
     */
    abstract protected function makeDecision(Unit $unit, MapObject $map, $wizards, $objects);
} 