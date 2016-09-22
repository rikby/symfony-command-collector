<?php
namespace Rikby\SymfonyConsole\CommandCollector;

/**
 * Class Collector
 *
 * This class can find commands by paths and merge them into a file
 * This script will try to find files [COMMAND_NAME]-app-include.php in provided paths
 *
 * File [COMMAND_NAME]-app-include.php should contain code which will add a command into console application.
 *   $app->add(new CoolCommand());
 *
 * @package Rikby\SymfonyConsole\CommandCollector
 * @see     CollectorTrait
 */
class Collector
{
    use CollectorTrait;
}
