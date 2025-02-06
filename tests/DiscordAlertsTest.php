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

it('can dispatch a job to send a message to discord using the default webhook url and a custom queue', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');
    config()->set('discord-alerts.queue_connection', 'custom-queue-connection');

    DiscordAlert::message('test-data');

    Bus::assertDispatched(SendToDiscordChannelJob::class, function ($job) {
        return $job->connection === 'custom-queue-connection';
    });
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

it('will convert a newline string (\n) into a PHP_EOL constant in embeds as well', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');

    DiscordAlert::message('test \n data', [
        [
            'title' => 'Test Embed',
            'description' => 'This is a test embed.\nI should be on a new line.',
        ],
    ]);

    Bus::assertDispatched(function (SendToDiscordChannelJob $job) {
        return $job->embeds[0]['description'] === "This is a test embed." . PHP_EOL . "I should be on a new line.";
    });
});

it('will send a message as well as a embed in just one message', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');

    DiscordAlert::message('test \n data', [
        [
            'title' => 'Test Embed',
            'description' => 'This is a test embed.',
        ],
    ]);

    Bus::assertDispatched(function (SendToDiscordChannelJob $job) {
        return $job->text === "test " . PHP_EOL . " data" && count($job->embeds);
    });
});

it('can delay a message by minutes', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');

    DiscordAlert::delayMinutes(10)->message('test-data');

    Bus::assertDispatched(SendToDiscordChannelJob::class, function ($job) {
        return $job->delay === 10;
    });
});

it('can delay a message by hours', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');

    DiscordAlert::delayHours(1)->message('test-data');

    Bus::assertDispatched(SendToDiscordChannelJob::class, function ($job) {
        return $job->delay === 60;
    });
});

it('can delay a message by hours and minutes', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');

    DiscordAlert::delayHours(1)->delayMinutes(10)->message('test-data');

    Bus::assertDispatched(SendToDiscordChannelJob::class, function ($job) {
        return $job->delay === 70;
    });
});

it('includes username when specified', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');

    DiscordAlert::withUsername('CronBot')->message('test-data');

    Bus::assertDispatched(SendToDiscordChannelJob::class, function ($job) {
        return $job->username === 'CronBot';
    });
});

it('does not include username when not set', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');

    DiscordAlert::message('test-data');

    Bus::assertDispatched(SendToDiscordChannelJob::class, function ($job) {
        return $job->username === null;
    });
});

it('throws an exception for an invalid username', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');

    DiscordAlert::withUsername('<script>alert(1)</script>')->message('test-data');
})->throws(InvalidArgumentException::class);

it('includes avatar_url when a valid one is set', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');
    config()->set('discord-alerts.avatar_urls.custom', 'https://example.com/avatar.png');

    DiscordAlert::withAvatar('custom')->message('test-data');

    Bus::assertDispatched(SendToDiscordChannelJob::class, function ($job) {
        return $job->avatar_url === 'https://example.com/avatar.png';
    });
});

it('does not include avatar_url when default is empty', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');
    config()->set('discord-alerts.avatar_urls.default', '');

    DiscordAlert::message('test-data');

    Bus::assertDispatched(SendToDiscordChannelJob::class, function ($job) {
        return $job->avatar_url === null;
    });
});

it('throws an exception for an invalid avatar URL', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');
    config()->set('discord-alerts.avatar_urls.malicious', 'invalid-url');

    DiscordAlert::withAvatar('malicious')->message('test-data');
})->throws(InvalidArgumentException::class);

it('throws an exception if avatar URL is not HTTPS', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');
    config()->set('discord-alerts.avatar_urls.insecure', 'http://example.com/avatar.png');

    DiscordAlert::withAvatar('insecure')->message('test-data');
})->throws(InvalidArgumentException::class);

it('does not include tts when not explicitly set', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');

    DiscordAlert::message('test-data');

    Bus::assertDispatched(SendToDiscordChannelJob::class, function ($job) {
        return $job->tts === false;
    });
});

it('includes tts when explicitly set to true', function () {
    config()->set('discord-alerts.webhook_urls.default', 'https://test-domain.com');

    DiscordAlert::enableTTS(true)->message('test-data');

    Bus::assertDispatched(SendToDiscordChannelJob::class, function ($job) {
        return $job->tts === true;
    });
});
