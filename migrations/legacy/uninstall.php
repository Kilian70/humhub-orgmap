<?php

use yii\db\Migration;

/**
 * Removes all database objects owned by the OrgMap module.
 */
class uninstall extends Migration
{
	public function up()
	{
		// Drop dependent tables before the tables they reference.
		$this->dropTable('orgmap_connection');
		$this->dropTable('orgmap_node');
		$this->dropTable('orgmap_organ');
		$this->dropTable('orgmap_asset');
	}

	public function down()
	{
		echo "OrgMap uninstall cannot be reverted.\n";

		return false;
	}
}
