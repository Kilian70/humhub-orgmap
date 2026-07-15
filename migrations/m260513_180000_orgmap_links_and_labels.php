<?php

use yii\db\Migration;

class m260513_180000_orgmap_links_and_labels extends Migration
{

    public function safeUp()
    {

        $table = $this->db
            ->schema
            ->getTableSchema('orgmap_node');


        if ($table->getColumn('label_x') === null) {

            $this->addColumn(
                'orgmap_node',
                'label_x',
                $this->integer()->null()
            );
        }


        if ($table->getColumn('label_y') === null) {

            $this->addColumn(
                'orgmap_node',
                'label_y',
                $this->integer()->null()
            );
        }


        if ($table->getColumn('link_type') === null) {

            $this->addColumn(
                'orgmap_node',
                'link_type',
                $this->string(50)->null()
            );
        }


        /*
        url existiert eventuell bereits
        */

        if ($table->getColumn('url') === null) {

            $this->addColumn(
                'orgmap_node',
                'url',
                $this->string()->null()
            );
        }
    }


    public function safeDown()
    {

        $table = $this->db
            ->schema
            ->getTableSchema('orgmap_node');


        if ($table->getColumn('label_x') !== null) {

            $this->dropColumn(
                'orgmap_node',
                'label_x'
            );
        }


        if ($table->getColumn('label_y') !== null) {

            $this->dropColumn(
                'orgmap_node',
                'label_y'
            );
        }


        if ($table->getColumn('link_type') !== null) {

            $this->dropColumn(
                'orgmap_node',
                'link_type'
            );
        }
    }
}