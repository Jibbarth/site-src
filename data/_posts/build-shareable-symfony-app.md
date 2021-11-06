---
title: "How to build a shareable Symfony App"
date: 2021-11-06
hero: https://images.unsplash.com/flagged/photo-1563536314719-2e812e896f50??crop=faces&&fit=crop&fm=jpg&h=300&ixid=MnwxfDB8MXxyYW5kb218fHx8fHx8fHwxNjE5OTUxOTcw&ixlib=rb-1.2.1&q=80&w=1600
state: published
---

Symfony is a powerfull framework that allow us to build complex applications. Many projects rely on it or it's components.

As a PHP developer, each time I have a new side project idea, I build it on Symfony.

Sometime, I put theses projects in public on Github, in case anyone discover it and want the same app.

For example, I developed few time ago [Slack Newsletter](https://github.com/Jibbarth/slacknewsletter), a symfony app that fetch all links shared in differents channels on slack, and that build a newsletter with all theses link inside each weeks.

![slack newsletter example](https://repository-images.githubusercontent.com/129633240/a2815d80-adbe-11ea-9379-b3b04705d572)

I reused this app for multiple slack, and something always bothered me. Each time I made an improvement on the base repository, fetching update on the fork can be a pain. Because, on the fork, I alter sometime the template, sometime the configuration, etc.

So, today, I had in my mind to find a way to easily build a Symfony app, and made it shareable.

The main repo would be the application itself, but others would just require the app as a dependency, as a bundle, so no fork to do.

As far as I see from others project, for such a case, they have two repo. The bundle itself, and another symfony project that require the bundle.

It could be the easiest way, but I don't want to manage two repository, even more when I work on a side project.  It has to be fun, not boring to jump between two repositories.

Recently, I discovered that there is a new Bundle structure in Symfony, to be clother to a Symfony Project Structure.

With this new structure, here is how your bundle can be organized :

```bash
AcmeAwesomeBundle
├── assets
├── config
├── public
├── src
│   └── AcmeAwesomeBundle.php
├── templates
├── tests
└── translations
```

Sound familiar, right ? 😏

Yep, it's the same as our symfony app.

You can find more information on this new structure [on symfony docs](https://symfony.com/doc/current/bundles/best_practices.html#directory-structure), [in the PR implementing it](https://github.com/symfony/symfony/pull/32845), or in this [demo bundle](https://github.com/yceruto/acme-bundle).

My idea is so to start from a symfony skeleton project, and code inside like a bundle. The boilerplate provided by the skeleton and recipes will made the app working, and once you distribute it through composer, it'll act as a bundle.

That's the point.

Interested ? The following part will dive into this concept, to transform a standard skeleton app into a standalone bundle shareable.

<hr />

To validate my idea, I created two application from the skeleton :

```bash
# ~/Projects
composer create-project symfony/skeleton main-app
composer create-project symfony/skeleton consumer-app
```

And start them :

```bash
# ~/Projects/main-app
symfony serve -d
# ~/Projects/consumer-app
symfony serve -d
```

Now, open your favorite IDE on the `main-app` folder.

First thing to do, edit the `composer.json` to add a name to our app/bundle, and mark its type as `symfony-bundle`.

```diff
# composer.json
{
-    "type": "project",
-    "license": "proprietary",
+    "name": "acme/my-app-bundle",
+    "type": "symfony-bundle",
+    "license": "MIT",
    "minimum-stability": "stable",
    "prefer-stable": true,
    ...
```

In the same time, change also the PSR4 declaration :

```diff
# composer.json
    "autoload": {
        "psr-4": {
-            "App\\": "src/"
+            "Acme\\MyAppBundle\\": "src/"
        }
    },
```

As we remove the App namespace, let's change it in the Kernel, and references to this Kernel into `bin/console` and `public/index.php`:

```php
# src/Kernel.php
<?php

namespace Acme\MyAppBundle;

// use ...

class Kernel extends BaseKernel
{
    //...
```

```php
# bin/console.php and public/index.php
<?php

use Acme\MyAppBundle\Kernel;
//...
```


Now, create a `AcmeMyAppBundle.php` beside the `Kernel`.

```php
# src/AcmeMyAppBundle.php
<?php

declare(strict_types=1);

namespace Acme\MyAppBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class AcmeMyAppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
		// If you need to add compiler pass, register them here
        //$container->addCompilerPass(new MyAwesomeCompilerPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
```

And register your bundle in `config/bundles.php`:

```php
# config/bundles.php
<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Acme\MyAppBundle\AcmeMyAppBundle::class => ['all' => true],
];
```

For the services and package configuration that I have to change, I decided to create a `config/bundle` folder to isolate them from the standalone app.

I also decided to use a `services.php` instead a yaml file, but it's up to you.

```php
# config/bundle/services.php
<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('Acme\MyAppBundle\\', '../../src/')
        ->exclude([
            '../../src/DependencyInjection/',
            '../../src/Entity/',
            '../../src/AcmeMyAppBundle.php',
            '../../src/Kernel.php',
        ]);
};
```

This file will contain all our services declarations for our app. The `config/services.yaml` is not anymore mandatory, but to avoid altering so much the Kernel, I decided to just remove the `App` declaration in it :

```diff
# config/services.yaml
parameters:

services:
    # default configuration for services in *this* file
    _defaults:
        autowire: true      # Automatically injects dependencies in your services.
        autoconfigure: true # Automatically registers your services as commands, event subscribers, etc.
-    App\:
-        resource: '../src/'
-        exclude:
-            - '../src/DependencyInjection/'
-            - '../src/Entity/'
-            - '../src/Kernel.php'
-            - '../src/Tests/'

```

But now, we need an Extension to load our services. Let's create it :

```php
<?php

declare(strict_types=1);

namespace Acme\MyAppBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class AcmeMyAppExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__, 2) . '/config/bundle'));

        $loader->load('services.php');
    }
}
```


Let's see how working with routing now.

Create a sample controller :

```php
# src/Controller/IndexController
<?php

declare(strict_types=1);

namespace Acme\MyAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return new Response('Hello from AcmeMyAppBundle');
    }
}
```

In a normal case, we add to declare an annotation routing in the app. The recipe when we require `doctine/annotation` add this file

```yaml
# config/routes/annotations.yaml
controllers:
    resource: ../../src/Controller/
    type: annotation
```

But as we are now in a bundle, let's create a shareable routing file. So let's remove the file `config/routes/annotations.yaml` to don't get disturbed by it, and create a routing file in `config/bundle`.

Here again, I decided to wrote it in php, but others formats work too.

```php
# config/bundle/routing.php
<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->import('@AcmeMyAppBundle/src/Controller/', 'annotation');
};

```

As same for the services, you now have to declare all your routing here.

But if we try to reach this page, the default page from symfony is remaining.
In effect, in Symfony app, it's the kernel that has the responsability to fetch the routes, and it's not configured to fetch our new file.

instead of altering the `Kernel.php`, require our routing in the dedicated folder like for any other bundles :

```yaml
# config/routes/acme_myapp.yaml
acme_myapp:
  resource: "@AcmeMyAppBundle/config/bundle/routing.php"
```

And VOILA : Our controller works as expected 💖

Now, we just have to build the side project we want, it's totally shareable as a bundle ✨

Don't believe me? Need a proof?

🤔 Okay. Remember the `consumer-app` ? Let's require our new bundle inside it.

First, as we don't push anything, configure the composer.json of the secondary project to fetch our "bundle".

```yaml
# ~/Projects/consumer-app/composer.json
    "repositories": [
        {
            "type": "path",
            "url": "../main/"
        }
    ],
```

And require it through `composer` :

```bash
# ~/Projects/consumer-app
composer require acme/my-app-bundle
```

As `symfony/flex` is enabled, our new bundle is automatically registered. Check `config/bundles.php`.

Import routing in this repository too:

```yaml
# config/routes/acme_myapp.yaml
acme_myapp:
  resource: "@AcmeMyAppBundle/config/bundle/routing.php"
  # you can give here also a prefix like /my-app if you want to have a site with multiple app-bundles ;)
  #prefix: '/my-app'

```

Try to reach your consume-app, the controller from your bundle is now responding.

<hr/>

With this method, I'm now able to produce some open-source apps that could be easily required by others. And others can directly contribute to the app to get thing improved, instead of altering directly the source code in a different fork that can't be sync with the main repo.

I just covered here the very basic, maybe I'll write later about how to prepend configuration of others bundles in main app, to directly dispatch it to others without them have to follow an upgrade process, altering configuration files.
A focus on entities could be done also, doctrine allowing now to execute migrations that are in bundles.

And now, I have some work to do to refact my existing open source app and transform them in bundles 😉

Thanks for reading,

![See ya later](https://media.giphy.com/media/dRvEZLV0ORAmHT1L5u/giphy.gif)
