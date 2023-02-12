---
title: "[PHP-CS-Fixer] Configure any rule with except paths"
date: 2023-02-12
state: draft
---

> Based on a true story.

<p></p>
So you are hyped by new possibility of PHP8, and you start a new Symfony Project!
Thanks to maker-bundle, you create your first entities, and you get a beautiful class using PHP Attributes for doctrine.

Later, you want to add [PHP-CS-Fixer](https://cs.symfony.com/), to keep your code consistency.

Finally, you decide to add a rule that add `final` to almost every class, as you read [this article](https://ocramius.github.io/blog/when-to-declare-classes-final/).

And 💥! All your tests are now failing, the new shiny project is unusable.

After inspect changes, all your doctrine entities are now final, and it breaks doctrine internals.

---

The [documentation of that rule](https://cs.symfony.com/doc/rules/class_notation/final_class.html) explain:

> No exception and no configuration are intentional. Beside Doctrine entities and of course abstract classes, there is no single reason not to declare all classes final

So what the heck my doctrine entities are changed ?

Well, unfortunately, [for now](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/issues/5782), the tool only support Doctrine annotations, not Doctrine attributes.

Does it mean we have to migrate all our entities to `@ORM\Entity` instead of `#[ORM\Entity]` ?

Deeping into [source code](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/v3.14.4/src/Fixer/ClassNotation/FinalInternalClassFixer.php#L166), I found that we can avoid the change if we mark entity as final with the `@final` annotation.

```php
<?php

namespace App\Entity;

use App\Repository\AwesomeEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AwesomeEntityRepository::class)]
/** @final */
class AwesomeEntity
{
   // ...
```

For a quick workaround, it does the job. But it would be a lie. Do we want add lies in our codebase?

So I decided to write a custom PhpCsFixer rule that could do the job of FinalClassFixer, but configurable with a list of path where the rule should not apply.

The documentation has a part about that: https://cs.symfony.com/doc/custom_rules.html

```php
<?php
// ...
return (new PhpCsFixer\Config())
    // ...
    ->registerCustomFixers([
        new CustomerFixer1(),
        new CustomerFixer2(),
    ])
    ->setRules([
        // ...
        'YourVendorName/custome_rule' => true,
        'YourVendorName/custome_rule_2' => true,
    ])
;
```

### TLDR (give me the tip)

<div class="text-right">
<a href="https://gist.github.com/03e4f36ccd296dcb90d83af5707532ac"
   target="_blank" rel="nofollow,noopener"
   class="px-4 py-2 bg-gray-100 text-blue-500 border border-blue-500 hover:bg-blue-500 hover:text-white">
    <i class="fa fa-external-link-alt"></i>
        See also on gist.github.com
</a>
</div>
Create the two following files:

<div data-controller="gist-file" data-gist-file-github-user-value="Jibbarth" data-gist-file-article-value="03e4f36ccd296dcb90d83af5707532ac" data-gist-file-file-value="NotInProxyFixer.php"></div>
<div data-controller="gist-file" data-gist-file-github-user-value="Jibbarth" data-gist-file-article-value="03e4f36ccd296dcb90d83af5707532ac" data-gist-file-file-value="NoValidateFixerConfigurationResolver.php"></div>

Then, alter your `.php-cs-fixer.php` configuration to register that custom rule:

```php
<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->registerCustomFixers([
        new \App\Fixer\NotInProxyFixer()
    ])
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
        'Barth/not_in' => [
            'final_class' => ['except' => [
                'src/Entity',
            ]],
        ],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
```

### Additional notes

This custom rule should be able to handle any rule from PHP-CS-Fixer.
You just have to move it into `Barth/not_in` rule, and add the `except` array where you don't want the rule to be applied.

```diff
    ->setRules([
-        'final_class' => true,
+        'Barth/not_in' => [
+            'final_class' => ['except' => [
+                'src/Entity',
+            ]],
        ],
    ])
```

`final_class` was my main issue, but it could be also used with the `method_chaining_indentation` when we create a bundle with a configuration and keep the custom indentation:

```php
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('awesome_extension');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('test')
                    ->canBeDisabled()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
```
```diff
    ->setRules([
-        'method_chaining_indentation' => true,
+        'Barth/not_in' => [
+             'method_chaining_indentation' => ['except' => ['src/DependencyInjection']]
        ],
    ])
```


If your rule is configurable, you can also configure it beside the `except` key:

```php
// ...
    ->setRules([
        'Barth/not_in' => [
            'header_comment' => [
                'header' => 'This file belong to AwesomePackage',
                'except' => ['src/Entity']
            ],
        ],
    ])

```

It could also be boring to copy paste these file on multiple project.
I'll may create a composer package to distribute it easily.
