<?php

use yii\db\Migration;

class m260513_210000_add_font_size_to_orgmap_node extends Migration
{
    public function safeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema('orgmap_node');

        if (!$table->getColumn('font_size')) {

            $this->addColumn(
                'orgmap_node',
                'font_size',
                $this->integer()->null()
            );
        }
    }

    public function safeDown()
    {
        echo "m260513_210000_add_font_size_to_orgmap_node cannot be reverted.\n";

        return false;
    }
}