<?php

namespace humhub\modules\orgmap\helpers;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Connection;

/** Resolve legacy tables without renaming or silently merging existing data. */
final class TableNameHelper
{
    public const TABLES = ['orgmap_node', 'orgmap_organ', 'orgmap_connection', 'orgmap_asset'];

    public static function resolve(string $name, ?Connection $db = null): string
    {
        $db ??= Yii::$app->db;
        $prefixed = $db->tablePrefix . $name;
        if ($prefixed === $name) {
            return $name;
        }
        $legacyExists = $db->schema->getTableSchema($name, true) !== null;
        $prefixedExists = $db->schema->getTableSchema($prefixed, true) !== null;
        if ($legacyExists && $prefixedExists) {
            throw new InvalidConfigException('Ambiguous OrgMap tables: ' . $name . ' and ' . $prefixed . '. No data was merged.');
        }
        return $legacyExists ? $name : $prefixed;
    }

    public static function assertUnambiguous(?Connection $db = null): void
    {
        foreach (self::TABLES as $name) {
            self::resolve($name, $db);
        }
    }
}
