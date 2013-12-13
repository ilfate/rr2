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

    /** @var Point */
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

    /** @var array */
    public $events;

    /** @var integer Id in current game object */
    public $objectId;


    /**
     * @param MapObject  $map
     * @param \Game\Game $game
     *
     * @return Action|mixed
     */
    public function action(MapObject $map, Game $game)
    {
        if ($this->action) {
            $this->action->timeSpended += Game::TIME_STEP;
            $this->action->onProgress();
            if ($this->action->timeSpended >= $this->action->duration || $this->action->stop) {
                // this action is just ended
                $this->action->onEnd();
                $this->triggerEvent(Event::AFTER_ACTION);
                $this->actionsHistory[] = $this->action->code;
                $this->action = null;
            } else {
                return false;
            }
        }
        if (!$this->action) {
            // now we do not have action in progress.
            // here we need to make decision about next action
            $this->action = $this->makeDecision($this, $map, $game);
            $this->triggerEvent(Event::BEFORE_ACTION);
            $this->action->onStart();
        }
    }

    /**
     * @param Event  $event
     * @param string $type
     */
    public function setEvent(Event $event, $type)
    {
        if (!isset($this->events[$type])) {
            $this->events[$type] = array();
        }
        $this->events[$type][] = $event;
    }

    public function triggerEvent($type)
    {
        if (!empty($this->events[$type])) {
            $eventsCount = count($this->events[$type]);
            for ($eventNum = 0; $eventNum < $eventsCount; $eventNum++) {
                $this->events[$type][$eventNum]->trigger($this, $eventNum);
            }
        }
    }

    /**
     * @param Unit       $unit
     * @param MapObject  $map
     * @param \Game\Game $game
     *
     * @return Action
     */
    abstract protected function makeDecision(Unit $unit, MapObject $map, Game $game);
} 