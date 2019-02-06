<?php

/*
 * This file is part of the Doctrine Fixtures Bundle
 *
 * The code was originally distributed inside the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 * (c) Doctrine Project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class FixturesCompilerPass implements CompilerPassInterface
{
    const FIXTURE_TAG = 'doctrine.fixture.odm';

    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('doctrine.mongodb.odm.fixtures.loader');
        $taggedServices = $container->findTaggedServiceIds(self::FIXTURE_TAG);

        foreach ($taggedServices as $serviceId => $tags) {
            $definition->addMethodCall('addFixture', [new Reference($serviceId)]);
        }
    }
}
