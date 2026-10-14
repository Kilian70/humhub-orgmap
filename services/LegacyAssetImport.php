<?php

namespace humhub\modules\orgmap\services;

use humhub\modules\orgmap\models\Asset;
use RuntimeException;
use Yii;

/** Copies and verifies first, then relocates ALL originals, including unused files. */
final class LegacyAssetImport
{
    public static function archivePath(): string
    {
        return Yii::getAlias(Yii::$app->getModule('orgmap')->legacyAssetArchivePath);
    }

    public static function run(): void
    {
        $public = Yii::getAlias('@webroot/uploads/orgmap/assets');
        $archive = self::archivePath();
        $source = is_dir($public) ? $public : $archive;
        if (is_link($public) || is_link($archive)) {
            throw new RuntimeException('OrgMap legacy directories must not be symlinks.');
        }
        foreach (Asset::find()->all() as $asset) {
            // New assets and already imported records are verified, never overwritten.
            $path = $source . '/' . basename($asset->filename);
            if (basename($asset->filename) !== $asset->filename || is_link($path)) {
                throw new RuntimeException('Unsafe OrgMap legacy filename. Original preserved.');
            }
            $file = AssetStorage::file($asset, false);
            if ($asset->file_guid && $file === null) {
                throw new RuntimeException('OrgMap asset has an invalid existing file reference. Upgrade stopped without replacing it.');
            }
            if ($file !== null && !is_file($path)) {
                AssetStorage::read($file);
                continue;
            }
            if (!is_file($path) || ($bytes = file_get_contents($path)) === false) {
                throw new RuntimeException('OrgMap legacy asset ' . $asset->id . ' is missing. Upgrade stopped without deleting data.');
            }
            if ($file === null) {
                $file = AssetStorage::put($asset, $bytes, $asset->filename);
                $asset->updateAttributes(['file_guid' => $file->guid]);
            }
            if (!hash_equals(hash('sha256', $bytes), hash('sha256', AssetStorage::read($file)))) {
                throw new RuntimeException('OrgMap legacy asset checksum mismatch. Original preserved.');
            }
        }
        if (!is_dir($public)) {
            return;
        }
        // Require a private destination outside the webroot, or inside protected/.
        $webroot = realpath(Yii::getAlias('@webroot'));
        $parent = realpath(dirname($archive));
        $protected = realpath(Yii::getAlias('@app'));
        if ($parent === false || $webroot === false || $protected === false
            || ($parent === $webroot || str_starts_with($parent, $webroot . DIRECTORY_SEPARATOR))
                && !($parent === $protected || str_starts_with($parent, $protected . DIRECTORY_SEPARATOR))) {
            throw new RuntimeException('OrgMap archive destination must be private.');
        }
        if (file_exists($archive) || !rename($public, $archive)) {
            throw new RuntimeException('OrgMap legacy directory could not be relocated privately. No original was deleted.');
        }
    }
}
