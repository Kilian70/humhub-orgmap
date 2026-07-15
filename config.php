<?php

use humhub\widgets\TopMenu;
use humhub\modules\orgmap\Module;
use humhub\modules\orgmap\Events;

return [
    'id' => 'orgmap',
    'class' => Module::class,
    'namespace' => 'humhub\modules\orgmap',

    'events' => [
        [
            'class' => TopMenu::class,
            'event' => TopMenu::EVENT_INIT,
            'callback' => [Events::class, 'onTopMenuInit']
        ],
    ],
];