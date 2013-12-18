<?php


namespace Game\Units\Actions;
   
/**
 *
 * PHP version 5
 *
 * @category 
 * @package  
 * @author    Ilya Rubinchik <ilfate@gmail.com>
 */
class RotateLeft extends Action{
    public $code = 'rl';
    public $duration = 600;

    public function onStart()
    {
        $this->d--;
        if ($this->d < 0) {
            $this->d = 3;
        }
    }
} 