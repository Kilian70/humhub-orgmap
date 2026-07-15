<?php

namespace humhub\modules\orgmap;

use Yii;
use yii\helpers\Url;

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
		
        $event->sender->addItem([

            'label' => Yii::$app
				->getModule('orgmap')
				->settings
				->get(
					'moduleTitle',
					'ORG.'
				),

            'url' => Url::to(['/orgmap/map/index']),

            'icon' => '<i class="fa fa-circle-o"></i>',

            'sortOrder' => Yii::$app
                ->getModule('orgmap')
                ->settings
                ->get(
                    'topMenuSortOrder',
                    250
                ),

            'isActive' => (

                Yii::$app->controller->module

                && Yii::$app->controller->module->id === 'orgmap'
            ),
        ]);
    }
}