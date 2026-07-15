<?php

use yii\db\Migration;

class m260601_000000_create_orgmap_asset_table
	extends Migration
{

	public function safeUp()
	{

		$this->createTable(
			'orgmap_asset',
			[

				'id' => $this->primaryKey(),

				'title' => $this->string()
					->notNull(),

				'filename' => $this->string()
					->notNull(),

				'type' => $this->string(50)
					->defaultValue('background'),

				'created_at' => $this->integer(),

				'created_by' => $this->integer(),
			]
		);
	}

	public function safeDown()
	{

		$this->dropTable('orgmap_asset');
	}
}