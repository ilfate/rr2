<?php

class Map extends Eloquent {
	protected $guarded = array();

	public static $rules = array();

    public function getBioms()
    {
        if (!$this->bioms) {
            return array();
        }
        return explode('|', $this->bioms);
    }
}
