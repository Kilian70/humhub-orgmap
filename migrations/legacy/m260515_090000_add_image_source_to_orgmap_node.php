<?php

use yii\db\Migration;

class m260515_090000_add_image_source_to_orgmap_node extends Migration
{

    public function safeUp()
    {

        $this->addColumn(
            'orgmap_node',
            'image_source',
            $this->string(20)->defaultValue('space')
        );
    }

    public function safeDown()
    {

        $this->dropColumn(
            'orgmap_node',
            'image_source'
        );
    }
}