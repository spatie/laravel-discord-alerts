# Quickly send a message to Discord

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-discord-alerts.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-discord-alerts)
[![run-tests](https://github.com/spatie/laravel-discord-alerts/actions/workflows/run-tests.yml/badge.svg)](https://github.com/spatie/laravel-discord-alerts/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/spatie/laravel-discord-alerts/actions/workflows/phpstan.yml/badge.svg)](https://github.com/spatie/laravel-discord-alerts/actions/workflows/phpstan.yml)
[![Check & fix styling](https://github.com/spatie/laravel-discord-alerts/actions/workflows/php-cs-fixer.yml/badge.svg)](https://github.com/spatie/laravel-discord-alerts/actions/workflows/php-cs-fixer.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-discord-alerts.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-discord-alerts)

This package can quickly send alerts to Discord. You can use this to notify yourself of any noteworthy events happening in your app. 

Want to quickly send alerts to Slack? Then check out [laravel-slack-alerts](https://github.com/spatie/laravel-slack-alerts).

```php
use Spatie\DiscordAlerts\Facades\DiscordAlert;

DiscordAlert::message("You have a new subscriber to the {$newsletter->name} newsletter!");
```

Under the hood, a job is used to communicate with Discord. This prevents your app from failing in case Discord is down.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-discord-alerts.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-discord-alerts)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-discord-alerts
```

You can set a `DISCORD_ALERT_WEBHOOK` env variable containing a valid Discord webhook URL. You can learn how to get a webhook URL [in the Discord API docs](https://support.discord.com/hc/en-us/articles/228383668-Intro-to-Webhooks).


Alternatively, you can publish the config file with:

```bash
php artisan vendor:publish --tag="discord-alerts-config"
```

This is the contents of the published config file:

```php
return [
    /*
     * The webhook URLs that we'll use to send a message to Discord.
     */
    'webhook_urls' => [
        'default' => env('DISCORD_ALERT_WEBHOOK'),
    ],

    /*
     * This job will send the message to Discord. You can extend this
     * job to set timeouts, retries, etc...
     */
    'job' => Spatie\DiscordAlerts\Jobs\SendToDiscordChannelJob::class,
];

```

## Usage

To send a message to Discord, simply call `DiscordAlert::message()` and pass it any message you want.

```php
DiscordAlert::message("You have a new subscriber to the {$newsletter->name} newsletter!");
```

## Sending an embed

To send an embed you can call the same function as above. Just add the embed as a second array as following:

```php
DiscordAlert::message("You have a new subscriber to the {$newsletter->name} newsletter!", [
    [
        'title' => 'My title',
        'description' => 'My description',
        'color' => '#E77625',
        'author' => [
            'name' => 'Spatie',
            'url' => 'https://spatie.be/'
        ]    
    ]
]);
```

You can also send multiple embeds as one message. Just be careful that you don't hit the limit of Discord.

## Using multiple webhooks

You can also use an alternative webhook, by specify extra ones in the config file.

```php
// in config/discord-alerts.php

'webhook_urls' => [
    'default' => 'https://hooks.discord.com/services/XXXXXX',
    'marketing' => 'https://hooks.discord.com/services/YYYYYY',
],
```

The webhook to be used can be chosen using the `to` function.

```php
use Spatie\DiscordAlerts\Facades\DiscordAlert;

DiscordAlert::to('marketing')->message("You have a new subscriber to the {$newsletter->name} newsletter!");
```

### Using a custom webhooks

The `to` function also supports custom webhook urls.

```php
use Spatie\DiscordAlerts\Facades\DiscordAlert;

DiscordAlert::to('https://custom-url.com')->message("You have a new subscriber to the {$newsletter->name} newsletter!");
```

## Formatting

### Markdown
You can format your messages with markup. Learn how [in the Discord API docs](https://support.discord.com/hc/en-us/articles/210298617-Markdown-Text-101-Chat-Formatting-Bold-Italic-Underline-).

```php
use Spatie\DiscordAlerts\Facades\DiscordAlert;

DiscordAlert::message("A message **with some bold statements** and _some italicized text_.");
```

### Emoji's

You can use the same emoji codes as in Discord. This means custom emoji's are also supported.
```php
use Spatie\DiscordAlerts\Facades\DiscordAlert;

DiscordAlert::message(":smile: :custom-code:");
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Rias Van der Veken](https://github.com/Riasvdv)
- [Niels Vanpachtenbeke](https://github.com/Nielsvanpach)
- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
