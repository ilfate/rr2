<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2012
 */
/**
 * Created by PhpStorm.
 * User: ilfate
 * Date: 12/11/13
 * Time: 10:48 PM
 */

namespace Game;

class GameExecuter {

    /** @var \BattleWizard */
    protected $battleWizardModel;
    /** @var \Wizard */
    protected $wizardModel;
    /** @var \BattleMap */
    protected $battleMapModel;

    public function __construct()
    {
        $this->battleWizardModel = new \BattleWizard();
        $this->wizardModel       = new \Wizard();
        $this->battleMapModel    = new \BattleMap();
    }

    public function run()
    {
        $battleMaps = $this->battleMapModel->getBattleMapWithWizards();
    }
}