<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */

namespace Game\Units\Wizards;

use Game\Geometry\Point;
use Game\Units\Unit;
use Game\Units\Logic;

abstract class Wizard extends Unit
{
    public $wizardId;
    public $battleWizardId;
    public $playerId;
    public $class;
    public $userId;
    public $type = 'wizard';

    public function __construct($game, $wizardData)
    {
        $this->game = $game;
        $data = $wizardData['data'];
        if (empty($data['x']) || empty($data['y'])) {
            list($this->x, $this->y) = $this->game->map->getSpawnPoint();
        } else {
            $this->x = $data['x'];
            $this->y = $data['y'];
        }

        $this->ownerType      = 'player';
        $this->wizardId       = $wizardData['id'];
        $this->battleWizardId = $wizardData['battleWizardId'];
        $this->level          = $wizardData['level'];
        $this->class          = $wizardData['class'];
        $this->userId         = $wizardData['userId'];


        if (isset($data['logic'])) {
            $logicClassName = ucfirst($data['logic']) . 'Logic';
        } else {
            $logicClassName = ucfirst($this->class) . 'Logic';
        }
        $logicClassName = 'Game\Units\Logic\\' . $logicClassName;
        $this->logic = new $logicClassName();
    }
}
