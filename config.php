<?php

use humhub\widgets\TopMenu;
use humhub\modules\orgmap\Module;
use humhub\modules\orgmap\Events;

return [
    'id' => 'orgmap',
    'class' => Module::class,
    'namespace' => 'humhub\modules\orgmap',

    'consoleControllerMap' => [
        'orgmap-migration' => \humhub\modules\orgmap\commands\MigrationController::class,
    ],

    'events' => [
        [
            'class' => \humhub\commands\MigrateController::class,
            'event' => \yii\base\Controller::EVENT_BEFORE_ACTION,
            'callback' => [Events::class, 'onBeforeMigration'],
        ],
        [
            'class' => TopMenu::class,
            'event' => TopMenu::EVENT_INIT,
            'callback' => [Events::class, 'onTopMenuInit']
        ],
    ],
];
