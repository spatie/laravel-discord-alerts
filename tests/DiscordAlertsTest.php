<?php

use Illuminate\Support\Facades\Bus;
use Spatie\DiscordAlerts\Exceptions\JobClassDoesNotExist;
use Spatie\DiscordAlerts\Exceptions\WebhookUrlNotValid;
use Spatie\DiscordAlerts\Facades\DiscordAlert;
use Spatie\DiscordAlerts\Jobs\SendToDiscordChannelJob;

beforeEach(function () {
    Bus::fake();
});

it('can dispatch a job to send a message to discord using the default webhook url', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');

    DiscordAlert::message('test-data');

    Bus::assertDispatched(SendToDiscordChannelJob::class);
});

it('can dispatch a job to send a message to discord using an alternative webhook url', function () {
    config()->set('discord-alerts.webhook_urls.marketing', 'https://test-domain.com');

    DiscordAlert::to('marketing')->message('test-data');

    Bus::assertDispatched(SendToDiscordChannelJob::class);
});

it('will throw an exception for a non existing job class', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');
    config()->set('discord-alerts.job', 'non-existing-job');

    DiscordAlert::message('test-data');
})->throws(JobClassDoesNotExist::class);


it('will throw an exception for an invalid webhook url', function () {
    config()->set('discord-alerts.webhook_urls.default', '');

    DiscordAlert::message('test-data');
})->throws(WebhookUrlNotValid::class);

it('will throw an exception for an invalid job class', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');
    config()->set('discord-alerts.job', '');

    DiscordAlert::message('test-data');
})->throws(JobClassDoesNotExist::class);

it('will throw an exception for a missing job class', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');
    config()->set('discord-alerts.job', null);

    DiscordAlert::message('test-data');
})->throws(JobClassDoesNotExist::class);

it('will convert a newline string (\n) into a PHP_EOL constant', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');

    DiscordAlert::message('test \n data');

    Bus::assertDispatched(function (SendToDiscordChannelJob $job) {
        return $job->text === "test " . PHP_EOL . " data";
    });
});

it('will send a message as well as a embed in just one message', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');

    DiscordAlert::message('test \n data', [
        [
            'title' => 'Test Embed',
            'description' => 'This is a test embed.'
        ]
    ]);

    Bus::assertDispatched(function (SendToDiscordChannelJob $job) {
        return $job->text === "test " . PHP_EOL . " data" && count($job->embeds);
    });
});
