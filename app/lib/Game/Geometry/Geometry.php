<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */
namespace Game\Geometry;

class Geometry
{
    const CHUNK_SIZE = 4;

    const DIRECTION_TOP_RIGHT     = 1;
    const DIRECTION_BOTTOM_RIGHT  = 2;
    const DIRECTION_BOTTOM_LEFT   = 3;
    const DIRECTION_TOP_LEFT      = 4;

    protected $config;

    protected $planet;

    public function getConfig()
    {
        if (empty($this->config)) {
            $this->config = Service::getConfig()->get('all', 'map');
        }
        return $this->config;
    }

    public function getScreenCells($x, $y)
    {
        $size = $this->getConfig()['view']['screen_size'];
        $startX = $x - $size; $endX = $x + $size;
        $startY = $y - $size; $endY = $y + $size;
        $list = [];
        for ($iy = $startY; $iy <= $endY; $iy++) {
            for ($ix = $startX; $ix <= $endX; $ix++) {
                $list[] = [$ix, $iy];
            }
        }
        return $list;
    }

    public function getChunksList($cellsList)
    {
        $chunkList = [];
        foreach ($cellsList as &$pair) {
            $this->prepareCellCoordinats($pair[0], $pair[1]);
            $chunkPair = $this->cellToChunk($pair[0], $pair[1]);
            if (!in_array($chunkPair, $chunkList)) {
                $chunkList[] = $chunkPair;
            }
        }
        return $chunkList;
    }

    /**
     * Returns ID by chunk coords
     *
     * @param $x
     * @param $y
     * @return number
     */
    public static function getIdByCoords($x, $y)
    {
        if ($x > $y) {
            return self::centerIdFormula($x) + $x - $y;
        }
        return self::centerIdFormula($y) + $x - $y;
    }

    private static function centerIdFormula($n)
    {
        return pow($n, 2) - abs($n) + 1;
    }

    public static function getNeiboursArray($x, $y)
    {
        return [
            [$x+1, $y+1],
            [$x+1, $y  ],
            [$x+1, $y-1],
            [$x,   $y-1],
            [$x-1, $y-1],
            [$x-1, $y  ],
            [$x-1, $y+1],
            [$x,   $y+1],
        ];
    }
    public static function getDirectNeiboursArray($x, $y)
    {
        return [
            [$x+1, $y  ],
            [$x,   $y-1],
            [$x-1, $y  ],
            [$x,   $y+1],
        ];
    }

    /**
     * @param $x
     * @param $y
     * @return array
     */
    public static function cellToChunk($x, $y, $chunkSize)
    {
        $chunkX = ceil($x / $chunkSize);
        $chunkY = ceil($y / $chunkSize);
        return [$chunkX, $chunkY];
    }


    public static function prepareChunkCoordinats(&$x, &$y, $mapWidth, $mapHeight)
    {
        if ($x <= 0) {
            $x = $mapWidth + $x;
        } elseif ($x > $mapWidth) {
            $x = $x % $mapWidth;
        }
        if ($y <= 0) {
            $y = $mapHeight + $y;
        } elseif ($y > $mapHeight) {
            $y = $y % $mapHeight;
        }
        return [$x, $y];
    }

    public static function prepareCellCoordinats(&$x, &$y, $mapWidth, $mapHeight, $chunkSize)
    {
        $cellsX = $mapWidth * $chunkSize;
        $cellsY = $mapHeight * $chunkSize;
        if ($x <= 0) {
            $x = $cellsX + $x;
        } elseif ($x > $cellsX) {
            $x = $x % $cellsX;
        }
        if ($y <= 0) {
            $y = $cellsY + $y;
        } elseif ($y > $cellsY) {
            $y = $y % $cellsY;
        }
        return [$x, $y];
    }
}