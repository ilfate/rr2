<?php
/**
 * ILFATE PHP ENGINE
 *
 * @autor Ilya Rubinchik ilfate@gmail.com
 *        2013
 */

namespace Game\Units\Monsters;

use Game\Game;
use Game\Units\Unit;

abstract class Monster extends Unit
{
    public $type = 'monster';
    public $monsterType;
    public $isSaved = false;
    public $stats = array();
    public $logicCode = '_m';

    public function __construct(Game $game, $data = array())
    {
        $this->game      = $game;
        $this->maxHealth = $this->stats['sta'] * 5;
        if (isset($data['health'])) {
            $this->isSaved     = true;
            $this->level       = $data['level'];
            $this->monsterType = $data['type'];
            $this->health      = $data['health'];
            $this->x           = $data['data']['x'];
            $this->y           = $data['data']['y'];
            $this->d           = $data['data']['d'];
            $this->logicCode   = $data['data']['l'];
            $this->loadAction($data['data']['a'][0], $data['data']['a'][1]);
        } else {
            // let`s create new monster
            $this->monsterType = $data['type'];
            $this->x           = $data['x'];
            $this->y           = $data['y'];
            $this->d           = mt_rand(0, 3);
            $monsterConfig     = \Config::get('monsters.monsters.' . $this->monsterType);
            $this->stats       = $monsterConfig['stats'];
            $this->health      = $this->maxHealth;
        }
        $this->logic = \Config::get('wizards.logic.' . $this->logicCode);

    }

    public function prepareToSave()
    {
        $data = [
            'x' => $this->x,
            'y' => $this->y,
            'd' => $this->d,
            'l' => $this->logicCode,
            'a' => [$this->action->code, $this->action->startTime],
        ];
        return array(
            'id'            => $this->unitId,
            'battle_map_id' => $this->game->map->battleMapId,
            'health'        => $this->health,
            'level'         => $this->level,
            'type'          => $this->monsterType,
            'data'          => json_encode($data)
        );
    }
} 