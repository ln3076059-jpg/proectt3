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

        // Đảm bảo 100% mọi bài kiểm thử luôn chạy trên CSDL test riêng biệt `phongkham_thaithinh_test`
        config(['database.connections.mysql.database' => 'phongkham_thaithinh_test']);
        if (\Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== 'phongkham_thaithinh_test') {
            \Illuminate\Support\Facades\DB::purge('mysql');
            \Illuminate\Support\Facades\DB::reconnect('mysql');
        }

        if (\Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== 'phongkham_thaithinh_test') {
            throw new \RuntimeException('LỖI CÔ LẬP CSDL: Bài test không được phép kết nối vào CSDL chính!');
        }

        if (! static::$testDbEnsured) {
            if (! \Illuminate\Support\Facades\Schema::hasTable('doctors')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--database' => 'mysql']);
            }
            static::$testDbEnsured = true;
        }
    }
}
