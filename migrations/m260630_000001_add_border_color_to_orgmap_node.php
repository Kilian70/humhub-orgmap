<?php

use yii\db\Migration;

class m260630_000001_add_border_color_to_orgmap_node extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            '{{%orgmap_node}}',
            'border_color',
            $this->string(20)
                ->notNull()
                ->defaultValue('#2196f3')
                ->after('border_width')
        );
    }

    public function safeDown()
    {
        $this->dropColumn(
            '{{%orgmap_node}}',
            'border_color'
        );
    }
}