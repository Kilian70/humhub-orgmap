<?php

use yii\db\Migration;

class m260515_110000_add_label_background_to_orgmap_node extends Migration
{

    public function safeUp()
    {

        $this->addColumn(
            'orgmap_node',
            'label_background',
            $this->boolean()
                ->notNull()
                ->defaultValue(1)
        );
    }

    public function safeDown()
    {

        $this->dropColumn(
            'orgmap_node',
            'label_background'
        );
    }
}