<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */


namespace Game\Units\Logic;


use Game\Game;
use Game\Units\Actions\Action;
use Game\Units\Actions\MoveForward;
use Game\Units\Unit;

class ExploringLogic extends WizardsLogic {

    /**
     * @param Unit $unit
     * @param Game $game
     *
     * @internal param \Game\Map\MapObject $map
     * @return Action
     */
    public static function decision(Unit $unit, Game $game)
    {
        return new MoveForward($unit, $game);
    }
}