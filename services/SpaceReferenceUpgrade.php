<?php

namespace humhub\modules\orgmap\services;

use humhub\components\Migration;
use humhub\modules\orgmap\helpers\TableNameHelper;
use humhub\modules\space\models\Space;

/** Retain container provenance after a Space is deleted; orphaned nodes stay private. */
final class SpaceReferenceUpgrade extends Migration
{
    public function apply(): void
    {
        TableNameHelper::assertUnambiguous($this->db);
        $table = TableNameHelper::resolve('orgmap_node', $this->db);
        $schema = $this->db->schema->getTableSchema($table, true);
        if ($schema === null || $schema->getColumn('space_id') === null) {
            return;
        }
        $spaceTable = $this->db->schema->getRawTableName(Space::tableName());
        $remove = [];
        foreach ($schema->foreignKeys as $name => $foreignKey) {
            if (!isset($foreignKey['space_id'])
                || $this->db->schema->getRawTableName($foreignKey[0]) !== $spaceTable) {
                continue;
            }
            if (array_diff_key($foreignKey, [0 => true]) !== ['space_id' => 'id']) {
                throw new \RuntimeException('Unexpected composite OrgMap Space reference; no constraint was changed.');
            }
            $remove[] = $name;
        }
        // Keep query performance even if the old foreign key owned the only index.
        $indexes = $this->db->createCommand('SHOW INDEX FROM ' . $this->db->quoteTableName($table))->queryAll();
        $covering = array_filter($indexes, static fn(array $index) => $index['Column_name'] === 'space_id' && (int) $index['Seq_in_index'] === 1);
        if (!$covering) {
            $this->createIndex('idx-om-' . substr(hash('sha256', $table . ':space_id'), 0, 16), $table, 'space_id');
        }
        foreach ($remove as $name) {
            $this->dropForeignKey($name, $table);
        }
        $this->db->schema->refresh();
    }
}
