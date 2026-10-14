<?php

use yii\db\Migration;

class m260602_231000_add_font_weight_to_connection
	extends Migration
{

	public function safeUp()
	{
		$this->addColumn(
			'orgmap_connection',
			'font_weight',
			$this->string(50)
				->defaultValue('normal')
		);
	}

	public function safeDown()
	{
		$this->dropColumn(
			'orgmap_connection',
			'font_weight'
		);
	}
}