<?php

use yii\db\Migration;

class m260530_150000_add_show_parent_line_to_orgmap_node extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            '{{%orgmap_node}}',
            'show_parent_line',
            $this->boolean()->notNull()->defaultValue(1)
        );
    }

    public function safeDown()
    {
        $this->dropColumn(
            '{{%orgmap_node}}',
            'show_parent_line'
        );
    }
}