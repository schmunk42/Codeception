<?php

namespace Codeception\Command;

use Codeception\Lib\Generator\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Creates default config, tests directory and sample suites for current project. Use this command to start building a test suite.
 *
 * By default it will create 3 suites **acceptance**, **functional**, and **unit**.
 *
 * * `codecept bootstrap` - creates `tests` dir and `codeception.yml` in current dir.
 * * `codecept bootstrap --empty` - creates `tests` dir without suites
 * * `codecept bootstrap --namespace Frontend` - creates tests, and use `Frontend` namespace for actor classes and helpers.
 * * `codecept bootstrap --actor Wizard` - sets actor as Wizard, to have `TestWizard` actor in tests.
 * * `codecept bootstrap path/to/the/project` - provide different path to a project, where tests should be placed
 *
 */
class Bootstrap extends Command
{
    // defaults
    protected $namespace = '';
    protected $actorSuffix = 'Tester';
    protected $supportDir = 'tests/_support';
    protected $logDir = 'tests/_output';
    protected $dataDir = 'tests/_data';
    protected $envsDir = 'tests/_envs';

    protected function configure()
    {
        $this->setDefinition(
            [
                new InputArgument('path', InputArgument::OPTIONAL, 'custom installation path', '.'),
                new InputOption('namespace', 'ns', InputOption::VALUE_OPTIONAL, 'Namespace to add for actor classes and helpers'),
                new InputOption('actor', 'a', InputOption::VALUE_OPTIONAL, 'Custom actor instead of Tester'),
                new InputOption('empty', 'e', InputOption::VALUE_NONE, 'Don\'t create standard suites')
            ]
        );
    }

    public function getDescription()
    {
        return "Creates default test suites and generates all requires files";
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('namespace')) {
            $this->namespace = trim($input->getOption('namespace'), '\\') . '\\';
        }

        if ($input->getOption('actor')) {
            $this->actorSuffix = $input->getOption('actor');
        }

        $path = $input->getArgument('path');

        if (!is_dir($path)) {
            $output->writeln("<error>\nDirectory '$path' does not exist\n</error>");
            return;
        }

        $realpath = realpath($path);
        chdir($path);

        if (file_exists('codeception.yml')) {
            $output->writeln("<error>\nProject is already initialized in '$path'\n</error>");
            return;
        }

        $output->writeln(
            "<fg=white;bg=magenta>Initializing Codeception in " . $realpath . "</fg=white;bg=magenta>\n"
        );

        $this->createGlobalConfig();
        $output->writeln("File codeception.yml created       <- global configuration");
        $this->createDirs();

        if (!$input->getOption('empty')) {
            $this->createUnitSuite();
            $output->writeln("tests/unit created                 <- unit tests");
            $output->writeln("tests/unit.suite.yml written       <- unit tests suite configuration");
            $this->createFunctionalSuite();
            $output->writeln("tests/functional created           <- functional tests");
            $output->writeln("tests/functional.suite.yml written <- functional tests suite configuration");
            $this->createAcceptanceSuite();
            $output->writeln("tests/acceptance created           <- acceptance tests");
            $output->writeln("tests/acceptance.suite.yml written <- acceptance tests suite configuration");
        }
        $this->ignoreFolderContent('tests/_output');

        file_put_contents('tests/_bootstrap.php', "<?php\n// This is global bootstrap for autoloading\n");
        $output->writeln("tests/_bootstrap.php written <- global bootstrap file");

        $output->writeln("<info>Building initial {$this->actorSuffix} classes</info>");
        $this->getApplication()->find('build')->run(
            new ArrayInput(['command' => 'build']),
            $output
        );

        $output->writeln("<info>\nBootstrap is done. Check out " . $realpath . "/tests directory</info>");
    }

    public function createGlobalConfig()
    {
        $basicConfig = [
            'actor'    => $this->actorSuffix,
            'paths'    => [
                'tests'   => 'tests',
                'log'     => $this->logDir,
                'data'    => $this->dataDir,
                'support' => $this->supportDir,
                'envs'    => $this->envsDir,
            ],
            'settings' => [
                'bootstrap'    => '_bootstrap.php',
                'colors'       => (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN'),
                'memory_limit' => '1024M'
            ],
            'modules'  => [
                'config' => [
                    'Db' => [
                        'dsn'      => '',
                        'user'     => '',
                        'password' => '',
                        'dump'     => 'tests/_data/dump.sql'
                    ]
                ]
            ]
        ];

        $str = Yaml::dump($basicConfig, 4);
        if ($this->namespace) {
            $str = "namespace: {$this->namespace}\n" . $str;
        }
        file_put_contents('codeception.yml', $str);
    }

    protected function createFunctionalSuite($actor = 'Functional')
    {
        $suiteConfig = [
            'class_name' => $actor . $this->actorSuffix,
            'modules'    => ['enabled' => ['Filesystem', "\\{$this->namespace}Helper\\$actor"]],
        ];

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for functional (integration) tests.\n";
        $str .= "# emulate web requests and make application process them.\n";
        $str .= "# Include one of framework modules (Symfony2, Yii2, Laravel4) to use it.\n\n";
        $str .= Yaml::dump($suiteConfig, 2);
        $this->createSuite('functional', $actor, $str);
    }

    protected function createAcceptanceSuite($actor = 'Acceptance')
    {
        $suiteConfig = [
            'class_name' => $actor . $this->actorSuffix,
            'modules'    => [
                'enabled' => ['PhpBrowser', "\\{$this->namespace}Helper\\$actor"],
                'config'  => [
                    'PhpBrowser' => [
                        'url' => 'http://localhost/myapp/'
                    ],
                ]
            ],
        ];

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for acceptance tests.\n";
        $str .= "# perform tests in browser using the WebDriver or PhpBrowser.\n";
        $str .= "# If you need both WebDriver and PHPBrowser tests - create a separate suite.\n\n";

        $str .= Yaml::dump($suiteConfig, 5);
        $this->createSuite('acceptance', $actor, $str);
    }

    protected function createUnitSuite($actor = 'Unit')
    {
        $suiteConfig = [
            'class_name' => $actor . $this->actorSuffix,
            'modules'    => ['enabled' => ['Asserts', "\\{$this->namespace}Helper\\$actor"]],
        ];

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for unit (internal) tests.\n";
        $str .= Yaml::dump($suiteConfig, 2);

        $this->createSuite('unit', $actor, $str);
    }

    protected function createSuite($suite, $actor, $config)
    {
        @mkdir("tests/$suite");
        file_put_contents(
            "tests/$suite/_bootstrap.php",
            "<?php\n// Here you can initialize variables that will be available to your tests\n"
        );
        @mkdir($this->supportDir . DIRECTORY_SEPARATOR . "Helper");
        file_put_contents(
            $this->supportDir . DIRECTORY_SEPARATOR . "Helper" . DIRECTORY_SEPARATOR . "$actor.php",
            (new Helper($actor, rtrim($this->namespace, '\\')))->produce()
        );
        file_put_contents("tests/$suite.suite.yml", $config);
    }

    protected function ignoreFolderContent($path)
    {
        if (file_exists('.gitignore')) {
            file_put_contents("{$path}/.gitignore", "*\n!.gitignore");
        }
    }

    protected function createDirs()
    {
        @mkdir('tests');
        @mkdir($this->logDir);
        @mkdir($this->dataDir);
        @mkdir($this->supportDir);
        @mkdir($this->envsDir);
        file_put_contents($this->dataDir . '/dump.sql', '/* Replace this file with actual dump of your database */');
    }

}
