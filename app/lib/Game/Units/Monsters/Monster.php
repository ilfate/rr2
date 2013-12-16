<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */

namespace Game\Units\Monsters;

use Game\Units\Unit;

abstract class Monster extends Unit
{
    public $type = 'monster';
    public $monsterType;
    public $isSaved = false;

    public function __construct($data = array())
    {
        if ($data) {
            $this->isSaved = true;
            $this->level = $data['level'];
            $this->monsterType = $data['type'];
        }

    }

    public function prepareToSave()
    {
        $data = [];
        return array(
            'id' => $this->unitId,
            'battle_map_id' => $this->game->map->battleMapId,
            'health' => $this->health,
            'level' => $this->level,
            'type' => $this->monsterType,
            'data' => json_encode($data)
        );
    }
} 