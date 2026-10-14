<?php

use yii\db\Migration;

class m260530_210000_add_content_to_orgmap_node extends Migration
{
	public function safeUp()
	{
		$this->addColumn(
			'orgmap_node',
			'content',
			$this->text()->null()
		);
	}

	public function safeDown()
	{
		$this->dropColumn(
			'orgmap_node',
			'content'
		);
	}
}

