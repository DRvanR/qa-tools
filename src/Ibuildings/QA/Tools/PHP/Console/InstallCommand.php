<?php
/**
 * @author Matthijs van den Bos <matthijs@vandenbos.org>
 * @copyright 2013 Matthijs van den Bos
 */

namespace Ibuildings\QA\Tools\PHP\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InstallCommand
 * @package Ibuildings\QA\Tools\PHP\Console
 *
 * @SuppressWarnings(PHPMD)
 */
class InstallCommand extends Command
{
    protected $settings = array();

    /** @var DialogHelper */
    protected $dialog;

    /** @var \Twig_Environment */
    protected $twig;

    protected function configure()
    {
        $this
            ->setName('install')
            ->setDescription('Setup for Ibuildings QA Tools for PHP')
            ->setHelp('Installs all tools and config files');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->enableDefaultSettings();

        $this->dialog = $this->getHelperSet()->get('dialog');

        $loader = new \Twig_Loader_Filesystem(PACKAGE_BASE_DIR . '/config-dist');
        $this->twig = new \Twig_Environment($loader);
        $filter = new \Twig_SimpleFilter(
            'bool',
            function ($value) {
                if ($value) {
                    return 'true';
                } else {
                    return 'false';
                }
            }
        );
        $this->twig->addFilter($filter);
    }


    private function enableDefaultSettings()
    {
        $this->settings['buildArtifactsPath'] = 'build/artifacts';
        $this->settings['enablePhpCsFixer'] = false;
        $this->settings['enablePhpMessDetector'] = false;
        $this->settings['enablePhpCopyPasteDetection'] = false;
        $this->settings['enablePhpCodeSniffer'] = false;

        return $this;
    }

    protected function configureBuildArtifactsPath(InputInterface $input, OutputInterface $output)
    {
        $this->settings['buildArtifactsPath'] = $this->dialog->askAndValidate(
            $output,
            "Where do you want to store the build artifacts? [".$this->settings['buildArtifactsPath']."] ",
            function ($data) {
                if (file_exists(BASE_DIR . '/' . $data)) {
                    return $data;
                }
                throw new \Exception("That path doesn't exist");
            },
            false,
            $this->settings['buildArtifactsPath']
        );

    }

    protected function configurePhpCsFixer(InputInterface $input, OutputInterface $output)
    {
        $this->settings['enablePhpCsFixer'] = $this->dialog->askConfirmation(
            $output,
            "Do you want to enable the PHP CS Fixer? [Y/n] ",
            true
        );

        if ($this->settings['enablePhpCsFixer']) {
            $this->settings['phpCsFixerLevel'] = $this->dialog->askAndValidate(
                $output,
                "What fixer level do you want to use? (psr0, psr1, psr2, all) [all] ",
                function ($data) {
                    if (in_array($data, array("psr0", "psr1", "psr2", "all"))) {
                        return $data;
                    }
                    throw new \Exception("That fixer level is not supported");
                },
                false,
                'all'
            );
        }
    }

    protected function configurePhpMessDetector(InputInterface $input, OutputInterface $output)
    {
        $this->settings['enablePhpMessDetector'] = $this->dialog->askConfirmation(
            $output,
            "Do you want to enable the PHP Mess Detector? [Y/n] ",
            true
        );
    }

    protected function configurePhpCodeSniffer(InputInterface $input, OutputInterface $output)
    {
        $this->settings['enablePhpCodeSniffer'] = $this->dialog->askConfirmation(
            $output,
            "Do you want to enable the PHP Code Sniffer? [Y/n] ",
            true
        );

        if ($this->settings['enablePhpCodeSniffer']) {
            $this->settings['phpCodeSnifferCodingStyle'] = $this->dialog->askAndValidate(
                $output,
                "Wich coding standard do you want to use? (PEAR, PHPCS, PSR1, PSR2, Squiz, Zend) [PSR2] ",
                function ($data) {
                    if (in_array($data, array("PEAR", "PHPCS", "PSR1", "PSR2", "Squiz", "Zend"))) {
                        return $data;
                    }
                    throw new \Exception("That coding style is not supported");
                },
                false,
                'PSR2'
            );
        }
    }

    protected function configurePhpCopyPasteDetection(InputInterface $input, OutputInterface $output)
    {
        $this->settings['enablePhpCopyPasteDetection'] = $this->dialog->askConfirmation(
            $output,
            "Do you want to enable PHP Copy Paste Detection? [Y/n] ",
            true
        );
    }

    protected function configurePhpSecurityChecker(InputInterface $input, OutputInterface $output)
    {
        $this->settings['enablePhpSecurityChecker'] = $this->dialog->askConfirmation(
            $output,
            "Do you want to enable the Sensiolabs Security Checker? [Y/n] ",
            true
        );
    }

    protected function configurePhpLint(InputInterface $input, OutputInterface $output)
    {
        $this->settings['enablePhpLint'] = $this->dialog->askConfirmation(
            $output,
            "Do you want to enable PHP Lint? [Y/n] ",
            true
        );
    }

    protected function configurePhpSrcPath(InputInterface $input, OutputInterface $output)
    {
        if ($this->settings['enablePhpCsFixer']
            || $this->settings['enablePhpMessDetector']
            || $this->settings['enablePhpCodeSniffer']
            || $this->settings['enablePhpCopyPasteDetection']
        ) {
            $this->settings['phpSrcPath'] = $this->dialog->askAndValidate(
                $output,
                "What is the path to the PHP source code? [src] ",
                function ($data) {
                    if (file_exists(BASE_DIR . '/' . $data)) {
                        return $data;
                    }
                    throw new \Exception("That path doesn't exist");
                },
                false,
                'src'
            );
        }
    }

    protected function configurePhpUnit(InputInterface $input, OutputInterface $output)
    {
        $this->settings['enablePhpUnit'] = $this->dialog->askConfirmation(
            $output,
            "Do you want to enable PHPunit tests? [Y/n] ",
            true
        );

        if ($this->settings['enablePhpUnit']) {
            $this->settings['phpTestsPath'] = $this->dialog->askAndValidate(
                $output,
                "What is the path to the PHPUnit tests? [tests] ",
                function ($data) {
                    if (file_exists(BASE_DIR . '/' . $data)) {
                        return $data;
                    }
                    throw new \Exception("That path doesn't exist");
                },
                false,
                'tests'
            );

            $this->settings['enablePhpUnitAutoload'] = $this->dialog->askConfirmation(
                $output,
                "Do you want to enable an autoload script for PHPUnit? [Y/n] ",
                true
            );

            if ($this->settings['enablePhpUnitAutoload']) {
                $this->settings['phpTestsAutoloadPath'] = $this->dialog->askAndValidate(
                    $output,
                    "What is the path to the autoload script for PHPUnit? [vendor/autoload.php] ",
                    function ($data) {
                        if (file_exists(BASE_DIR . '/' . $data)) {
                            return $data;
                        }
                        throw new \Exception("That path doesn't exist");
                    },
                    false,
                    'vendor/autoload.php'
                );
            }
        }
    }

    protected function writeAntBuildXml(InputInterface $input, OutputInterface $output)
    {
        if ($this->settings['enablePhpCsFixer']
            || $this->settings['enablePhpMessDetector']
            || $this->settings['enablePhpCopyPasteDetection']
            || $this->settings['enablePhpCodeSniffer']
            || $this->settings['enablePhpUnit']
            || $this->settings['enablePhpLint']
        ) {
            $fh = fopen(BASE_DIR . '/build.xml', 'w');
            fwrite(
                $fh,
                $this->twig->render(
                    'build.xml.dist',
                    $this->settings
                )
            );
            fclose($fh);
            $output->writeln("Ant build file written");
        }
    }

    protected function writePhpUnitXml(InputInterface $input, OutputInterface $output)
    {
        if ($this->settings['enablePhpUnit']) {
            $fh = fopen(BASE_DIR . '/phpunit.xml', 'w');
            fwrite(
                $fh,
                $this->twig->render(
                    'phpunit.xml.dist',
                    $this->settings
                )
            );
            fclose($fh);
            $output->writeln("Config file for PHPUnit written");
        }
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Starting setup of Ibuildings QA Tools for PHP");

        if (!$this->dialog->askConfirmation($output, "Do you want to continue? [Y/n] ", true)) {
            return;
        }

        $this->configurePhpLint($input, $output);
        $this->configurePhpCsFixer($input, $output);
        $this->configurePhpMessDetector($input, $output);
        $this->configurePhpCodeSniffer($input, $output);
        $this->configurePhpCopyPasteDetection($input, $output);
        $this->configurePhpSecurityChecker($input, $output);
        $this->configurePhpSrcPath($input, $output);
        $this->configurePhpUnit($input, $output);

        $this->configureBuildArtifactsPath($input, $output);

        $this->writePhpUnitXml($input, $output);
        $this->writeAntBuildXml($input, $output);
    }
}