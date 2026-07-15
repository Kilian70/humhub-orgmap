<?php

use yii\db\Migration;

class m260527_220000_add_is_background_to_orgmap_node extends Migration
{

    public function safeUp()
    {

        $this->addColumn(
            'orgmap_node',
            'is_background',
            $this->boolean()->defaultValue(0)
        );
    }

    public function safeDown()
    {

        $this->dropColumn(
            'orgmap_node',
            'is_background'
        );
    }
}