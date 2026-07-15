<?php

use yii\db\Migration;

class m260514_080000_add_organ_id_to_node extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            'orgmap_node',
            'organ_id',
            $this->integer()->null()
        );
    }

    public function safeDown()
    {
        $this->dropColumn(
            'orgmap_node',
            'organ_id'
        );
    }
}