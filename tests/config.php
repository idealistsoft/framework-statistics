<?php

return  [
  'site' => [
    'salt' => 'replacewithrandomstring',
  ],
  'modules' => [
    'middleware' => [
      'auth'
    ],
    'all' => [
    	'auth',
      'statistics'
    ]
  ],
  'database' => [
    'type' => 'mysql',
    'user' => 'travis',
    'password' => '',
    'host' => '127.0.0.1',
    'name' => 'mydb',
  ],
  'sessions' => [
    'enabled' => true,
    'adapter' => 'database',
    'lifetime' => 86400
  ],
  'statistics' => [
    'metrics' => [
      'DatabaseSize',
      'DatabaseTables',
      'DatabaseVersion',
      'PhpVersion',
      'SignupsToday',
      'SiteMode',
      'SiteSession',
      'SiteStatus',
      'SiteVersion',
      'Users'
    ],
    'dashboard' => [
      'Signup Funnel' => [
        'SignupsToday'
      ],
      'Usage' => [
        'Users'
      ],
      'Site' => [
        'SiteStatus',
        'SiteVersion',
        'PhpVersion',
        'SiteMode',
        'SiteSession',
      ],
      'Database' => [
        'DatabaseSize',
        'DatabaseVersion',
        'DatabaseTables'
      ]
    ]
  ]
];