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
use Game\Units\Wizards\Wizard;
use Game\Units\Monsters;
use Game\Units\Unit;

class Game
{

    /** This value is border between loading all chunks if there less cells than in this value
     * or load only needed chunks if there is more cells than this value */
    const MAX_CELLS_IN_MAP_TO_LOAD_ALL_CHUNKS = 10000;

    /** time for step in milli seconds */
    const TIME_STEP = 200;

    /** @var GameExecuter */
    protected $gameExecuter;

    /** @var MapObject */
    protected $map;

    /** @var integer current game time */
    protected $time;

    /** @var Unit[] */
    protected $objects;
    protected $countObjects;

    /**
     * @param $data
     * @param $gameExecuter
     */
    public function __construct($data, $gameExecuter)
    {
        $this->time = $data['time'];
        $this->gameExecuter = $gameExecuter;
        foreach ($data['wizards'] as $wizard) {
            $wizardClass = ucfirst($wizard['class']);
            $this->objects[] = new $wizardClass($wizard);
            $wizardId = count($this->objects) - 1;
            $this->objects[$wizardId]->objectId = $wizardId;
        }
        unset($data['wizards']);
        $this->countObjects = count($this->objects);
        $this->map = new MapObject($data);

        // here we decide should we load all chunks for map or we will load only necessary ones
        if ($this->map->mapWidth * $this->map->mapHeight * $this->map->chunkSize
            <= self::MAX_CELLS_IN_MAP_TO_LOAD_ALL_CHUNKS
        ) {
            $this->map->setChunks($this->gameExecuter->loadAllChunks($this->map->battleMapId));
        }
    }

    /**
     * Main run method. One run = one tick
     */
    public function run()
    {
        for($i = 0; $i < $this->countObjects; $i++) {
            $this->objects[$i]->action($this->map, $this);
        }

        $this->time += self::TIME_STEP;
    }

    /**
     * @param $id
     *
     * @return Unit
     */
    public function getUnit($id)
    {
        return $this->objects[$id];
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }
}