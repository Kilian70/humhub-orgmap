<?php

use yii\db\Migration;

class m260628_000001_add_background_size_to_orgmap_node extends Migration
{
	public function safeUp()
	{
		$this->addColumn(
			'{{%orgmap_node}}',
			'background_size',
			$this->string(20)
				->notNull()
				->defaultValue('cover')
				->after('display_mode')
		);
	}

	public function safeDown()
	{
		$this->dropColumn(
			'{{%orgmap_node}}',
			'background_size'
		);
	}
}