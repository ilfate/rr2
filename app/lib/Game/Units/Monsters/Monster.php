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

    public function __construct($data)
    {
        $this->level = $data['level'];
    }
} 