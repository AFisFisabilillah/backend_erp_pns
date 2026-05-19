<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $_ENV['APP_ENV'] = $_SERVER['APP_ENV'] = 'testing';
        $_ENV['DB_CONNECTION'] = $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = $_SERVER['DB_DATABASE'] = ':memory:';
        $_ENV['CACHE_STORE'] = $_SERVER['CACHE_STORE'] = 'array';
        $_ENV['QUEUE_CONNECTION'] = $_SERVER['QUEUE_CONNECTION'] = 'sync';
        $_ENV['SESSION_DRIVER'] = $_SERVER['SESSION_DRIVER'] = 'array';
        $_ENV['MAIL_MAILER'] = $_SERVER['MAIL_MAILER'] = 'array';

        putenv('APP_ENV=testing');
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        putenv('CACHE_STORE=array');
        putenv('QUEUE_CONNECTION=sync');
        putenv('SESSION_DRIVER=array');
        putenv('MAIL_MAILER=array');

        $app = require Application::inferBasePath().'/bootstrap/app.php';

        $this->traitsUsedByTest = class_uses_recursive(static::class);

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
