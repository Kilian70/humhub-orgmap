<?php

use yii\db\Migration;

class m260527_235000_add_color_to_orgmap_organ extends Migration
{

	public function safeUp()
	{

		$this->addColumn(
			'orgmap_organ',
			'color',
			$this->string(255)->null()
		);
	}

	public function safeDown()
	{

		$this->dropColumn(
			'orgmap_organ',
			'color'
		);
	}
}