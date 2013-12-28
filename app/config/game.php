<?php

return [

    'log' => [
        'codes' => [
            'map', // the full map that we export at start of generation
            'c',   // a single cell that is exported when unit moves and user see new cells [$dx, $dy, $cell]
            'init', // bootstraping new unit in vision area
            'new', // vision area is moved and now we can see new unit
            'inc', // new unit is coming to our vision area!
            // [health, maxHealth, x, y, d, type('m' or 'w'), type(class or monster type)]
            'mf',  // unit moves forward
            'vm',  // vision area is moved in data -> [Amount_of_moved_cells, diration]
        ]
    ]
];