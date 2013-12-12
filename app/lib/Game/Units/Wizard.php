<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */

namespace Game\Units;

class Wizard extends Unit
{
    public $wizardId;
    public $battleWizardId;
    public $playerId;
    public $class;


    public function __construct($wizardData)
    {
        $this->ownerType      = 'player';
        $this->wizardId       = $wizardData['id'];
        $this->battleWizardId = $wizardData['battleWizardId'];
        $this->level          = $wizardData['level'];
        $this->class          = $wizardData['class'];
    }
} 