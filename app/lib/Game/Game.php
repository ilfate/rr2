<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2012
 */
/**
 * Created by PhpStorm.
 * User: ilfate
 * Date: 12/11/13
 * Time: 10:48 PM
 */

namespace Game;

use Game\Map\MapObject;
use Game\Units\Monsters;
use Game\Units\Unit;

class Game
{

    /** This value is border between loading all chunks if there less cells than in this value
     * or load only needed chunks if there is more cells than this value */
    const MAX_CELLS_IN_MAP_TO_LOAD_ALL_CHUNKS = 10000;

    /** time for step in milli seconds */
    const TIME_STEP = 200;

    /** How long is game running without stopping */
    const RUN_TIME_LIMIT = 5000;

    /** @var GameExecuter */
    protected $gameExecuter;

    /** @var MapObject */
    public $map;

    /** @var integer current game time */
    protected $time;

    protected $runTime = 0;

    /** @var Unit[] */
    protected $units;
    protected $countUnits;

    /** @var array here we will store all logs for displaying */
    protected $log = array();

    /** @var bool if false then we stop executing */
    private $running = true;

    /**
     * @param $data
     * @param $gameExecuter
     */
    public function __construct($data, $gameExecuter)
    {
        $this->time = $data['time'];
        $this->gameExecuter = $gameExecuter;

        $this->map = new MapObject($data);

        // here we decide should we load all chunks for map or we will load only necessary ones
        if ($this->map->mapWidth * $this->map->mapHeight * $this->map->chunkSize
            <= self::MAX_CELLS_IN_MAP_TO_LOAD_ALL_CHUNKS
        ) {
            $this->map->setChunks($this->gameExecuter->loadAllChunks($this->map->battleMapId));
        }

        foreach ($data['wizards'] as $wizard) {
            $wizardClass = 'Game\Units\Wizards\\' . ucfirst($wizard['class']);
            $this->units[] = new $wizardClass($this, $wizard);
            $wizardId = count($this->units) - 1;
            $this->units[$wizardId]->unitId = $wizardId;
            // create log spot for user
            $this->log[$wizard['userId']] = array();
        }
        $monsters = $this->gameExecuter->loadMonsters($this->map->battleMapId);
        if ($monsters) {
            foreach ($monsters as $monsterData) {
                $monsterClass = 'Game\Units\Monsters\\' . ucfirst($monsterData['type']);
                $this->units[] = new $monsterClass($monsterData);
                $monsterId = count($this->units) - 1;
                $this->units[$monsterId]->unitId = $monsterId;
            }
        }
        $this->countUnits = count($this->units);
        $this->map->putUnitsOnTheMap($this->units);
    }

    /**
     * Main run method. One run = one tick
     */
    public function run()
    {
        while ($this->running) {
            for($i = 0; $i < $this->countUnits; $i++) {
                $this->units[$i]->action($this);
            }

            $this->time    += self::TIME_STEP;
            $this->runTime += self::TIME_STEP;

            if ($this->runTime >= self::RUN_TIME_LIMIT) {
                $this->stop();
            }
        }
    }

    /**
     * @param $id
     *
     * @return Unit
     */
    public function getUnit($id)
    {
        return $this->units[$id];
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param array $watchers
     * @param       $performer
     * @param       $code
     * @param array $data
     */
    public function addLog(array $watchers, $performer, $code, $data = array())
    {
        foreach ($watchers as $watcherId)
        {
            // who | what | when | with what
            $this->log[$watcherId][] = array($performer, $code, $this->getTime(), $data);
        }
    }

    protected function saveUnits()
    {
        foreach ($this->units as $unit) {
            if ($unit->isWizard()) {
                $this->gameExecuter->saveWizard($unit->prepareToSave());
            } else {
                $monsterData = $unit->prepareToSave();
                if ($unit->isSaved) {
                    $this->gameExecuter->saveMonster($monsterData);
                } else {
                    $this->gameExecuter->newMonster($monsterData);
                }
            }
        }
        $this->gameExecuter->saveMap(array(
            'id'   => $this->map->battleMapId,
            'time' => $this->getTime()
        ));
    }


    public function stop()
    {
        $this->running = false;
        $this->saveUnits();
        echo 'hello world!';
    }

    public function spawnMonster()
    {

    }
}