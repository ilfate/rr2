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

    /** @var array of relation user -> wizard */
    protected $usersWizards = array();

    /**
     * @param $data
     * @param $gameExecuter
     */
    public function __construct($data, $gameExecuter)
    {
        $this->time = $data['time'];
        $this->gameExecuter = $gameExecuter;

        $this->map = new MapObject($data, null, $this);

        // here we decide should we load all chunks for map or we will load only necessary ones
        if ($this->map->mapWidth * $this->map->mapHeight * $this->map->chunkSize
            <= self::MAX_CELLS_IN_MAP_TO_LOAD_ALL_CHUNKS
        ) {
            $this->map->setChunks($this->gameExecuter->loadAllChunks($this->map->battleMapId));
        }

        foreach ($data['wizards'] as $wizard) {
            $wizardClass = 'Game\Units\Wizards\\' . ucfirst($wizard['class']);
            $wizardId = $this->addUnit(new $wizardClass($this, $wizard), false);
            $this->usersWizards[$wizard['userId']] = $wizardId;
            // create log spot for user
            $this->log[$wizard['userId']] = array();
        }
        $monsters = $this->gameExecuter->loadMonsters($this->map->battleMapId);
        if ($monsters) {
            foreach ($monsters as $monsterData) {
                $monsterData['data'] = json_decode($monsterData['data'], true);
                $monsterClass = 'Game\Units\Monsters\\' . ucfirst($monsterData['type']);
                $this->addUnit(new $monsterClass($this, $monsterData), false);
            }
        }
        $this->countUnits = count($this->units);
        $this->map->putUnitsOnTheMap($this->units);
        $this->bootstrapUnitsOnMap($this->units);
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

    protected function addUnit(Unit $unit, $justCreatedUnit = true)
    {
        $unitId = count($this->units);
        $unit->unitId = $unitId;
        $this->units[] = $unit;
        $this->countUnits++;
        if ($justCreatedUnit) {
            $this->map->addUnitToTheMap($unit);
            $this->bootstrapUnitsOnMap([$unit]);
        }
        return $unitId;
    }

    /**
     * @param Unit[] $units
     */
    protected function bootstrapUnitsOnMap($units)
    {
        foreach ($units as $unit) {
            $data = [
                $unit->health,
                $unit->maxHealth,
                $unit->x,
                $unit->y,
                $unit->d
            ];
            if ($unit->isWizard()) {
                /** @var Units\Wizards\Wizard $unit */
                $this->addLog([$unit->userId], $unit->unitId, 'map', [$unit->exportVisibleMap()]);
                $data[] = 'w';
                $data[] = $unit->class;
            } else {
                /** @var Units\Monsters\Monster $unit */
                $data[] = 'm';
                $data[] = $unit->monsterType;
            }
            $this->addLog($this->map->getWatchman($unit->x, $unit->y), $unit->unitId, 'init', $data);
        }
    }

    public function unitAppearsOnScreen(Unit $unit, $userId, $code)
    {
        $data = [
            $unit->health,
            $unit->maxHealth,
            $unit->x,
            $unit->y,
            $unit->d
        ];
        if ($unit->isWizard()) {
            /** @var Units\Wizards\Wizard $unit */
            $data[] = 'w';
            $data[] = $unit->class;
        } else {
            /** @var Units\Monsters\Monster $unit */
            $data[] = 'm';
            $data[] = $unit->monsterType;
        }
        $this->addLog([$userId], $unit->unitId, $code, $data);
    }

    protected function prepareUnitLogData($code, $userId, $performer, &$data)
    {
        if (in_array($code, ['new', 'init', 'inc'])) {
            $watcherUnit = $this->getUnit($this->getWizardIdByUserId($userId));
            $watcherUnit->addVisibleUnit($performer);

//            if ($code == 'new') {
//                // if we here that mean unit we are about to dicover is not visible yet. that why we need to move it`s coordinats a bit.
//                list($data[2], $data[3]) = $this->map->getNextCoords($data[2], $data[3], $watcherUnit->d, false);
//            }

            // we doing this coz we need coordinates to be relative
            list($data[2], $data[3]) = $watcherUnit->getRelativeCoordinates($data[2], $data[3]);
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
     * @param $userId
     *
     * @return int
     */
    public function getWizardIdByUserId($userId)
    {
        return $this->usersWizards[$userId];
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param array $userIds
     * @param       $performer
     * @param       $code
     * @param array $data
     */
    public function addLog(array $userIds, $performer, $code, $data = array())
    {
        foreach ($userIds as $userId)
        {
            $this->prepareUnitLogData($code, $userId, $performer, $data);

            if (!isset($this->log[$userId][$this->getTime()])) {
                $this->log[$userId][$this->getTime()] = [];
            }
            // who | what | when | with what
            $this->log[$userId][$this->getTime()][] = array($performer, $code, $data);
        }
    }

    protected function saveUnits()
    {
        foreach ($this->units as $unit) {
            if ($unit->isWizard()) {
                $this->gameExecuter->saveWizard($unit->prepareToSave());
            } else if ($unit->isMonster()) {
                /** @var Monsters\Monster $unit */
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
        $this->gameExecuter->saveLog($this->log, $this->map->battleMapId, $this->getTime());
        echo 'hello world!';
    }

    public function spawnMonster($x, $y)
    {
        $type = 'wolf';
        $monsterClass = 'Game\Units\Monsters\\' . ucfirst($type);
        $data = [
            'type' => $type,
            'x'    => $x,
            'y'    => $y,
        ];
        /** @var Monsters\Monster $monster */
        $this->addUnit(new $monsterClass($this, $data));
    }

    /**
     * This method is triggered by Map when new cell is comes to user
     * @param $x
     * @param $y
     */
    public function newCellIsVisible($x, $y)
    {
        // here we should set logic to decide spawn monster or not.
        if (mt_rand(1, 20) == 1) {
            $this->spawnMonster($x, $y);
        }
    }
}