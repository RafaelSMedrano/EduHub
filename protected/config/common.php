<?php
/**
 * This file provides to overwrite the default HumHub / Yii configuration by your local common (Console and Web) environments
 * @see http://www.yiiframework.com/doc-2.0/guide-concept-configurations.html
 * @see http://docs.humhub.org/admin-installation-configuration.html
 * @see http://docs.humhub.org/dev-environment.html
 */
return [
	'components' => [
        	'db' => [
        	    		'class' => 'yii\db\Connection', // A classe que gerencia a conexão com o banco de dados
            			'dsn' => 'mysql:host=localhost;dbname=eduhub_db',
            			'username' => 'eduhub',
            			'password' => 'edub@@',
            			'charset' => 'utf8',
        		],
			'urlManager' => [
					'showScriptName' => false,
					'enablePrettyUrl' => true,
				],
			],
];
