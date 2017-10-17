Yii 2 Advanced Project Template
===============================

Yii 2 Advanced Project Template is a skeleton [Yii 2](http://www.yiiframework.com/) application best for
developing complex Web applications with multiple tiers.

The template includes three tiers: front end, back end, and console, each of which
is a separate Yii application.

The template is designed to work in a team development environment. It supports
deploying the application in different environments.

Documentation is at [docs/guide/README.md](docs/guide/README.md).

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii2-app-advanced/v/stable.png)](https://packagist.org/packages/yiisoft/yii2-app-advanced)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii2-app-advanced/downloads.png)](https://packagist.org/packages/yiisoft/yii2-app-advanced)
[![Build Status](https://travis-ci.org/yiisoft/yii2-app-advanced.svg?branch=master)](https://travis-ci.org/yiisoft/yii2-app-advanced)

DIRECTORY STRUCTURE
-------------------

```
common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
    tests/               contains tests for common classes    
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
backend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for backend application    
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
frontend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains frontend configurations
    controllers/         contains Web controller classes
    models/              contains frontend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains frontend widgets
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides
```
---��� config rbac---
1. ˹�� common/config/main-local ź��õԴ��� db �ͧ MySQL �͡ ���������� MongoDB �ѧ��� 'mongodb' => [
    			'class' => '\yii\mongodb\Connection',
    			'dsn' => 'mongodb://localhost:27017/db_pm',
    	],
2. ���� "mdmsoft/yii2-admin": "~2.0" ���ǹ�ͧ require ��� composer.json
3. ˹�� common/config/main ��ǹ�ͧ components ���� 'authManager' => [
    		'class' => 'yii\mongodb\rbac\MongoDbManager',
    	],
4. ˹�� backend/config/main ��ǹ�ͧ modules ���� 'admin' => [
			'class' => 'mdm\admin\Module',
			'layout' => 'left-menu',
			'controllerMap' => [
				  'assignment' => [
					  'class' => 'mdm\admin\controllers\AssignmentController',
					  'userClassName' => 'common\models\User'
				  ]
			],
		],
5. ˹�� backend/config/main ��ǹ��鹹͡�ش��͹ params ����		
		'as access' => [
		'class' => 'mdm\admin\components\AccessControl',
		'allowActions' => [
			'site/*',
			// 'admin/*',
			// 'gii/*',
			'some-controller/some-action',
		],
	], 
	
