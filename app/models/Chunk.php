<?php

class Chunk extends Eloquent {
	protected $guarded = array();

	public static $rules = array();

    /**
     * Create new chunk by the data passed from generator
     *
     * @param array   $chunk
     * @param integer $battleMapId
     */
    public function addChunk($chunk, $battleMapId)
    {
        $chunk['battle_map_id'] = $battleMapId;
        $chunk['biom_id'] = $chunk['biom'];
        unset($chunk['cellsParsed']);
        unset($chunk['biom']);
        $this->insert($chunk);
    }

    public function getChunksById($ids)
    {
        return $this->where('id', 'IN', $ids)->get();
    }

}
