<?php

use yii\db\Migration;

class m260711_150000_harden_orgmap_relations extends Migration
{
    public function safeUp()
    {
        $this->cleanupOrphanedReferences();

        $this->createIndex('idx-orgmap_node-parent_id', 'orgmap_node', 'parent_id');
        $this->createIndex('idx-orgmap_node-organ_id', 'orgmap_node', 'organ_id');
        $this->createIndex('idx-orgmap_node-space_id', 'orgmap_node', 'space_id');
        $this->createIndex('idx-orgmap_node-asset_id', 'orgmap_node', 'asset_id');

        $this->addForeignKey(
            'fk-orgmap_node-parent_id',
            'orgmap_node',
            'parent_id',
            'orgmap_node',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-orgmap_node-organ_id',
            'orgmap_node',
            'organ_id',
            'orgmap_organ',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-orgmap_node-space_id',
            'orgmap_node',
            'space_id',
            'space',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-orgmap_node-asset_id',
            'orgmap_node',
            'asset_id',
            'orgmap_asset',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-orgmap_connection-from_node_id',
            'orgmap_connection',
            'from_node_id',
            'orgmap_node',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-orgmap_connection-to_node_id',
            'orgmap_connection',
            'to_node_id',
            'orgmap_node',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-orgmap_connection-to_node_id', 'orgmap_connection');
        $this->dropForeignKey('fk-orgmap_connection-from_node_id', 'orgmap_connection');
        $this->dropForeignKey('fk-orgmap_node-asset_id', 'orgmap_node');
        $this->dropForeignKey('fk-orgmap_node-space_id', 'orgmap_node');
        $this->dropForeignKey('fk-orgmap_node-organ_id', 'orgmap_node');
        $this->dropForeignKey('fk-orgmap_node-parent_id', 'orgmap_node');

        $this->dropIndex('idx-orgmap_node-asset_id', 'orgmap_node');
        $this->dropIndex('idx-orgmap_node-space_id', 'orgmap_node');
        $this->dropIndex('idx-orgmap_node-organ_id', 'orgmap_node');
        $this->dropIndex('idx-orgmap_node-parent_id', 'orgmap_node');
    }

    private function cleanupOrphanedReferences(): void
    {
        $this->execute(
            'UPDATE {{%orgmap_node}} n '
            . 'LEFT JOIN {{%orgmap_node}} p ON p.id = n.parent_id '
            . 'SET n.parent_id = NULL '
            . 'WHERE n.parent_id IS NOT NULL AND p.id IS NULL'
        );
        $this->execute(
            'UPDATE {{%orgmap_node}} n '
            . 'LEFT JOIN {{%orgmap_organ}} o ON o.id = n.organ_id '
            . 'SET n.organ_id = NULL '
            . 'WHERE n.organ_id IS NOT NULL AND o.id IS NULL'
        );
        $this->execute(
            'UPDATE {{%orgmap_node}} n '
            . 'LEFT JOIN {{%space}} s ON s.id = n.space_id '
            . 'SET n.space_id = NULL '
            . 'WHERE n.space_id IS NOT NULL AND s.id IS NULL'
        );
        $this->execute(
            'UPDATE {{%orgmap_node}} n '
            . 'LEFT JOIN {{%orgmap_asset}} a ON a.id = n.asset_id '
            . 'SET n.asset_id = NULL '
            . 'WHERE n.asset_id IS NOT NULL AND a.id IS NULL'
        );
        $this->execute(
            'DELETE c FROM {{%orgmap_connection}} c '
            . 'LEFT JOIN {{%orgmap_node}} f ON f.id = c.from_node_id '
            . 'LEFT JOIN {{%orgmap_node}} t ON t.id = c.to_node_id '
            . 'WHERE f.id IS NULL OR t.id IS NULL OR c.from_node_id = c.to_node_id'
        );
    }
}
