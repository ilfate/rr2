<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */


namespace Game\Units;


abstract class Event {

    const BEFORE_ACTION = 'ba';
    const AFTER_ACTION  = 'aa';
    const ON_TICK       = 'ot';

    public $code;
    public $type;

    abstract public function trigger(Unit $unit, $eventNumber);

} 