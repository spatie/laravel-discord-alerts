<?php

use Spatie\DiscordAlerts\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function fixturePath(string $name = 'report.txt'): string
{
    return __DIR__."/fixtures/{$name}";
}
