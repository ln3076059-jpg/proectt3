<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static bool $testDbEnsured = false;

    protected function setUp(): void
    {
        if (! static::$testDbEnsured) {
            try {
                $pdo = new \PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
                $pdo->exec('CREATE DATABASE IF NOT EXISTS `phongkham_thaithinh_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            } catch (\Throwable $e) {
                // Ignore if MySQL is managed externally
            }
        }

        parent::setUp();

        if (! static::$testDbEnsured) {
            if (! \Illuminate\Support\Facades\Schema::hasTable('doctors')) {
                \Illuminate\Support\Facades\Artisan::call('migrate');
            }
            static::$testDbEnsured = true;
        }
    }
}
