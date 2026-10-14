<?php

use yii\db\Migration;

class m260814_154500_default_node_opacity_to_100 extends Migration
{
	public function safeUp()
	{
		$this->update(
			'orgmap_node',
			['opacity' => 100],
			['opacity' => null]
		);

		$this->alterColumn(
			'orgmap_node',
			'opacity',
			$this->integer()->defaultValue(100)
		);
	}

	public function safeDown()
	{
		$this->alterColumn(
			'orgmap_node',
			'opacity',
			$this->integer()->defaultValue(45)
		);
	}
}
