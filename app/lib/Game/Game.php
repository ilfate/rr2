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
use Game\Map\Geometry;
use Game\Units\Wizard;

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

    /** @var Wizard[] */
    protected $wizards = array();
    protected $countWizards;

    protected $time;

    protected $objects;

    public function __construct($data, $gameExecuter)
    {
        $this->time = $data['time'];
        $this->gameExecuter = $gameExecuter;
        foreach ($data['wizards'] as $wizard) {
            $this->wizards[] = new Wizard($wizard);
        }
        $this->countWizards = count($data['wizards']);
        unset($data['wizards']);
        $this->map = new MapObject($data);

        // here we decide should we load all chunks for map or we will load only necessary ones
        if ($this->map->mapWidth * $this->map->mapHeight * $this->map->chunkSize
            <= self::MAX_CELLS_IN_MAP_TO_LOAD_ALL_CHUNKS
        ) {
            $this->map->setChunks($this->gameExecuter->loadAllChunks($this->map->battleMapId));
        }
    }

    public function run()
    {
        for($i = 0; $i < $this->countWizards; $i++) {
            $this->wizards[$i]->action($this->map, $this->wizards, $this->objects);
        }


        $this->time += self::MAX_CELLS_IN_MAP_TO_LOAD_ALL_CHUNKS;
    }
}