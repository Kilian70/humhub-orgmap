<?php

use yii\db\Migration;

class m260614_000000_add_type_to_orgmap_connection
	extends Migration
{

	public function safeUp()
	{
		$this->addColumn(
			'orgmap_connection',
			'type',
			$this->string(50)->null()
		);
	}

	public function safeDown()
	{
		$this->dropColumn(
			'orgmap_connection',
			'type'
		);
	}
}