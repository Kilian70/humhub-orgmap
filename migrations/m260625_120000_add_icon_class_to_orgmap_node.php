<?php

use yii\db\Migration;

class m260625_120000_add_icon_class_to_orgmap_node extends Migration
{
	public function safeUp()
	{
		$this->addColumn(
			'{{%orgmap_node}}',
			'icon_class',
			$this->string(100)->null()->after('title')
		);
	}

	public function safeDown()
	{
		$this->dropColumn(
			'{{%orgmap_node}}',
			'icon_class'
		);
	}
}