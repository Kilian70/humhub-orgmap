<?php

use yii\db\Migration;

class m260601_010000_add_asset_id_to_orgmap_node
	extends Migration
{

	public function safeUp()
	{

		$this->addColumn(
			'orgmap_node',
			'asset_id',
			$this->integer()->null()
		);
	}

	public function safeDown()
	{

		$this->dropColumn(
			'orgmap_node',
			'asset_id'
		);
	}
}