<?php

use yii\db\Migration;

class m260514_070000_create_orgmap_organ_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('orgmap_organ', [

            'id' => $this->primaryKey(),

            'name' => $this->string()->notNull(),

            'parent_id' => $this->integer()->null(),

            'sort_order' => $this->integer()->defaultValue(0),

            'created_at' => $this->integer(),
            'created_by' => $this->integer(),

            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),

        ]);

        $this->addForeignKey(
            'fk-orgmap-organ-parent',
            'orgmap_organ',
            'parent_id',
            'orgmap_organ',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-orgmap-organ-parent',
            'orgmap_organ'
        );

        $this->dropTable('orgmap_organ');
    }
}