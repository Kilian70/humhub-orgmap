<?php

namespace humhub\modules\orgmap\services;

use LogicException;

/** Relocate only verified historical upload leftovers; never execute or discard them. */
final class LegacyMigrationArchive
{
    public static function prepare(string $modulePath): void
    {
        $directory = $modulePath . '/migrations';
        $archive = $directory . '/legacy';
        if (is_link($modulePath) || is_link($directory) || is_link($archive)) {
            throw new LogicException('OrgMap migration directories must not be symlinks.');
        }
        $manifestPath = $archive . '/manifest.json';
        if (is_link($manifestPath) || !is_file($manifestPath)) {
            throw new LogicException('OrgMap historical migration manifest is missing or unsafe. Upload the complete module.');
        }
        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (!is_array($manifest) || count($manifest) !== 35) {
            throw new LogicException('Invalid OrgMap historical migration manifest.');
        }
        $pending = [];
        foreach ($manifest as $name => $hash) {
            if (!is_string($name) || !preg_match('/^(m[0-9]{6}_[0-9]{6}_[a-z0-9_]+|uninstall)\.php$/D', $name)
                || !is_string($hash) || !preg_match('/^[a-f0-9]{64}$/D', $hash)) {
                throw new LogicException('Invalid OrgMap historical migration manifest entry.');
            }
            if (file_exists($directory . '/' . $name) || is_link($directory . '/' . $name)) {
                $pending[$name] = $hash;
            }
        }
        if (!$pending) {
            return;
        }
        $lockPath = $archive . '/.archive.lock';
        if (is_link($lockPath)) {
            throw new LogicException('Unsafe OrgMap migration archive lock.');
        }
        $lock = @fopen($lockPath, 'c');
        if ($lock === false) {
            throw new LogicException('OrgMap cannot archive old migrations: allow the PHP user to write migrations/legacy. Original historical files were not moved.');
        }
        try {
            if (!flock($lock, LOCK_EX)) {
                throw new LogicException('Cannot lock the OrgMap migration archive.');
            }
            // Validate the entire batch before moving any original. Recheck after locking.
            foreach ($pending as $name => $hash) {
                foreach ([$archive . '/' . $name, $directory . '/' . $name] as $path) {
                    if (!file_exists($path) && !is_link($path) && dirname($path) === $directory) {
                        continue; // Another request may already have archived this leftover.
                    }
                    if (is_link($path) || !is_file($path) || !is_readable($path)
                        || !hash_equals($hash, hash_file('sha256', $path))) {
                        throw new LogicException('OrgMap historical migration differs or is unsafe: ' . $name . '. Original retained; inspect local changes before updating.');
                    }
                }
            }
            $pending = array_filter($pending, static fn($hash, $name) => is_file($directory . '/' . $name), ARRAY_FILTER_USE_BOTH);
            if (!$pending) {
                return;
            }
            $parent = $archive . '/overlaid';
            if (is_link($parent) || (file_exists($parent) && !is_dir($parent))) {
                throw new LogicException('Unsafe OrgMap upload archive directory.');
            }
            if (!is_dir($parent) && !@mkdir($parent, 0700)) {
                throw new LogicException('Cannot create OrgMap migration upload archive; original files retained.');
            }
            $batch = $parent . '/' . bin2hex(random_bytes(12));
            if (!@mkdir($batch, 0700)) {
                throw new LogicException('Cannot create unique OrgMap migration archive batch.');
            }
            foreach ($pending as $name => $hash) {
                $source = $directory . '/' . $name;
                if (is_link($source) || !is_file($source) || !hash_equals($hash, hash_file('sha256', $source))
                    || !@rename($source, $batch . '/' . $name)) {
                    throw new LogicException('Cannot archive OrgMap historical migration: ' . $name . '. Retained files are in migrations/legacy/overlaid; retry after checking permissions.');
                }
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
