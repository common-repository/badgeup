# BadgeUp PHP Client

[![Build Status](https://travis-ci.org/BadgeUp/badgeup-php-client.svg?branch=master)](https://travis-ci.org/BadgeUp/badgeup-php-client)

## Example Use
```php
$key = '<API Key>';
$bup = new BadgeUp\Client($key);

$allAchievements = $bup->getAchievements()->wait();
var_dump($allAchievements); // prints the list of achievements
```

## API

### `getAchievement(string $id)`
Retrieves an achievement by ID.

### `getAchievements()`
Retrieves an array of achievements.

### `getEarnedAchievements(string $subject = null, string $achievementId = null)`
Retrieves a list of earned achievements.

### `createEvent(string $subject, string $key, array $modifier = ['@inc' => 1], $showIncomplete = null)`
Creates a single event, returning the event and earned achievement progress records for any achievement affected.

## Running Unit Tests

Unit tests are written for PHPUnit
1. Install and Configure PHPUnit (https://phpunit.de/getting-started.html)
1. Run 'phpunit' from the project's root directory
