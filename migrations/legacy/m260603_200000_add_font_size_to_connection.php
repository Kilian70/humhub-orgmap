<?php

use yii\db\Migration;

class m260603_200000_add_font_size_to_connection
	extends Migration
{

	public function safeUp()
	{
		$this->addColumn(
			'orgmap_connection',
			'font_size',
			$this->integer()->defaultValue(12)
		);
	}

	public function safeDown()
	{
		$this->dropColumn(
			'orgmap_connection',
			'font_size'
		);
	}
}