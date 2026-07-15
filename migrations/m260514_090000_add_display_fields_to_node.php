<?php

use yii\db\Migration;

class m260514_090000_add_display_fields_to_node extends Migration
{

    public function safeUp()
    {

        $this->addColumn(
            'orgmap_node',
            'display_mode',
            $this->string(50)->defaultValue('color')
        );

        $this->addColumn(
            'orgmap_node',
            'custom_image',
            $this->string()->null()
        );

        $this->addColumn(
            'orgmap_node',
            'opacity',
            $this->integer()->defaultValue(45)
        );
    }

    public function safeDown()
    {

        $this->dropColumn(
            'orgmap_node',
            'display_mode'
        );

        $this->dropColumn(
            'orgmap_node',
            'custom_image'
        );

        $this->dropColumn(
            'orgmap_node',
            'opacity'
        );
    }
}