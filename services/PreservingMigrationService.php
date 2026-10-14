<?php

namespace humhub\modules\orgmap\services;

/** Direct callers must not accidentally invoke the archived destructive uninstall. */
class PreservingMigrationService extends \humhub\services\MigrationService
{
    public static function assertLayout(string $modulePath): void
    {
        LegacyMigrationArchive::prepare($modulePath);
        foreach (glob($modulePath . '/migrations/legacy/*.php') as $archived) {
            if (is_file($modulePath . '/migrations/' . basename($archived))) {
                throw new \LogicException('Incomplete OrgMap code replacement: a historical migration is still in the executable migration directory. Retain it only in migrations/legacy.');
            }
        }
    }

    public function migrateUp(): ?bool
    {
        self::assertLayout($this->getModule()->getBasePath());
        return parent::migrateUp();
    }

    public function migrateNew(): ?bool
    {
        self::assertLayout($this->getModule()->getBasePath());
        return parent::migrateNew();
    }

    public function uninstall(): ?bool
    {
        throw new \LogicException('OrgMap data is retained on disable. Destructive uninstall is not available through the migration service.');
    }
}
