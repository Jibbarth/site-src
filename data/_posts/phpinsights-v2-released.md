---
title: "PHPInsights v2 released"
date: 2021-06-21
hero: https://user-images.githubusercontent.com/3168281/122650958-08bd6080-d136-11eb-8e77-04cf8377cb6b.png
state: published
---

In case you missed the following tweet, let me introduce the new **v2.0** for [PHPInsights](https://phpinsights.com).

<div data-controller="tweet-embed" data-tweet-embed-id-value="1385995349605724162">
<blockquote><p lang="en" dir="ltr">People using PHP Insights: <a href="https://twitter.com/jibbarth?ref_src=twsrc%5Etfw">@jibbarth</a> is making the final preparations for v2 — this release will feature:<br><br>✓ An &quot;--fix&quot; option that applies the fixes proposed by <a href="https://twitter.com/phpinsights?ref_src=twsrc%5Etfw">@phpinsights</a> automatically. 🔥<br>✓ Blazing fast analysis by using cache and all your CPU cores. 🚀<br>✓ And more! <a href="https://t.co/K4w5rNFpwr">pic.twitter.com/K4w5rNFpwr</a></p>&mdash; Nuno Maduro (@enunomaduro) <a href="https://twitter.com/enunomaduro/status/1385995349605724162?ref_src=twsrc%5Etfw">April 24, 2021</a></blockquote>
</div>

This new version was mainly focused to improve performances, but it came also with new shiny features ✨

Let's discover them together.

### Speed up the analysis process 🚀

Back in march 2020, I started to learn using [Blackfire](https://blackfire.io) by following this [course on SymfonyCast](https://symfonycasts.com/screencast/blackfire).
Just after that, I decided to dig into PHPInsights to find ways to improve performances.

With this great tool, [Oliver](https://twitter.com/OliverNybroe) and I found some bottleneck, and we started to gain huge performances !

![First run of performances gain](https://user-images.githubusercontent.com/3168281/115996462-a89bb980-a5df-11eb-8d88-2caee35c66f7.png)

The next step was to add cached results.

When we run twice an analysis, all files are reanalyzed. What a boring process. As files doesn't change, the tool should keep the results.

Thanks to [symfony/cache](https://symfony.com/doc/current/components/cache.html), we can now store all issues details in cache, and avoid recheck a file if it's content doesn't change between two analysis.

![Cache usage](https://user-images.githubusercontent.com/3168281/81601081-ca9a2d00-93ca-11ea-9985-99980e2ad8d5.gif)

Finally, the most powerful tweak we do to improve performances is allow to use multiples CPU cores !

A new options is available in the  `phpinsights.php` configuration file : `threads`. Specify here how many core you want to use.
If you don't specify it, the tool is able to detect how many CPU core your computer has, and use them all.

See this example, with an `htop` profiling CPU core at the bottom :

![PHPInsights Parallelization](https://user-images.githubusercontent.com/3168281/115996108-47271b00-a5de-11eb-972b-ae6bc57681c6.gif)

Thanks to all theses improvements, we increase performance of 93% 🚀

![Final blackfire comparison](https://user-images.githubusercontent.com/3168281/115996469-adf90400-a5df-11eb-9221-a5b6c08475f9.png)

### Auto-fixer 🌟

As lot of Insights came from PHPCS or PHP-CS-Fixer, you can now fix them automatically.
Just append the  `--fix`  option, and 🎉. After the analysis report, you'll get what has been fixed.

If you don't want to get the full report, just launch `vendor/bin/phpinsights fix`.

![PHPInsights autofixer](https://user-images.githubusercontent.com/3168281/115998397-4e065b80-a5e7-11eb-9afd-c8fd3ac49d6f.gif)

### Better feedbacks on your CI 🤖

[@50bhan](https://twitter.com/50bhan) provide a way to specify multiple paths or files to analyze. This great feature allow you to use report only issues on changed files for a Pull Request with our GithubAction formatter :

```yaml
  - name: PHPInsight on new/modified files
    if: github.event_name == 'pull_request'
    run: |
        URL="https://api.github.com/repos/${GITHUB_REPOSITORY}/pulls/${{ github.event.pull_request.number }}/files"
        # Retrieve list of add/modified files
        FILES=$(curl -s -X GET -G $URL | jq -r '.[] |  select( .status == "added" or .status == "modified") | select(.filename|endswith(".php")) | .filename')
        # Launch phpinsights against theses files
        php vendor/bin/phpinsights analyse $FILES --ansi -v -n --format=github-action
```

![feedback on PRs](https://user-images.githubusercontent.com/3168281/115997313-f6fe8780-a5e2-11eb-9fc5-7e933339c6c7.png)


For peoples that don't use GithubAction but prefer Jenkins, [@dsamburskyi](https://github.com/dsamburskyi) improved the Checkstyle report, that allow the Warning NG plugin to correctly display it. Jenkins users, enjoy ✨

![Warning NG on Jenkins](https://user-images.githubusercontent.com/25210529/110533067-975c2380-80eb-11eb-8caf-bac106000d73.png)


Finally, for **Gitlab** users, [@guywarner](https://twitter.com/guywarner801) add a new formatter to display results for `codeclimate`.

```yaml
#.gitlab-ci.yml
insights:
  script:
    - vendor/bin/phpinsights -n --ansi --format=codeclimate > codeclimate-report.json
  artifacts:
    reports:
      codequality: codeclimate-report.json
```

### Dependencies update 🔧

In this version, PHPInsights is fully compatible with PHP8 and Composer2. We also drop abandonned packages and upgrade to latest version of [Slevomat/Coding-Standard](https://github.com/slevomat/coding-standard) and [PHP-CS-Fixer](https://cs.symfony.com/). Thank you [@50bhan](https://twitter.com/50bhan) for handling this 💪

Some Insights are already aware when you use PHP8 and are able to detect old fashion usage that could be refactored.
For example, in PHP8, you can use `::class` on every object instead calling the `get_class()` function.

![php8 aware](https://user-images.githubusercontent.com/3168281/115997977-68d7d080-a5e5-11eb-9f16-80bc9b9d4942.png)

### Real time monitor 📈

A new `--summary` option is now available. By running following code, you can get an real time monitor of state of your code
```bash
watch -c -b php vendor/bin/phpinsights --summary --ansi
```

<div data-controller="tweet-embed" data-tweet-embed-id-value="1387496703012507650">
<blockquote><p lang="en" dir="ltr">📈Do you want a Real-Time monitor of <a href="https://twitter.com/phpinsights?ref_src=twsrc%5Etfw">@phpinsights</a> while you have a coding session ? <br><br>This PR might be helpful 😉 <a href="https://t.co/BT1md5quyj">https://t.co/BT1md5quyj</a> <a href="https://t.co/2lrRNxmGWD">pic.twitter.com/2lrRNxmGWD</a></p>&mdash; JiBé Barth (@jibbarth) <a href="https://twitter.com/jibbarth/status/1387496703012507650?ref_src=twsrc%5Etfw">April 28, 2021</a></blockquote></div>

### And lots of few bugs fixed, thanks of the awesome community 🙌:

I strongly encourage you to browse the [full changelog](https://github.com/nunomaduro/phpinsights/blob/master/CHANGELOG.md#v200).

We hope you'll enjoy this new release 😊


## What's coming next ?

We've few ideas for the following releases.

We thought about integrate [Rector](https://getrector.org/), and [@50bhan](https://twitter.com/50bhan) already start working on this.

I would also love to quickly import [sets from PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/3.0/doc/ruleSets/index.rst#list-of-available-rule-sets) to automatically add batch of rules pre-configured.

Other idea ? Create an issue or send a Pull Request : https://github.com/nunomaduro/phpinsights
