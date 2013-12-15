<?php

class BattleMap extends Eloquent
{
    protected $guarded = array();

    public static $rules = array();

    public function getBattleMapData($battleMapId)
    {
        $maps = $this->where('battle_maps.id', '=', $battleMapId)->join('maps', 'battle_maps.map_id', '=', 'maps.id')->select(
            'battle_maps.id as battle_map_id',
            'battle_maps.active as active',
            'maps.width as width',
            'maps.height as height',
            'maps.chunk_size as chunk_size',
            'maps.bioms as bioms'
        )->get();
        return $maps->toArray()[0];
    }

    public function getBattleMapWithWizards()
    {
        $data = $this
            ->join('battle_wizards', 'battle_maps.id', '=', 'battle_wizards.battle_map_id')
            ->join('maps', 'battle_maps.map_id', '=', 'maps.id')
            ->join('wizards', 'battle_wizards.wizard_id', '=', 'wizards.id')
            ->where('battle_maps.active', '=', 1)
            ->whereNotNull('battle_wizards.id')
            ->select(
                'battle_maps.id as battle_map_id',
                'battle_maps.time as time',
                'battle_wizards.wizard_id as wizard_id',
                'battle_wizards.id as battle_wizard_id',
                'battle_wizards.data as battle_wizard_data',
                'wizards.level as level',
                'wizards.class as class',
                'wizards.user_id as user_id',
                'maps.width as width',
                'maps.height as height',
                'maps.chunk_size as chunk_size',
                'maps.bioms as bioms'
            )
            ->get()
            ->toArray();
        $maps = array();
        if (!$data) {
            return $maps;
        }
        foreach ($data as $row) {
            if (!isset($maps[$row['battle_map_id']])) {
                $maps[$row['battle_map_id']] = array(
                    'battle_map_id' => $row['battle_map_id'],
                    'width'         => $row['width'],
                    'height'        => $row['height'],
                    'chunk_size'    => $row['chunk_size'],
                    'bioms'         => $row['bioms'],
                    'time'          => $row['time'],
                    'wizards'       => array()
                );
            }
            $maps[$row['battle_map_id']]['wizards'][] = array(
                'id'             => $row['wizard_id'],
                'battleWizardId' => $row['battle_wizard_id'],
                'data'           => $row['battle_wizard_data'],
                'level'          => $row['level'],
                'class'          => $row['class'],
                'userId'         => $row['user_id'],
            );
        }
        return $maps;
    }
}
