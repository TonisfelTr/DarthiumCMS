<?php

namespace Engine\Services;

/**
 * Parent interface of migrations unanimous class.
 */
interface MigrationInterface
{
    /**
     * Run migration code
     *
     * @return mixed
     */
    public static function run();

    /**
     * Rollback table changes
     *
     * @return mixed
     */
    public static function rollback();
}