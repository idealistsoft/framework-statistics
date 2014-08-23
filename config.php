<?php

// WARNING this config file is only used for the tests

return  [
  'site' => [
    'salt' => 'replacewithrandomstring',
  ],
  'modules' => [
    'middleware' => [
      'auth'
    ]
  ],
  'database' => [
    'type' => 'mysql',
    'user' => 'root',
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