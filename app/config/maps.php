<?php


return array(

    'types' => array(
        'normal', 'training', 'deathmatch', 'dungeon'
    ),

    'spawns' => array(
        'random', 'specific', 'radius'
    ),

    'biomTypes' => array(
        0 => '',
        1 => 'forest',
        2 => 'desert',
        3 => 'lake',
        4 => 'plain',
        5 => 'mountains'
    ),
    'biomsChances' => [
        1 => 15,
        2 => 8,
        3 => 5,
    ],
    'defaultBiom' => 1,
    'bioms' => [
        1 => [ // common forest
            'cells_chances' => [
                'a1' => 40, // grass
                'a2' => 12, // dirt
                'a3' => 3,  //rocks
                'a4' => 3,  //hole
                'a5' => 2,  //pile,
                'a6' => 16,  //trees,
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
        2 => [ // desert
            'cells_chances' => [
                'a1' => 2, // grass
                'a2' => 3, // dirt
                'a3' => 15,  //rocks
                'a4' => 4,  //hole
                'a5' => 2,  //pile,
                'a6' => 2,  //trees,
                'a7' => 2,  //puddle,
                'w1' => 0,  //water,
                's1' => 25,  //sand,
                't1' => 0.4, //resourse breaching
            ],
            'cell_multiply_coefficients' => [
                'a1' => 3,
                'a3' => 5,
                'a2' => 4,
            ]
        ],
        3 => [ // water biom
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
    ],
);