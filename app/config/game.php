<?php

return [

    'log' => [
        'codes' => [
            'map', // the full map that we export at start of generation
            'c',   // a single cell that is exported when unit moves and user see new cells
            'new', // display new unit on map that is visible for user
            // [health, maxHealth, x, y, d, type('m' or 'w'), type(class or monster type)]
            'mf',  // unit moves forward
            'vm',  // vision area is moved in data -> [Amount_of_moved_cells, diration]
        ]
    ]
];