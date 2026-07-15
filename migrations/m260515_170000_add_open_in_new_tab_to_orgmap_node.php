<?php

use yii\db\Migration;

class m260515_170000_add_open_in_new_tab_to_orgmap_node extends Migration
{

    public function safeUp()
    {

        $this->addColumn(
            'orgmap_node',
            'open_in_new_tab',
            $this->boolean()
                ->notNull()
                ->defaultValue(0)
        );
    }

    public function safeDown()
    {

        $this->dropColumn(
            'orgmap_node',
            'open_in_new_tab'
        );
    }
}