<?php


return array(

    'types' => array(
        'normal', 'training', 'deathmatch', 'dungeon'
    ),

    'spawns' => array(
        'random', 'specific', 'radius'
    ),

    'biomTypes' => array(
        '',
        'forest',
        'desert',
        'lake',
        'plain',
        'mountains'
    ),
    'biomsChances' => [
        1 => 15,
        2 => 8,
        3 => 5,
    ],
    'defaultBiom' => 1,
    'bioms' => [
        1 => [ // common grass biom
            'cells_chances' => [
                'a1' => 40, // grass
                'a2' => 12, // dirt
                'a3' => 3,  //rocks
                'a4' => 3,  //hole
                'a5' => 2,  //pile,
                'a6' => 10,  //trees,
                'a7' => 2,  //puddle,
                'w1' => 3,  //water,
                's1' => 0,  //sand,
                't1' => 0.4, //resourse breaching
            ],
            'cell_multiply_coefficients' => [
                'a1' => 4,
                'a2' => 5,
                'a9' => 4,
                '10' => 4,
            ]
        ],
        2 => [ // water biom
            'cells_chances' => [
                'a1' => 0, // grass
                'a2' => 0, // dirt
                'a3' => 0,  //rocks
                'a4' => 0,  //hole
                'a5' => 0,  //pile,
                'a6' => 0,  //trees,
                'a7' => 0,  //puddle,
                'w1' => 5, // water
                's1' => 0, //sand
                't1' => 0, //resourse breaching
            ],
            'cell_multiply_coefficients' => [
                'w1' => 3,
                's1' => 5,
                'a1' => 4,
            ]
        ],
        3 => [ // rocks
            'cells_chances' => [
                'a1' => 25, // grass
                'a2' => 15, // dirt
                'a3' => 55,  //rocks
                'a4' => 10,  //hole
                'a5' => 2,  //pile,
                'a6' => 2,  //trees,
                'a7' => 2,  //puddle,
                'w1' => 0,  //water,
                's1' => 0,  //sand,
                't1' => 0.4, //resourse breaching
            ],
            'cell_multiply_coefficients' => [
                'a1' => 3,
                'a3' => 5,
                'a2' => 4,
            ]
        ],
    ],
);