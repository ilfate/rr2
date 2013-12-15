<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */


namespace Game\Units\Actions;


class MoveForward extends Action{
    public $code = 'mf';
    public $duration = 800;

    public function onEnd()
    {
        switch($this->unit->d)
        {
            case 0:
                $this->unit->point->y--;
                break;
            case 1:
                $this->unit->point->x++;
                break;
            case 2:
                $this->unit->point->y++;
                break;
            case 3:
                $this->unit->point->x--;
                break;
        }
    }
} 