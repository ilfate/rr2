<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */

namespace Game\Map;

use Game\Math;

class Generator {

    const DEFAULT_MULTIPLY_COEFFICIENT = 2;
    /**
     * @var MapObject
     */
    protected $map;



    public function __construct(MapObject $map)
    {
        $this->map = $map;
    }

    /**
     * Full map will be generated
     */
    public function generateFullMap(\Chunk $chunkModel)
    {
        for($x = 1; $x <= $this->map->mapWidth; $x++) {
            for($y = 1; $y <= $this->map->mapHeight; $y++) {
                $chunkModel->addChunk($this->getChunk($x, $y), $this->map->battleMapId);
            }
        }
    }

    protected function getChunk($x, $y)
    {
        $chunk = ['x' => $x, 'y' => $y];
        $neibours = $this->map->getChunksNeiboursIfExists($x, $y);
        $list = [];
        foreach ($neibours as $yList) {
            foreach ($yList as $neibour) {
                $list[] = $neibour;
            }
        }
        $chunk['biom'] = $this->getBiomByNeibours($list);
        $chunk['cells'] = $this->generateCells($chunk);
        $chunk['id'] = Geometry::getIdByCoords($chunk['x'], $chunk['y']);
        return $chunk;
    }

    /**
     * Generate random biom_id with information about neibours
     *
     * @param $neibours
     *
     * @return bool|int|string
     */
    protected function getBiomByNeibours($neibours)
    {
        /** @var array $chanses custom chances by neibours */
        $chanses = [];
        foreach ($neibours as $neibour) {
            if ($neibour && isset($this->map->allowedBioms[$neibour['biom']])) {
                if (!isset($chanses[$neibour['biom']])) {
                    $chanses[$neibour['biom']] = 0;
                }
                $chanses[$neibour['biom']] += 10;
            }
        }
        if(count($chanses) > 1) {
            $newBiom = Math::customChance($chanses);
        }
        if(!isset($newBiom) || $newBiom === false) {

            $newBiom = Math::customChance($this->map->allowedBioms);
        }
        return $newBiom;
    }

    protected function generateCells(&$chunk)
    {
        $addX = ($chunk['x'] - 1) * $this->map->chunkSize;
        $addY = ($chunk['y'] - 1) * $this->map->chunkSize;
        for ($i = 1; $i <= $this->map->chunkSize; $i++) {
            for ($i2 = 1; $i2 <= $this->map->chunkSize; $i2++) {
                $x = $i2 + $addX;
                $y = $i  + $addY;
                $cell = $this->randomCell($x, $y, $chunk['biom']);
                //$this->map->addCell($x, $y, $cell);
                $chunk['cellsParsed'][$x][$y] = $cell;
            }
        }
        // recheck cells
        for ($i = 1; $i <= $this->map->chunkSize; $i++) {
            for ($i2 = 1; $i2 <= $this->map->chunkSize; $i2++) {
                $x = $i2 + $addX;
                $y = $i  + $addY;

                $list = Geometry::getDirectNeiboursArray($x, $y);

                $neighboursCounted = [];
                $neighbours = $this->map->getCellsIfExists($list);
                foreach ($neighbours as $yList) {
                    foreach ($yList as $neighbour) {
                        if ($neighbour != '00') {
                            if (!isset($neighboursCounted[$neighbour])) {
                                $neighboursCounted[$neighbour] = 0;
                            }
                            $neighboursCounted[$neighbour]++;
                        }
                    }
                }
                arsort($neighboursCounted);
                if(reset($neighboursCounted) >= 3) {
                    $chunk['cellsParsed'][$x][$y] = key($neighboursCounted);
                }
                $this->map->addCell($x, $y, $chunk['cellsParsed'][$x][$y]);
            }
        }
        return $this->map->implodeChunk($chunk['x'], $chunk['y']);
    }

    protected function randomCell($x, $y, $biom)
    {
        $neibours = $this->map->geCellsNeighboursIfExists($x, $y);
        $chanses = $this->getBiomChances($biom);
        $neighbourCells = [];

        foreach ($neibours as $yList) {
            foreach ($yList as $neighbour) {
                if ($neighbour !== '00') {
                    if (!isset($neighbourCells[$neighbour])) {
                        $neighbourCells[$neighbour] = 0;
                    }
                    $neighbourCells[$neighbour]++;
                }
            }
        }
        $conf = $this->getBiomConfig($biom, 'cell_multiply_coefficients');
        foreach ($neighbourCells as $cell => $value) {

            $k = isset($conf[$cell]) ? $conf[$cell] : self::DEFAULT_MULTIPLY_COEFFICIENT;
            $chanses[$cell] += pow($value, $k);
        }
        return Math::customChance($chanses);
    }

    protected function getBiomChances($biom)
    {
        $default = \Config::get('maps.defaultBiom');
        $ret = $this->getBiomConfig($default, 'cells_chances');
        if ($biom == $default) {
            return $ret;
        }
        $conf = $this->getBiomConfig($biom, 'cells_chances', true);
        if ($conf) {
            foreach ($conf as $key => $val) {
                $ret[$key] = $val;
            }
        }
        return $ret;
    }

    protected function getBiomConfig($biom, $type, $exact = false)
    {
        $conf = \Config::get('maps');
        $default = $conf['defaultBiom'];
        if (isset($conf['bioms'][$biom]) && isset($conf['bioms'][$biom][$type])) {
            return $conf['bioms'][$biom][$type];
        } elseif($exact) {
            return false;
        }
        return $conf['bioms'][$default][$type];
    }
} 