<?php

namespace humhub\modules\orgmap\widgets;

use yii\base\Widget;

class IconPickerWidget extends Widget
{
	public $model;

	public $attribute = 'icon_class';

	public function run()
	{
		return $this->render(
			'icon-picker',
			[
				'model' => $this->model,
				'attribute' => $this->attribute,
			]
		);
	}
}