<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */


namespace Game\Units;

use Game\Game;
use Game\Map\MapObject;
use Game\Units\Actions\Action;
use Game\Units\Logic\Logic;

abstract class Unit {

    /** @var int */
    public $x;
    /** @var int */
    public $y;

    /** @var integer 0 is up. Clockwise */
    public $d;

    /** @var int remaining health */
    public $health;

    /** @var bool shows can this unit die of health loss or not */
    public $canDie = true;

    /** @var string can be 'player' or 'neutral' */
    public $ownerType = 'neutral';

    /** @var string wizard or monster */
    public $type;

    /** @var integer */
    public $level;

    /** @var Action */
    public $action;

    /** @var bool shows can this unit do actions or not */
    public $canDoActions = true;

    /** @var array */
    public $actionsHistory;

    /** @var array */
    public $events;

    /** @var integer Id in current game object */
    public $unitId;

    /** @var Logic */
    public $logic;

    /** @var Game */
    public $game;

    public function __construct($game, $data = array())
    {
        $this->game = $game;
    }

    /**
     * @return Action|mixed
     */
    public function action()
    {
        $this->triggerEvent(Event::ON_TICK);
        if (!$this->canDoActions) {
            return false;
        }
        if ($this->action) {
            $this->action->onProgress();
            if ($this->game->getTime() - $this->action->startTime >= $this->action->duration || $this->action->stop) {
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
            $this->action = $this->makeDecision($this, $this->game);
            $this->action->log();
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
     * @param \Game\Game $game
     *
     * @return Action
     */
    protected function makeDecision(Unit $unit , Game $game)
    {
        $logicName = $this->logic;
        return $logicName::decision($unit, $game);
    }

    /**
     * @return bool
     */
    public function isWizard()
    {
        return $this->type == 'wizard';
    }
} 