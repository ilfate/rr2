<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */

namespace Game\Map;

use Game\Geometry\Geometry;
use Game\Units\Unit;
use Game\Units\Wizards\Wizard;

/**
 * This class represents an instance of global Map
 */
class MapObject
{
    /** watch radius in cells */
    const WATCH_RADIUS = 3;

    protected $chunks;
    protected $needGenerationChunks = array();
    protected $cells;

    protected $mapData;
    public $mapWidth;
    public $mapHeight;
    public $chunkSize;
    public $allowedBioms;
    public $battleMapId;

    protected $lastMap;
    /**
     * @var \Chunk
     */
    protected $chunkModel;

    public $allowToLoadFromDb = true;

    /**
     * @var array An array of all objects that are exist on the map. Indexed by cells
     */
    public $units = array();
    public $watchman = array();

    /**
     * @param array $mapData
     * @param null  $chunkModel
     */
    public function __construct($mapData, $chunkModel = null)
    {
        $this->mapData     = $mapData;
        $this->mapWidth    = $mapData['width'];
        $this->mapHeight   = $mapData['height'];
        $this->chunkSize   = $mapData['chunk_size'];
        $this->battleMapId = $mapData['battle_map_id'];

        $allowedBiomsNames = explode('|', $mapData['bioms']);
        $biomChances       = \Config::get('maps.biomsChances');
        if ($mapData['bioms']) {
            // If we have allowed bioms we want to keep only biom_id
            $biomTypes = \Config::get('maps.biomTypes');
            foreach ($allowedBiomsNames as $biomName) {
                $biom_id                      = array_search($biomName, $biomTypes);
                $this->allowedBioms[$biom_id] = $biomChances[$biom_id];
            }
        } else { // all bioms are allowed
            $this->allowedBioms = $biomChances;
        }

        $this->chunkModel = $chunkModel;
    }

    public function setChunks($chunks)
    {
        foreach ($chunks as $chunk) {
            $this->chunks[$chunk['x']][$chunk['y']] = $chunk;
            $this->explodeChunk($chunk, true);
        }
    }

    public function getSpawnPoint()
    {
        return [1, 1, 0];
    }

    /**
     * Save all objects on map
     *
     * @param Unit[] $units
     */
    public function putUnitsOnTheMap($units)
    {
        foreach ($units as $unit) {
            $this->addUnitToTheMap($unit);
        }

    }

    public function addUnitToTheMap(Unit $unit, $createWatchData = null)
    {
        if (!isset($this->units[$unit->x][$unit->y])) {
            $this->units[$unit->x][$unit->y] = array();
        }
        $this->units[$unit->x][$unit->y][] = $unit->unitId;
        if ($unit->type == 'wizard') {
            /** @var $unit Wizard */
            if ($createWatchData) {
                list($createStartX, $createStartY, $createEndX, $createEndY) = $createWatchData;
            } else {
                $createStartX = $unit->x - self::WATCH_RADIUS;
                $createStartY = $unit->x - self::WATCH_RADIUS;
                $createEndX   = $unit->y + self::WATCH_RADIUS;
                $createEndY   = $unit->y + self::WATCH_RADIUS;
            }
            // here we will put userId into all cells that user can see.
            for ($currentX = $createStartX; $currentX <= $createEndX; $currentX++) {
                for ($currentY = $createStartY; $currentY <= $createEndY; $currentY++) {
                    $point = [$currentX, $currentY];
                    $point = Geometry::prepareCellCoordinats($point[0], $point[1], $this->mapWidth, $this->mapHeight, $this->chunkSize);
                    if (!isset($this->watchman[$point[0]][$point[1]])) {
                        $this->watchman[$point[0]][$point[1]] = array();
                    }
                    $this->watchman[$point[0]][$point[1]][$unit->userId] = $unit->userId;
                }
            }
        }
    }

    public function moveUnit($oldX, $oldY, Unit $unit)
    {
        $objects = $this->getUnits($oldX, $oldY);
        if (($key = array_search($unit->unitId, $objects)) !== false) {
            unset($this->units[$oldX][$oldY][$key]);
            if ($unit->type == 'wizard') {
                /** @var $unit Wizard */
                if ($oldX == $unit->x) {
                    list($delStartY, $delEndY, $createStartY, $createEndY) = Geometry::calculateWatchmanChange($oldY, $unit->y);
                    $delStartX = $createStartX = $oldX - self::WATCH_RADIUS;
                    $delEndX   = $createEndX = $oldX + self::WATCH_RADIUS;
                } else if ($oldY == $unit->y) {
                    list($delStartX, $delEndX, $createStartX, $createEndX) = Geometry::calculateWatchmanChange($oldX, $unit->x);
                    $delStartY = $createStartY = $oldY - self::WATCH_RADIUS;
                    $delEndY   = $createEndY = $oldY + self::WATCH_RADIUS;
                } else {
                    $delStartX    = $oldX - self::WATCH_RADIUS;
                    $delStartY    = $oldY - self::WATCH_RADIUS;
                    $delEndX      = $oldX + self::WATCH_RADIUS;
                    $delEndY      = $oldY + self::WATCH_RADIUS;
                    $createStartX = $unit->x - self::WATCH_RADIUS;
                    $createStartY = $unit->y - self::WATCH_RADIUS;
                    $createEndX   = $unit->x + self::WATCH_RADIUS;
                    $createEndY   = $unit->y + self::WATCH_RADIUS;
                }
                for ($currentX = $delStartX; $currentX <= $delEndX; $currentX++) {
                    for ($currentY = $delStartY; $currentY <= $delEndY; $currentY++) {
                        $point = [$currentX, $currentY];
                        $point = Geometry::prepareCellCoordinats($point[0], $point[1], $this->mapWidth, $this->mapHeight, $this->chunkSize);
                        unset($this->watchman[$point[0]][$point[1]][$unit->userId]);
                    }
                }
                $createData = [$createStartX, $createStartY, $createEndX, $createEndY];

            }
        } else {
            throw new \Exception('Map is trying to move object that is no longer there');
        }
        $this->addUnitToTheMap($unit, isset($createData) ? $createData : null);
    }

    /**
     * Get units from one cell
     * @param $x
     * @param $y
     *
     * @return array
     */
    public function getUnits($x, $y)
    {
        if (isset($this->units[$x][$y])) {
            return $this->units[$x][$y];
        }
        return array();
    }

    /** get users that are watching this cell */
    public function getWatchman($x, $y)
    {
        if (isset($this->watchman[$x][$y])) {
            return $this->watchman[$x][$y];
        }
        return array();
    }

    public function getUnitsInRadius($x, $y, $radius, $returnType = 'list')
    {
        $startX      = $x - $radius;
        $startY      = $y - $radius;
        $endX = $x + $radius;
        $endY = $y + $radius;
        $returnUnits = array();
        for ($currentX = $startX; $currentX <= $endX; $currentX++) {
            for ($currentY = $startY; $currentY <= $endY; $currentY++) {
                $point = [$currentX, $currentY];
                $point = Geometry::prepareCellCoordinats($point[0], $point[1], $this->mapWidth, $this->mapHeight, $this->chunkSize);
                if (isset($this->units[$point[0]][$point[1]])) {
                    switch ($returnType) {
                        case 'list':
                            $returnUnits = array_merge($returnUnits, $this->units[$point[0]][$point[1]]);
                            break;
                        case 'map':
                            $returnUnits[] = $this->units[$point[0]][$point[1]];
                            break;
                    }
                }
            }
        }
    }

    /**
     * Returns all downloaded cells on the screen by the screen center
     *
     * @param $x
     * @param $y
     *
     * @return mixed
     */
    public function getMap($x, $y)
    {
        if (!empty($this->lastMap[$x . '_' . $y])) {
            return $this->lastMap[$x . '_' . $y];
        }
        $list                         = $this->getScreenCells($x, $y);
        $this->lastMap[$x . '_' . $y] = $this->getCells($list);
        return $this->lastMap[$x . '_' . $y];
    }

    public function getScreenChunks($x, $y)
    {
        $ids         = [];
        $chunksPairs = $this->getChunksList($this->getScreenCells($x, $y));
        if ($chunksPairs) {
            foreach ($chunksPairs as $pair) {
                $ids[] = $this->getIdByCoords($pair[0], $pair[1]);
            }
        }
        return $ids;
    }

    public function getNextCoords($x, $y, $d)
    {
        switch ($d) {
            case 0 :
                $next = [$x, $y + 1];
                break;
            case 1 :
                $next = [$x + 1, $y];
                break;
            case 2 :
                $next = [$x, $y - 1];
                break;
            case 3 :
                $next = [$x - 1, $y];
                break;
        }
        Geometry::prepareCellCoordinats($next[0], $next[1], $this->mapWidth, $this->mapHeight, $this->chunkSize);
        return $next;
    }

    public function getCell($x, $y)
    {
        return $this->getCells([[$x, $y]])[$x][$y];
    }

    public function getCells($list = array())
    {
        $return = [];
        $cells  = $this->getCellsIfExists($list, true);
        if ($this->checkAndGenerate()) {
            $cells = $this->getCellsIfExists($list);
        }
        return $cells;
    }

    public function getChunks($list = array())
    {
        $chunks = $this->getChunksIfExists($list, true);
        if ($this->checkAndGenerate()) {
            $chunks = $this->getChunksIfExists($list);
        }
        return $chunks;
    }

    protected function checkAndGenerate()
    {
        if (!$this->needGenerationChunks) {
            return false;
        }
        foreach ($this->needGenerationChunks as $pair) {
            $this->chunks[$pair[0]][$pair[1]] = $this->getGenerator()->getChunk($pair[0], $pair[1]);
        }
        $this->needGenerationChunks = [];
        return true;
    }

    /**
     * Returns chunks near the target chunk if they are exists
     *
     * @param $x
     * @param $y
     *
     * @return array
     */
    public function getChunksNeiboursIfExists($x, $y)
    {
        return $this->getChunksIfExists(Geometry::getNeiboursArray($x, $y));
    }

    public function getChunksIfExists($list, $isMarkToGenerate = false)
    {
        $return  = [];
        $forLoad = array();
        foreach ($list as $pair) {
            Geometry::prepareChunkCoordinats($pair[0], $pair[1], $this->mapWidth, $this->mapHeight);
            if (isset($this->chunks[$pair[0]]) && isset($this->chunks[$pair[0]][$pair[1]])) {
                $return[$pair[0]][$pair[1]] = $this->chunks[$pair[0]][$pair[1]];
            } else {
                if (!in_array($pair, $forLoad)) {
                    $forLoad[] = $pair;
                }
            }
        }
        if ($this->allowToLoadFromDb && $forLoad) {
            $this->loadChunks($forLoad, $isMarkToGenerate);
            foreach ($forLoad as $pair) {
                if (isset($this->chunks[$pair[0]]) && isset($this->chunks[$pair[0]][$pair[1]])) {
                    $return[$pair[0]][$pair[1]] = $this->chunks[$pair[0]][$pair[1]];
                } else {
                    $return[$pair[0]][$pair[1]] = null;
                }
            }
        } else if ($forLoad) {
            $this->setUpEmptyChunks($forLoad);
        }
        return $return;
    }

    public function addCell($x, $y, $cell)
    {
        if ($this->cells[$x][$y] === '00') {
            $this->cells[$x][$y] = $cell;
        } else {
            Logger::dump('here somehow we trying to create existing wrong cell');
        }
    }

    /**
     * Returns cells near neibours if they are exists
     *
     * @param $x
     * @param $y
     *
     * @return array
     */
    public function geCellsNeighboursIfExists($x, $y)
    {
        $list = Geometry::getNeiboursArray($x, $y);
        return $this->getCellsIfExists($list);
    }

    /**
     * @param      $list array
     * @param bool $isMarkToGenerate
     *
     * @return array
     */
    public function getCellsIfExists($list, $isMarkToGenerate = false)
    {
        $return  = [];
        $forLoad = array();
        foreach ($list as &$pair) {
            Geometry::prepareCellCoordinats($pair[0], $pair[1], $this->mapWidth, $this->mapHeight, $this->chunkSize);
            $chunkPair = Geometry::cellToChunk($pair[0], $pair[1], $this->chunkSize);

            if (!isset($this->cells[$pair[0]]) || !isset($this->cells[$pair[0]][$pair[1]])) {
                if (!in_array($chunkPair, $forLoad)) {
                    $forLoad[] = $chunkPair;
                }
            }
        }

        if ($this->allowToLoadFromDb && $forLoad) {
            $this->loadChunks($forLoad, $isMarkToGenerate);
        } else if ($forLoad) {
            $this->setUpEmptyChunks($forLoad);
        }
        //Logger::dump($list);

        foreach ($list as $pair2) {

            if (isset($this->cells[$pair2[0]]) && isset($this->cells[$pair2[0]][$pair2[1]])) {
                $return[$pair2[0]][$pair2[1]] = $this->cells[$pair2[0]][$pair2[1]];
            } else {
                $return[$pair2[0]][$pair2[1]] = '00';
            }
        }
        return $return;
    }

    public function loadChunks($list, $isMarkToGenerate = false)
    {
        $ids = [];
        foreach ($list as $pair) {
            $ids[] = Geometry::getIdByCoords($pair[0], $pair[1]);
        }
        sort($ids);
        $result = $this->chunkModel->getChunksById($ids);
        if ($result) {
            $this->setChunks($result);
        }
        $this->setUpEmptyChunks($list);
    }

    protected function setUpEmptyChunks($list)
    {
        foreach ($list as $pair) {
            if (!isset($this->chunks[$pair[0]]) || !isset($this->chunks[$pair[0]][$pair[1]])) {
                $this->chunks[$pair[0]][$pair[1]] = array();
                $x                                = ($pair[0] - 1) * $this->chunkSize;
                $y                                = ($pair[1] - 1) * $this->chunkSize;
                for ($i = 1; $i <= $this->chunkSize; $i++) {
                    for ($i2 = 1; $i2 <= $this->chunkSize; $i2++) {
                        $this->cells[$x + $i2][$y + $i] = '00';
                    }
                }
            }
        }
    }

    /**
     * @param      $chunk
     * @param bool $save
     *
     * @return array
     */
    public function explodeChunk($chunk, $save = true)
    {
        $return = [];
        $cells  = str_split($chunk['cells'], 2);
        $x      = ($chunk['x'] - 1) * $this->chunkSize;
        $y      = ($chunk['y'] - 1) * $this->chunkSize;
        for ($i = 1; $i <= $this->chunkSize; $i++) {
            for ($i2 = 1; $i2 <= $this->chunkSize; $i2++) {
                $return[$x + $i2][$y + $i] = array_shift($cells);
                if ($save) {
                    $this->cells[$x + $i2][$y + $i]                                           = $return[$x + $i2][$y + $i];
                    $this->chunks[$chunk['x']][$chunk['y']]['cellsParsed'][$x + $i2][$y + $i] = $return[$x + $i2][$y + $i];
                }
            }
        }
        return $return;
    }

    /**
     * @param $x
     * @param $y
     *
     * @return string
     */
    public function implodeChunk($x, $y)
    {
        $string = '';
        $addX   = ($x - 1) * $this->chunkSize;
        $addY   = ($y - 1) * $this->chunkSize;
        for ($i = 1; $i <= $this->chunkSize; $i++) {
            for ($i2 = 1; $i2 <= $this->chunkSize; $i2++) {
                $string .= $this->cells[$addX + $i2][$addY + $i];
            }
        }
        return $string;
    }

    /**
     * @return Game_MapGeneratorInterface
     */
    protected function getGenerator()
    {
        if (empty($this->generator)) {
            $config = $this->getConfig();
            if (!isset($config['generator'][$this->planet])) {
                $generator = $config['generator'][self::DEFAULT_PLANET];
            } else {
                $generator = $config['generator'][$this->planet];
            }
            $this->generator = new $generator($this);
        }
        return $this->generator;
    }
}