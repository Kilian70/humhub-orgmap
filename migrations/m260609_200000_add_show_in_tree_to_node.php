<?php

use yii\db\Migration;

class m260609_200000_add_show_in_tree_to_node
	extends Migration
{

	public function safeUp()
	{

		$this->addColumn(

			'orgmap_node',

			'show_in_tree',

			$this->boolean()
				->notNull()
				->defaultValue(1)
		);
	}

	public function safeDown()
	{

		$this->dropColumn(

			'orgmap_node',

			'show_in_tree'
		);
	}
}