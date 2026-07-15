<?php

use yii\db\Migration;

class m260526_000001_add_shape_to_orgmap_node
	extends Migration
{

	public function safeUp()
	{
		$this->addColumn(
			'orgmap_node',
			'shape',
			$this->string(255)
				->defaultValue('circle')
		);
	}

	public function safeDown()
	{
		$this->dropColumn(
			'orgmap_node',
			'shape'
		);
	}
}