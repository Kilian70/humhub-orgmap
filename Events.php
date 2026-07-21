<?php

namespace humhub\modules\orgmap;

use humhub\modules\ui\menu\MenuLink;
use Yii;

class Events
{

    public static function onTopMenuInit($event)
    {
    
			$allowGuestAccess =
			Yii::$app
				->getModule('orgmap')
				->settings
				->get(
					'allowGuestAccess',
					false
				);
		
		if (
			Yii::$app->user->isGuest
			&& !$allowGuestAccess
		) {
		
			return;
		}
		
        $event->sender->addEntry(new MenuLink([

            'id' => 'topmenu-orgmap',

            'label' => Yii::$app
				->getModule('orgmap')
				->settings
				->get(
					'moduleTitle',
					'ORG.'
				),

            'url' => ['/orgmap/map/index'],

            'icon' => 'fa-circle-o',

            'sortOrder' => Yii::$app
                ->getModule('orgmap')
                ->settings
                ->get(
                    'topMenuSortOrder',
                    250
                ),

            'isActive' => Yii::$app->controller?->module?->id === 'orgmap',
        ]));
    }
}
