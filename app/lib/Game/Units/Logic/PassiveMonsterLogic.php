<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */


namespace Game\Units\Logic;

use Game\Game;
use Game\Units\Actions\Action;
use Game\Units\Unit;

class PassiveMonsterLogic extends MonsterLogic
{
    /**
     * @param Unit $unit
     * @param Game $game
     *
     * @internal param \Game\Map\MapObject $map
     * @return Action
     */
    public static function decision(Unit $unit, Game $game)
    {
        return ExploringLogic::decision($unit, $game);
    }
} 