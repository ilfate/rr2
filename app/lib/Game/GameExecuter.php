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
    /** @var \Chunk */
    protected $chunkModel;

    public $startTime;

    const MAX_EXECUTIONS_TIMES = 12;
    protected $executed        = 0;

    public function __construct()
    {
        $this->startTime = microtime(true);

        $this->battleWizardModel = new \BattleWizard();
        $this->wizardModel       = new \Wizard();
        $this->battleMapModel    = new \BattleMap();
        $this->chunkModel        = new \Chunk();
    }

    public function run()
    {
        $this->executed++;

        $battleMapsWithWizards = $this->battleMapModel->getBattleMapWithWizards();
        if (!$battleMapsWithWizards) {
            $this->reRun();
            return;
        }
        $battleMapsCount = count($battleMapsWithWizards);
        for ($i = 0; $i < $battleMapsCount; $i++)
        {
            $game = new Game($battleMapsWithWizards[$i], $this);
            $game->run();
        }

        // TODO: log run results here
    }

    public function reRun()
    {
        if ($this->executed >= self::MAX_EXECUTIONS_TIMES)
        {
            // TODO: log cronjob results here
            return;
        }
        //TODO: here we should wait and run one more time
    }

    public function loadAllChunks($battleMapId)
    {
        return $this->chunkModel->where('battle_map_id', '=', $battleMapId)->get();
    }
}