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
    /** @var \Monster */
    protected $monsterModel;
    /** @var \BattleLog */
    protected $battleLogModel;

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
        $this->monsterModel      = new \Monster();
        $this->battleLogModel    = new \BattleLog();
    }

    public function run()
    {
        $this->executed++;

        $battleMapsWithWizards = $this->battleMapModel->getBattleMapWithWizards();
        if (!$battleMapsWithWizards) {
            $this->reRun();
            return;
        }

        foreach ($battleMapsWithWizards as $battleMapData)
        {
            try {
                $game = new Game($battleMapData, $this);
                $game->run();
            } catch(\Exception $e) {
                echo 'we have an exception here!!';
            }
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
        return $this->chunkModel->where('battle_map_id', '=', $battleMapId)->get()->toArray();
    }

    public function loadMonsters($battleMapId)
    {
        return $this->monsterModel->where('battle_map_id', '=', $battleMapId)->get()->toArray();
    }

    public function saveWizard($wizard)
    {
        $this->battleWizardModel->where('id', '=', $wizard['id'])->update($wizard);
    }

    public function saveMap($map)
    {
        $this->battleMapModel->where('id', '=', $map['id'])->update($map);
    }

    public function saveMonster($monster)
    {
        $this->monsterModel->saveMonster($monster);
    }

    public function newMonster($monster)
    {
        $this->monsterModel->insert($monster);
    }

    public function saveLog($log, $battleMapId, $time)
    {
        foreach ($log as $userId => $log) {
            $data = [
                'user_id' => $userId,
                'battle_map_id' => $battleMapId,
                'time' => $time,
                'data' => json_encode($log)
            ];
            $this->battleLogModel->create($data);
        }
    }
}