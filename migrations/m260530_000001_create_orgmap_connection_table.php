<?php

use yii\db\Migration;

class m260530_000001_create_orgmap_connection_table extends Migration
{
	public function safeUp()
	{
		$this->createTable('orgmap_connection', [

			'id' => $this->primaryKey(),

			'from_node_id' => $this->integer()->notNull(),

			'to_node_id' => $this->integer()->notNull(),

			'color' => $this->string(50)->null(),

			'width' => $this->integer()->defaultValue(2),

			'style' => $this->string(50)->defaultValue('solid'),

			'created_at' => $this->integer(),
			'created_by' => $this->integer(),

			'updated_at' => $this->integer(),
			'updated_by' => $this->integer(),
		]);

		$this->createIndex(
			'idx-orgmap_connection-from_node_id',
			'orgmap_connection',
			'from_node_id'
		);

		$this->createIndex(
			'idx-orgmap_connection-to_node_id',
			'orgmap_connection',
			'to_node_id'
		);
	}

	public function safeDown()
	{
		$this->dropTable('orgmap_connection');
	}
}