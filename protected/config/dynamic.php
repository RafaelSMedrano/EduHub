<?php return array (
  'name' => 'EduHub',
  'language' => 'pt-BR',
  'components' => 
  array (
    'user' => 
    array (
    ),
    'mailer' => 
    array (
      'transport' => 
      array (
        'dsn' => 'native://default',
      ),
    ),
    'cache' => 
    array (
      'class' => 'yii\\caching\\FileCache',
      'keyPrefix' => 'humhub',
    ),
    'formatter' => 
    array (
      'defaultTimeZone' => 'America/Sao_Paulo',
    ),
  ),
  'params' => 
  array (
    'config_created_at' => 1704652052,
    'horImageScrollOnMobile' => 1,
    'databaseInstalled' => true,
    'installed' => true,
  ),
  'timeZone' => 'America/Sao_Paulo',
); ?>