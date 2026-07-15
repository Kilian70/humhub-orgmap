<?php

use yii\db\Migration;

class m260602_220000_add_connection_label_offsets
	extends Migration
{

	public function safeUp()
	{
		$this->addColumn(
			'orgmap_connection',
			'label_offset_x',
			$this->integer()->defaultValue(0)
		);

		$this->addColumn(
			'orgmap_connection',
			'label_offset_y',
			$this->integer()->defaultValue(0)
		);
	}

	public function safeDown()
	{
		$this->dropColumn(
			'orgmap_connection',
			'label_offset_y'
		);

		$this->dropColumn(
			'orgmap_connection',
			'label_offset_x'
		);
	}
}