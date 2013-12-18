<?php

class Monster extends Eloquent {
	protected $guarded = array();

	public static $rules = array();

    public function saveMonster($monster)
    {
        $sql = "UPDATE monsters SET `data` = '" . $monster['data'] . "', health = " . $monster['health'] . " WHERE id = " . $monster['id'] . " AND battle_map_id = " . $monster['battle_map_id'];
        DB::statement($sql);
        //$this->query(DB::raw($sql))->update();
//        $this->query()->where('id', '=', $monster['id'])->where('battle_map_id', '=', $monster['battle_map_id'])->update($monster);
    }

}
