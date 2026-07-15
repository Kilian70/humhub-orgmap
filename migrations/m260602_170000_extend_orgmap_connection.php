<?php

use yii\db\Migration;

class m260602_170000_extend_orgmap_connection
	extends Migration
{

	public function safeUp()
	{
		$this->addColumn(
			'orgmap_connection',
			'label',
			$this->string(50)->null()
		);

		$this->addColumn(
			'orgmap_connection',
			'arrow',
			$this->string(50)->null()
		);

		$this->addColumn(
			'orgmap_connection',
			'curve',
			$this->integer()->defaultValue(0)
		);
	}

	public function safeDown()
	{
		$this->dropColumn(
			'orgmap_connection',
			'curve'
		);

		$this->dropColumn(
			'orgmap_connection',
			'arrow'
		);

		$this->dropColumn(
			'orgmap_connection',
			'label'
		);
	}
}