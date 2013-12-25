<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */

namespace Game\Units\Wizards;

use Game\Game;
use Game\Geometry\Point;
use Game\Units\Unit;
use Game\Units\Logic;

abstract class Wizard extends Unit
{
    const STATS_STA_EFFECT = 5;
    const STATS_INT_EFFECT = 5;
    const STATS_WIZ_EFFECT = 5;
    const STATS_SPI_EFFECT = 5;

    public $wizardId;
    public $battleWizardId;
    public $playerId;
    public $class;
    public $userId;
    public $type = 'wizard';

    public $vision = array();
    public $visionIsExported = false;

    public function __construct(Game $game, $wizardData)
    {
        $this->game           = $game;
        $this->ownerType      = 'player';
        $this->wizardId       = $wizardData['id'];
        $this->battleWizardId = $wizardData['battleWizardId'];
        $this->level          = $wizardData['level'];
        $this->class          = $wizardData['class'];
        $this->userId         = $wizardData['userId'];
        $data                 = $wizardData['data'];
        if (empty($data['x'])) {
            // it means we do not have any saved data at all
            // we will populate default data
            list($this->x, $this->y, $this->d) = $this->game->map->getSpawnPoint();
            $this->maxHealth = $this->health = $wizardData['sta'] * static::STATS_STA_EFFECT;
        } else {
            $this->x      = $data['x'];
            $this->y      = $data['y'];
            $this->d      = $data['d'];
            $this->health = $data['h'];
            $this->logicCode         = $data['l'];
            $this->loadAction($data['a'][0], $data['a'][1]);
            $this->maxHealth = $wizardData['sta'] * static::STATS_STA_EFFECT;
        }

        $this->logic = \Config::get('wizards.logic.' . $this->logicCode);
    }

    public function prepareToSave()
    {
        $return         = array(
            'id' => $this->battleWizardId
        );
        $data           = array(
            'x' => $this->x,
            'y' => $this->y,
            'd' => $this->d,
            'a' => [$this->action->code, $this->action->startTime],
            'h' => $this->health,
            'l' => $this->logicCode
        );
        $return['data'] = json_encode($data);
        return $return;
    }

    public function putVisibleCell($x, $y, $cell)
    {
        $dx = $x - $this->x;
        $dy = $y - $this->y;
        $this->vision[$dx][$dy] = $cell;
        if ($this->visionIsExported) {
            // this is new cell and we need to log it
            $this->game->addLog([$this->userId], $this->unitId, 'c', [$dx, $dy, $cell]);
        }
    }

    public function exportVisibleMap()
    {
        $visibleArea = [];
        foreach ($this->vision as $x => $column) {
            foreach ($column as $y => $cell) {
                 $visibleArea[] = $cell;
            }
        }
        $this->visionIsExported = true;
        return implode('|', $visibleArea);
    }


}
