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
            $config       = \Config::get('wizards.wizards.' . $wizardData['class']);
            $this->health = $config['defaultHealth'] + ($wizardData['sta'] * $config['statsEffect']['sta']);
            $logic        = ucfirst($this->class);
        } else {
            $this->x      = $data['x'];
            $this->y      = $data['y'];
            $this->d      = $data['d'];
            $this->health = $data['h'];

            // set Action that is active right now
            $actionConf              = \Config::get('wizards.actions.' . $data['a'][0]);
            $actionClass             = 'Game\Units\Actions\\' . $actionConf['className'];
            $this->action            = new $actionClass($this, $game);
            $this->action->startTime = $data['a'][1];
            $logic                   = $data['l'];
        }

        $logicClassName = $logic;
        $this->logic    = $logicClassName;
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
            'l' => $this->logic
        );
        $return['data'] = json_encode($data);
        return $return;
    }
}
