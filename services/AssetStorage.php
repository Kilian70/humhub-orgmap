<?php

namespace humhub\modules\orgmap\services;

use humhub\modules\file\models\File;
use humhub\modules\orgmap\helpers\BackgroundImageHelper;
use humhub\modules\orgmap\models\Asset;
use RuntimeException;
use Yii;

/** Both HumHub versions accept bytes; no local path is passed to the 1.19 store. */
final class AssetStorage
{
    public static function read(File $file): string
    {
        $store = $file->getStore();
        if (!$store->has()) {
            throw new RuntimeException('OrgMap file is missing from storage.');
        }
        $content = method_exists($store, 'getContent') ? $store->getContent() : file_get_contents($store->get());
        if ($content === false) {
            throw new RuntimeException('OrgMap file could not be read.');
        }
        return $content;
    }

    public static function file(Asset $asset, bool $preview = true): ?File
    {
        $guid = $preview && $asset->hasAttribute('preview_file_guid') ? $asset->preview_file_guid : null;
        if (!$guid) {
            $guid = $asset->hasAttribute('file_guid') ? $asset->file_guid : null;
        }
        if (!$guid) {
            return null;
        }
        // A supplied GUID must never let another asset borrow an unrelated File record.
        $file = File::findOne(['guid' => $guid, 'object_model' => Asset::class, 'object_id' => $asset->id]);
        return $file !== null && (!$file->hasAttribute('public') || !$file->public) ? $file : null;
    }

    public static function put(Asset $asset, string $content, string $name): File
    {
        $file = new File();
        $file->file_name = basename($name);
        $file->mime_type = (new \finfo(FILEINFO_MIME_TYPE))->buffer($content);
        $file->size = strlen($content);
        $file->object_model = Asset::class;
        $file->object_id = $asset->id;
        if ($file->hasAttribute('public')) {
            $file->public = 0;
        }
        // Explicit lifecycle owner; also protects files during a retried upgrade.
        if ($file->hasAttribute('standalone')) {
            $file->standalone = 1;
        }
        if (!$file->save()) {
            throw new RuntimeException('OrgMap File record could not be saved.');
        }
        $file->setStoredFileContent($content);
        if (!hash_equals(hash('sha256', $content), hash('sha256', self::read($file)))) {
            throw new RuntimeException('OrgMap stored file checksum does not match.');
        }
        return $file;
    }

    public static function saveUpload(Asset $asset, string $content): void
    {
        $original = self::put($asset, $content, $asset->filename);
        if (!$asset->updateAttributes(['file_guid' => $original->guid])) {
            throw new RuntimeException('OrgMap file reference could not be saved.');
        }
        if ($asset->type !== 'background') {
            return;
        }
        $temporary = tempnam(sys_get_temp_dir(), 'orgmap-preview-');
        if ($temporary === false) {
            return; // Original is already persisted and remains a usable image.
        }
        try {
            if (file_put_contents($temporary, $content) !== strlen($content)) {
                return;
            }
            BackgroundImageHelper::optimize($temporary);
            $preview = file_get_contents($temporary);
            if ($preview !== false && $preview !== $content) {
                $file = self::put($asset, $preview, $asset->filename);
                $asset->updateAttributes(['preview_file_guid' => $file->guid]);
            }
        } catch (\Throwable $exception) {
            // Preview generation must not invalidate or overwrite an uploaded original.
            Yii::warning('OrgMap preview unavailable: ' . $exception->getMessage(), __METHOD__);
        } finally {
            unlink($temporary);
        }
    }
}
