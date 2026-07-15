<?php

use yii\db\Migration;

class m260626_000001_add_subtitle_to_orgmap_node extends Migration
{
	public function safeUp()
	{
		$this->addColumn(
			'orgmap_node',
			'subtitle',
			$this->string()->null()
		);
	}

	public function safeDown()
	{
		$this->dropColumn(
			'orgmap_node',
			'subtitle'
		);
	}
}