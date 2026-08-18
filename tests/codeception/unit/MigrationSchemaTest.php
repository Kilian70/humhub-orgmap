<?php

namespace humhub\modules\orgmap\tests\codeception\unit;

use orgmap\OrgMapTestCase;
use Yii;

class MigrationSchemaTest extends OrgMapTestCase
{
    public function testCurrentSchemaIsInstalled(): void
    {
        $schema = Yii::$app->db->schema;

        $expectedColumns = [
            'orgmap_organ' => ['id', 'name', 'parent_id', 'sort_order', 'color'],
            'orgmap_asset' => ['id', 'title', 'filename', 'type', 'created_at', 'created_by'],
            'orgmap_node' => [
                'id', 'parent_id', 'title', 'type', 'organ_id', 'space_id', 'asset_id',
                'pos_x', 'pos_y', 'radius', 'width', 'height', 'shape', 'visible',
                'display_mode', 'image_source', 'opacity', 'color_opacity', 'image_opacity',
                'border_color', 'border_width', 'content', 'icon_class', 'subtitle',
            ],
            'orgmap_connection' => [
                'id', 'from_node_id', 'to_node_id', 'color', 'width', 'style', 'arrow',
                'label', 'curve', 'label_offset_x', 'label_offset_y', 'label_rotation',
                'font_weight', 'font_size', 'type',
            ],
        ];

        foreach ($expectedColumns as $tableName => $columns) {
            $table = $schema->getTableSchema($tableName, true);
            $this->assertNotNull($table, 'Missing table: ' . $tableName);

            foreach ($columns as $column) {
                $this->assertArrayHasKey($column, $table->columns, $tableName . '.' . $column . ' is missing.');
            }
        }
    }
}
