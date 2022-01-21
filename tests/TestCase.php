<?php

namespace Spatie\DiscordAlerts\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\DiscordAlerts\DiscordAlertsServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            DiscordAlertsServiceProvider::class,
        ];
    }
}
