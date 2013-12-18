<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */

namespace Game\Units\Wizards;

use Game\Game;
use Game\Map\MapObject;
use Game\Units\Actions\Action;
use Game\Units\Unit;

class Might extends Wizard
{
    const STATS_STA_EFFECT = 10;
    public $logicCode = 'wm';
}