<?php

class BattleMap extends Eloquent {
	protected $guarded = array();

	public static $rules = array();

    public function getBattleMapData($battleMapId)
    {
        $maps = $this->where('battle_maps.id', '=', $battleMapId)->join('maps', 'battle_maps.map_id', '=', 'maps.id')->select(
            'battle_maps.id as battle_map_id',
            'maps.width as width',
            'maps.height as height',
            'maps.chunk_size as chunk_size',
            'maps.bioms as bioms'
        )->get();
        return $maps->toArray()[0];
    }

    public function getBattleMapWithWizards()
    {
        return $this
            ->where('battle_maps.active', '=', 1)
            ->join('battle_wizards', 'battle_maps.id', '=', 'battle_wizards.battle_map_id')
            ->join('maps', 'battle_maps.map_id', '=', 'maps.id')
            ->select(
            'battle_maps.id as battle_map_id',
            'battle_wizards.wizard_id as wizard_id',
            'battle_wizards.id as battle_wizard_id',
            'battle_wizards.data as battle_wizard_data',
            'maps.width as width',
            'maps.height as height',
            'maps.chunk_size as chunk_size'
            )
            ->get()
            ->toArray();
    }
}
