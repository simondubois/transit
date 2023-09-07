<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Webmozart\Assert\Assert;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        Assert::isInstanceOf($app, Application::class);

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
