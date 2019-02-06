<?php


namespace Doctrine\Bundle\MongoDBBundle\Command;

use Doctrine\Bundle\MongoDBBundle\Loader\SymfonyFixturesLoaderInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Loader\SymfonyFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\MongoDBExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Load data fixtures from bundles.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class LoadDataFixturesDoctrineODMCommand extends DoctrineODMCommand
{
    private $kernel;

    private $fixturesLoader;
    
    public function __construct(ManagerRegistry $registry = null, KernelInterface $kernel = null, SymfonyFixturesLoaderInterface $fixturesLoader)
    {
        parent::__construct($registry);

        $this->kernel = $kernel;
        $this->fixturesLoader = $fixturesLoader;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return parent::isEnabled() && class_exists(Loader::class);
    }

    protected function configure()
    {
        $this
            ->setName('doctrine:mongodb:fixtures:load')
            ->setDescription('Load data fixtures to your database.')
            ->addOption('append', null, InputOption::VALUE_NONE, 'Append the data fixtures instead of flushing the database first.')
            ->addOption('group', null, InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED, 'Only load fixtures that belong to this group')
            ->addOption('dm', null, InputOption::VALUE_REQUIRED, 'The document manager to use for this command.')
            ->setHelp(<<<EOT
The <info>doctrine:mongodb:fixtures:load</info> command loads data fixtures from your application:

  <info>./app/console doctrine:mongodb:fixtures:load</info>

If you want to append the fixtures instead of flushing the database first you can use the <info>--append</info> option:

  <info>./app/console doctrine:mongodb:fixtures:load --append</info>

Fixtures are services that are tagged with <comment>doctrine.fixture.odm</comment>.

To execute only fixtures that live in a certain group, use:

  <info>php %command.full_name%</info> <comment>--group=group1</comment>
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ui = new SymfonyStyle($input, $output);

        $dm = $this->getManagerRegistry()->getManager($input->getOption('dm'));

        if ($input->isInteractive() && !$input->getOption('append')) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Careful, database will be purged. Do you want to continue (y/N) ?', false);

            if (! $helper->ask($input, $output, $question)) {
                return;
            }
        }

        $groups   = $input->getOption('group');
        $fixtures = $this->fixturesLoader->getFixtures($groups);
        if (! $fixtures) {
            $message = 'Could not find any fixture services to load';

            if (! empty($groups)) {
                $message .= sprintf(' in the groups (%s)', implode(', ', $groups));
            }

            $ui->error($message . '.');

            return 1;
        }

        $purger = new MongoDBPurger($dm);
        $executor = new MongoDBExecutor($dm, $purger);
        $executor->setLogger(function($message) use ($output) {
            $output->writeln(sprintf('  <comment>></comment> <info>%s</info>', $message));
        });
        $executor->execute($fixtures, $input->getOption('append'));
    }

    private function getKernel()
    {
        if ($this->kernel === null) {
            $this->kernel = $this->container->get('kernel');
        }

        return $this->kernel;
    }
}
