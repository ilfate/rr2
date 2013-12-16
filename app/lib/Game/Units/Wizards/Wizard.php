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
        if (empty($data['x'])) {
            // it means we do not have any saved data at all
            // we will populate default data
            list($this->x, $this->y, $this->d) = $this->game->map->getSpawnPoint();
            $config = \Config::get('wizards.' . $wizardData['class']);
            $this->health = $config['defaultHealth'] + ($data['sta'] * $config['statsEffect']['sta']);

        } else {
            $this->x = $data['x'];
            $this->y = $data['y'];
            $this->d = $data['d'];
            $this->health = $data['h'];

            // set Action that is active right now
            $actionConf = \Config::get('wizards.actions.' . $data['a'][0]);
            $actionClass = 'Game\Units\Actions\\' . $actionConf['className'];
            $this->action = new $actionClass($this, $game);
            $this->action->startTime = $data['a'][1];
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

    public function prepareWizardsToSave()
    {
        $return = array(
            'id'    => $this->battleWizardId,
            'level' => $this->level,
        );
        $data = array(
            'x' => $this->x,
            'y' => $this->y,
            'd' => $this->d,
            'a' => [$this->action->code, $this->action->startTime],
            'h' => $this->health
        );
        $return['data'] = json_encode($data);
        return $return;
    }
}
