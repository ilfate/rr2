<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */

namespace Game\Units\Actions;

use Game\Game;
use Game\Units\Unit;

abstract class Action
{

    public $startTime;
    public $duration;
    public $code;
    public $stop = false;

    public $watchers = array();
    public $data = array();

    /** @var Unit */
    protected $unit;
    /** @var Game */
    protected $game;

    public function __construct(Unit $unit, Game $game)
    {
        $this->startTime = $game->getTime();
        $this->unit      = $unit;
        $this->game      = $game;

        $this->game->addLog($this->watchers, $this->unit->objectId, $this->code, $this->data);
    }

    public function onEnd()
    {
    }

    public function onStart()
    {
    }

    public function onProgress()
    {
    }
} 