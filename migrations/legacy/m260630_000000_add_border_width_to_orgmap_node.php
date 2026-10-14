<?php

use yii\db\Migration;

class m260630_000000_add_border_width_to_orgmap_node extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            '{{%orgmap_node}}',
            'border_width',
            $this->integer()
                ->notNull()
                ->defaultValue(1)
                ->after('color')
        );
    }

    public function safeDown()
    {
        $this->dropColumn(
            '{{%orgmap_node}}',
            'border_width'
        );
    }
}