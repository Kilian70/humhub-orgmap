<?php

use yii\db\Migration;

class m260602_230000_add_label_rotation_to_connection
	extends Migration
{

	public function safeUp()
	{
		$this->addColumn(
			'orgmap_connection',
			'label_rotation',
			$this->string(50)
				->defaultValue('auto')
		);
	}

	public function safeDown()
	{
		$this->dropColumn(
			'orgmap_connection',
			'label_rotation'
		);
	}
}