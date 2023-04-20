<?php

namespace Engine;

use FilesystemIterator;

class MigrationAgent
{
    private const MIGRATION_DIR = HOME_ROOT . "engine/customs/migrations/";
    private const MIGRATION_TABLE = "tt_migrations";

    public static function run() {
        $files = [];
        $fileIterator = new FilesystemIterator(self::MIGRATION_DIR, \FilesystemIterator::SKIP_DOTS);

        if (iterator_count($fileIterator) == 0) {
            return;
        }

        foreach ($fileIterator as $file) {
            $files[] = $file->getFilename();
        }

        $buffer = DataKeeper::MakeQuery("select * from `tt_migrations`", null, true);
        foreach ($buffer as $records) {
            if (in_array($records["migration_name"], $files)) {
                $files = array_diff($files, [$records["migration_name"]]);
            }
        }

        $fileIterator->rewind();
        foreach ($fileIterator as $file) {
            if (in_array($file->getFilename(), $files)) {
                $migrationClass = include_once self::MIGRATION_DIR . $file->getFilename();
                $migrationClass->run();
                DataKeeper::InsertTo(self::MIGRATION_TABLE, ["migration_name" => $file->getFilename()]);
            }
        }
    }

    public static function rollback(int $migrationId) {
        $result = DataKeeper::Get($_SERVER["DOCUMENT_ROOT"] . self::MIGRATION_TABLE, ["migration_name"], ["id" => $migrationId], 1);
        if (count($result) == 0) {
            return;
        }

        if (file_exists($_SERVER["DOCUMENT_ROOT"] . self::MIGRATION_DIR . $result["migration_name"])) {
            $migrationClass = include_once self::MIGRATION_DIR . $result["migration_name"];
            $migrationClass->rollback();
            DataKeeper::Delete(self::MIGRATION_TABLE, ["id" => $migrationId]);
        }
    }

    public static function rollbackAll() {
        $migrationNames = DataKeeper::Get($_SERVER["DOCUMENT_ROOT"] . self::MIGRATION_TABLE, ["migration_name"]);
        $mnArray = [];

        foreach ($migrationNames as $name) {
            $mnArray[] = $name;
        }

        foreach ($mnArray as $nm) {
            if (file_exists(self::MIGRATION_DIR . $nm)) {
                $migrationClass = include_once self::MIGRATION_DIR . $nm;
                $migrationClass->rollback();
                DataKeeper::Delete(self::MIGRATION_TABLE, ["migration_name" => $nm]);
            }
        }
    }
}