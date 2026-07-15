<?php

use yii\db\Migration;

class m260515_120000_add_show_label_to_orgmap_node extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            'orgmap_node',
            'show_label',
            $this->boolean()->defaultValue(1)
        );
    }

    public function safeDown()
    {
        $this->dropColumn(
            'orgmap_node',
            'show_label'
        );
    }
}