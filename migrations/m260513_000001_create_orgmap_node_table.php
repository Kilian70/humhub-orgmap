<?php

use yii\db\Migration;

class m260513_000001_create_orgmap_node_table extends Migration
{

    public function safeUp()
    {

        $this->createTable('orgmap_node', [

            'id' => $this->primaryKey(),

            'parent_id' => $this->integer()->null(),

            'title' => $this->string()->notNull(),

            'type' => $this->string(50)->defaultValue('space'),

            'space_id' => $this->integer()->null(),

            'url' => $this->string()->null(),

            'color' => $this->string(20)->null(),

            'pos_x' => $this->integer()->defaultValue(0),

            'pos_y' => $this->integer()->defaultValue(0),

            'radius' => $this->integer()->defaultValue(80),

            'is_external' => $this->boolean()->defaultValue(false),

            'visible' => $this->boolean()->defaultValue(true),

            'sort_order' => $this->integer()->defaultValue(0),

        ]);
    }

    public function safeDown()
    {
        $this->dropTable('orgmap_node');
    }
}