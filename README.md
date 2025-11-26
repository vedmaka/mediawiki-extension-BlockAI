# BlockAI MediaWiki Extension

> [!IMPORTANT]
> This extension is not suitable for wikis where anonymous users are allowed to edit pages!

> [!WARNING]
> This extension is experimental and may not be compatible with some other extensions!

The extension enforces a set of rules that are intended to block AI crawlers and random bots spamming
your wiki with requests to expensive pages like Special:RecentChanges and Special:RecentChangesLinked.

The extension evaluates only requests coming from anonymous users and ignores logged-in users completely.
Every request is evaluated against a set of eval-rules and decision is made based on the request evaluation results.

## Installation

* Clone the repository into your `extensions` directory
* Enable the extension in `LocalSettings.php` by adding `wfLoadExtension( 'BlockAI' );`

## Configuration

```php
$wgBlockAIThreshold = 1.0;
```

The setting can be used to adjust the threshold for marking a request as spammy. The default value is 1.0. Every
evaluation rule may have its own weight, with the default weight being 1 for all the embedded evaluation rules.

Every request starts with score if `1.0`. The formula for calculating the final score is:

```php
$score = $score * ( 1 - $eval->weight() );
```

And the final check is performed like that:

```php
if ( $score < $this->threshold ) {
    // block the request
}
```

Thus, when the total request score falls below the threshold, the request is blocked.

At the moment, all the default evaluation rules have a weight of 1, thus you can either disable the blocks completely
by setting the threshold to `0` or enable the blocks by setting it to `1`. Any other values would take no effect until
custom evaluation rules are added with wights < `1.0`

During request evaluation, every evaluation rule

## Embedded evaluation rules

* [InvalidRequest](includes/Evals/InvalidRequest.php) - fails for requests that are missing required headers
* [ExpensiveActions](includes/Evals/ExpensiveActions.php) - fails for requests that trigger expensive actions (anything except `view` and `info` are considered expensive)
* [ForeignPosts](includes/Evals/ForeignPosts.php) - fails for requests that do POST but contain no Referrer header
* [QueryParamsOrder](includes/Evals/QueryParamsOrder.php) - fails for requests that contain query parameters in a different order than expected by MediaWiki
* [SpecialPageLock](includes/Evals/SpecialPageLock.php) - fails for requests that access Special: pages that are considered expensive. All the Special pages considered expensive except `UserLogin`, `CreateAccount`, `Search`, `Random`, 'PasswordReset', `ConfirmEmail`.

## Adding custom evaluation rules

You can add your own evaluation rules by creating new classes that implement the [IEval](includes/Evals/IEval.php) interface. The
classes should be part of an extension, be loaded by the autoloader and registered through extension attributes. See
https://github.com/vedmaka/mediawiki-extension-BlockAISample for more details and a sample custom evaluation rule.

## Blocked requests

For the requests that were blocked the extension forced an early [HTTP 418 I am a teapot](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status/418) response.
