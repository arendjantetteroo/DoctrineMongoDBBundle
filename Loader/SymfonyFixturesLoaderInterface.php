<?php

declare(strict_types=1);

namespace Doctrine\Bundle\MongoDBBundle\Loader;

use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\FixturesCompilerPass;
use Doctrine\Bundle\MongoDBBundle\Fixture\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use LogicException;
use ReflectionClass;
use RuntimeException;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use function array_key_exists;
use function array_values;
use function get_class;
use function sprintf;

interface SymfonyFixturesLoaderInterface
{
    /**
     * @internal
     */
    public function addFixtures(array $fixtures) : void;


    public function addFixture(FixtureInterface $fixture) : void;

    /**
     * Returns the array of data fixtures to execute.
     *
     * @param string[] $groups
     *
     * @return FixtureInterface[]
     */
    public function getFixtures(array $groups = []) : array;
}
