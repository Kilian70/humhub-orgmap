<?php

use yii\db\Migration;

class m260526_000002_add_width_height_to_orgmap_node
	extends Migration
{

	public function safeUp()
	{
		$this->addColumn(
			'orgmap_node',
			'width',
			$this->integer()->null()
		);

		$this->addColumn(
			'orgmap_node',
			'height',
			$this->integer()->null()
		);
	}

	public function safeDown()
	{
		$this->dropColumn(
			'orgmap_node',
			'width'
		);

		$this->dropColumn(
			'orgmap_node',
			'height'
		);
	}
}