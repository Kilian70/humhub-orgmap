<?php

namespace humhub\modules\orgmap\services;

use humhub\components\Migration;
use humhub\modules\orgmap\helpers\TableNameHelper;
use humhub\modules\space\models\Space;
use yii\db\Expression;

/** Add missing schema only. Published historical data cleanups are never replayed. */
final class SchemaUpgrade extends Migration
{
    public function apply(): void
    {
        TableNameHelper::assertUnambiguous($this->db);
        $tables = [];
        foreach (TableNameHelper::TABLES as $name) {
            $tables[$name] = TableNameHelper::resolve($name, $this->db);
        }
        $definitions = [
            'orgmap_organ' => [
                'id' => $this->primaryKey(), 'name' => $this->string()->notNull(),
                'parent_id' => $this->integer()->null(), 'sort_order' => $this->integer()->defaultValue(0),
                'created_at' => $this->integer(), 'created_by' => $this->integer(),
                'updated_at' => $this->integer(), 'updated_by' => $this->integer(), 'color' => $this->string(255)->null(),
            ],
            'orgmap_asset' => [
                'id' => $this->primaryKey(), 'title' => $this->string()->notNull(), 'filename' => $this->string()->notNull(),
                'type' => $this->string(50)->defaultValue('background'), 'created_at' => $this->integer(), 'created_by' => $this->integer(),
                'file_guid' => $this->string(36)->null(), 'preview_file_guid' => $this->string(36)->null(), 'deleted_at' => $this->integer()->null(),
            ],
            'orgmap_node' => [
                'id' => $this->primaryKey(), 'parent_id' => $this->integer()->null(), 'title' => $this->string()->notNull(),
                'type' => $this->string(50)->defaultValue('space'), 'space_id' => $this->integer()->null(),
                'url' => $this->string()->null(), 'color' => $this->string(20)->null(),
                'pos_x' => $this->integer()->defaultValue(0), 'pos_y' => $this->integer()->defaultValue(0),
                'radius' => $this->integer()->defaultValue(80), 'is_external' => $this->boolean()->defaultValue(false),
                'visible' => $this->boolean()->defaultValue(true), 'sort_order' => $this->integer()->defaultValue(0),
                'label_x' => $this->integer()->null(), 'label_y' => $this->integer()->null(), 'link_type' => $this->string(50)->null(),
                'font_size' => $this->integer()->null(), 'organ_id' => $this->integer()->null(),
                'display_mode' => $this->string(50)->defaultValue('color'), 'custom_image' => $this->string()->null(),
                'opacity' => $this->integer()->defaultValue(100), 'image_source' => $this->string(20)->defaultValue('space'),
                'label_background' => $this->boolean()->notNull()->defaultValue(1), 'show_label' => $this->boolean()->defaultValue(1),
                'open_in_new_tab' => $this->boolean()->notNull()->defaultValue(0), 'shape' => $this->string(255)->defaultValue('circle'),
                'width' => $this->integer()->null(), 'height' => $this->integer()->null(),
                'is_background' => $this->boolean()->defaultValue(0), 'show_parent_line' => $this->boolean()->notNull()->defaultValue(1),
                'content' => $this->text()->null(), 'asset_id' => $this->integer()->null(), 'show_in_tree' => $this->boolean()->notNull()->defaultValue(1),
                'icon_class' => $this->string(100)->null(), 'subtitle' => $this->string()->null(),
                'background_size' => $this->string(20)->notNull()->defaultValue('cover'),
                'border_width' => $this->integer()->notNull()->defaultValue(1), 'border_color' => $this->string(20)->notNull()->defaultValue('#2196f3'),
                'color_opacity' => $this->integer()->notNull()->defaultValue(100), 'image_opacity' => $this->integer()->notNull()->defaultValue(100),
            ],
            'orgmap_connection' => [
                'id' => $this->primaryKey(), 'from_node_id' => $this->integer()->notNull(), 'to_node_id' => $this->integer()->notNull(),
                'color' => $this->string(50)->null(), 'width' => $this->integer()->defaultValue(2), 'style' => $this->string(50)->defaultValue('solid'),
                'created_at' => $this->integer(), 'created_by' => $this->integer(), 'updated_at' => $this->integer(), 'updated_by' => $this->integer(),
                'label' => $this->string(50)->null(), 'arrow' => $this->string(50)->null(), 'curve' => $this->integer()->defaultValue(0),
                'label_offset_x' => $this->integer()->defaultValue(0), 'label_offset_y' => $this->integer()->defaultValue(0),
                'label_rotation' => $this->string(50)->defaultValue('auto'), 'font_weight' => $this->string(50)->defaultValue('normal'),
                'font_size' => $this->integer()->defaultValue(12), 'type' => $this->string(50)->null(),
            ],
        ];
        foreach ($definitions as $name => $columns) {
            $table = $tables[$name];
            $schema = $this->db->schema->getTableSchema($table, true);
            if ($schema === null) {
                $this->createTable($table, $columns);
                continue;
            }
            $newOpacity = [];
            foreach ($columns as $column => $definition) {
                if ($schema->getColumn($column) === null) {
                    $this->addColumn($table, $column, $definition);
                    if (in_array($column, ['color_opacity', 'image_opacity'], true)) {
                        $newOpacity[$column] = new Expression($column === 'color_opacity'
                            ? 'COALESCE([[opacity]], 100)'
                            : "CASE WHEN [[display_mode]] = 'mixed' THEN 100 ELSE COALESCE([[opacity]], 100) END");
                    }
                }
            }
            if ($newOpacity) {
                $this->update($table, $newOpacity);
            }
        }
        $this->db->schema->refresh();
        (new SpaceReferenceUpgrade(['db' => $this->db]))->apply();
        $references = [
            ['orgmap_organ', 'parent_id', 'orgmap_organ', 'SET NULL'],
            ['orgmap_node', 'parent_id', 'orgmap_node', 'SET NULL'],
            ['orgmap_node', 'organ_id', 'orgmap_organ', 'SET NULL'],
            ['orgmap_node', 'asset_id', 'orgmap_asset', 'SET NULL'],
            ['orgmap_connection', 'from_node_id', 'orgmap_node', 'CASCADE'],
            ['orgmap_connection', 'to_node_id', 'orgmap_node', 'CASCADE'],
        ];
        foreach ($references as [$source, $column, $target, $delete]) {
            $table = $tables[$source];
            $targetTable = $target === null ? Space::tableName() : $tables[$target];
            $schema = $this->db->schema->getTableSchema($table, true);
            // The additional index has a predictable short name and is safe to retry.
            $index = 'idx-om-' . substr(hash('sha256', $table . ':' . $column), 0, 16);
            $existingIndexes = $this->db->createCommand('SHOW INDEX FROM ' . $this->db->quoteTableName($table))->queryAll();
            $coveringIndexes = array_filter($existingIndexes, static fn(array $entry) => $entry['Column_name'] === $column && (int) $entry['Seq_in_index'] === 1);
            if (!$coveringIndexes) {
                $this->createIndex($index, $table, $column);
            }
            if ($this->db->schema->getTableSchema($targetTable, true) === null) {
                throw new \RuntimeException('OrgMap reference table is missing: ' . $targetTable);
            }
            $present = false;
            foreach ($schema->foreignKeys as $foreignKey) {
                if (($foreignKey[$column] ?? null) === 'id' && $foreignKey[0] === $targetTable) {
                    $present = true;
                }
            }
            if ($present) {
                continue;
            }
            $sql = 'SELECT COUNT(*) FROM ' . $this->db->quoteTableName($table) . ' n LEFT JOIN '
                . $this->db->quoteTableName($targetTable) . ' t ON t.id = n.' . $this->db->quoteColumnName($column)
                . ' WHERE n.' . $this->db->quoteColumnName($column) . ' IS NOT NULL AND t.id IS NULL';
            if ((int) $this->db->createCommand($sql)->queryScalar() > 0) {
                echo "Preserved unresolved references in $table.$column; foreign key deferred.\n";
                continue;
            }
            $this->addForeignKey('fk-om-' . substr(hash('sha256', $table . ':' . $column), 0, 16), $table, $column, $targetTable, 'id', $delete, 'CASCADE');
        }
        $this->db->schema->refresh();
    }
}
