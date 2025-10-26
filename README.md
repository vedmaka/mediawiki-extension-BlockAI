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

* `$wgBlockAIThreshold = 1.0;` - the setting can be used to adjust the threshold for marking a request as spammy. The default value is 1.0, which means that request is only considered spammy if the total evaluation score is greater than 1. Every evaluation rule may contribute its own weight to the total score of the request being evaluated. By tuninig up this threshold value you can adjust how many evaluations the request must fail before it is considered spammy. Setting this to `1.0` guarantees that the request will be blocked as soon as any evaluation rule fails (as all the bundled evaluation rules has weight of `1.0`). Setting this to `2.0` would allow some of the evaluations to pass before the request is blocked.

## Embedded evaluation rules

* [InvalidRequest](includes/Evals/InvalidRequest.php) - fails for requests that are missing required headers
* [ExpensiveActions](includes/Evals/ExpensiveActions.php) - fails for requests that trigger expensive actions (anything except `view` and `info` are considered expensive)
* [ForeignPosts](includes/Evals/ForeignPosts.php) - fails for requests that do POST but contain no Referrer header
* [QueryParamsOrder](includes/Evals/QueryParamsOrder.php) - fails for requests that contain query parameters in a different order than expected by MediaWiki
* [SpecialPageLock](includes/Evals/SpecialPageLock.php) - fails for requests that access Special: pages that are considered expensive. All the Special pages considered expensive except `UserLogin`, `CreateAccount`, `Search`, `Random`.

## Blocked requests

For the requests that were blocked the extension forced an early [HTTP 418 I am a teapot](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status/418) response.
