<?php


return array(

    'types' => array(
        'fire',
        'life',
        'might'
    ),

    'buyPrice' => array(
        // currency, price
        'fire'  => array(1, 1000),
        'life'  => array(1, 1000),
        'might' => array(1, 1000),
    ),



    'stats' => [
        'sta' => ['stamina'],
        'wis' => ['wisdom'],
        'int' => ['intellect'],
        'spi' => ['spirit'],
    ],

    'wizards' => array(
        'fire'  => array(
            'defaultHealth' => 50,
            'statsDefault' => [
                'sta' => 5,
                'wis' => 5,
                'int' => 6,
                'spi' => 5,
            ]

        ),
        'life'  => array(
            'defaultHealth' => 50,
            'statsDefault' => [
                'sta' => 5,
                'wis' => 6,
                'int' => 5,
                'spi' => 5,
            ]
        ),
        'might' => array(
            'defaultHealth' => 50,
            'statsDefault' => [
                'sta' => 6,
                'wis' => 5,
                'int' => 5,
                'spi' => 5,
            ]
        ),
    ),
    'actions' => [
        'mf' => [
            'className' => 'MoveForward'
        ],
        'rl' => [
            'className' => 'RotateLeft'
        ]
    ],
    'logic' => [
        'wm' => 'Might',
        '_m' => 'Monster',
        'pm' => 'PassiveMonster',
    ]
);