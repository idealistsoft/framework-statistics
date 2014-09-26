<?php

/* This configuration is used to run the tests */

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
      'SessionAdapter',
      'SiteStatus',
      'FrameworkVersion',
      'TotalUsers'
    ],
    'dashboard' => [
      'Signup Funnel' => [
        'SignupsToday'
      ],
      'Usage' => [
        'TotalUsers'
      ],
      'Site' => [
        'SiteStatus',
        'FrameworkVersion',
        'PhpVersion',
        'SiteMode',
        'SessionAdapter',
      ],
      'Database' => [
        'DatabaseSize',
        'DatabaseVersion',
        'DatabaseTables'
      ]
    ]
  ]
];
