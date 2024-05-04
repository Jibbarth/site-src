<?php

declare(strict_types=1);

namespace App\Icon;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\Icons\Icon;
use Symfony\UX\Icons\IconRegistryInterface;

/**
 * This class is a decorator for the IconRegistryInterface, that allow to render icon once
 * and reuse it on the same page thanks to <use xlink:href="#icon-id"/> SVG tag.
 *
 * Check issue https://github.com/symfony/ux/issues/1800 for follow symfony integration
 * and maybe one day, remove this one
 */
#[AsDecorator('.ux_icons.icon_registry')]
final class IconAlreadyUsedRegistry implements IconRegistryInterface
{
    /**
     * @var array<string, string>
     */
    private array $alreadyUsed = [];

    public function __construct(
        private RequestStack $requestStack,
        private IconRegistryInterface $decorated,
    ) {}

    public function get(string $name): Icon
    {
        $icon = $this->decorated->get($name);

        $id = $icon::nameToId($name);
        // Generate an unique Id for each icon by Request
        // Needed as static-site does not restart kernel on each request,
        // and icon are considered as already used on new pages
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return $icon;
        }
        $id = md5(sprintf('%s-%s', $id, spl_object_id($request)));
        if (!\array_key_exists($id, $this->alreadyUsed)) {
            $this->alreadyUsed[$id] = $id;

            return $icon->withAttributes(['id' => $id]);
        }

        // Return a reusable icon, take only viewBox attribute to avoid rendering issues
        return new Icon('<use xlink:href="#' . $id . '"/>', [$icon->getAttributes()['viewBox']]);
    }
}
