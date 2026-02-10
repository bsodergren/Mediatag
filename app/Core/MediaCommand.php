<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core;

// use const PHP_OS;

use Closure;
use Doctrine\Migrations\Tools\Console\Command\DoctrineCommand;
use Mediatag\Core\MediaLogger;
use Mediatag\Core\MediaOptions;
use Mediatag\Core\Mediatag;
use Mediatag\Core\Traits\Command\CommandConfigure;
use Mediatag\Core\Traits\Command\CommandExecute;
use Mediatag\Core\Traits\Command\CommandHelper;
use Mediatag\Core\Traits\Command\CommandInit;
use Mediatag\Core\Traits\Command\CommandRun;
use Mediatag\Core\Traits\Command\SignalEvents;
use Mediatag\Locales\Lang;
use Mediatag\Modules\Display\ConsoleOutput;
use Mediatag\Modules\Executable\MediatagExec;
use Mediatag\Traits\MediaLibrary;
use Mediatag\Traits\Translate;
use Nette\Utils\Callback;
use SimpleCli\Traits\Name;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TypeError;
use UTM\Utilities\Option;

use function array_key_exists;
use function call_user_func;
use function count;
use function function_exists;
use function is_array;
use function is_int;
use function sprintf;

class MediaCommand extends DoctrineCommand
{
    use CommandConfigure;
    use CommandExecute;
    use CommandHelper;
    use CommandInit;
    use CommandRun;
    use Lang;
    use MediaLibrary;
    use SignalEvents;
    use Translate;

    public static $Console;

    // public static $CompletionHandlers = [];

    public const USE_LIBRARY = false;

    public const USE_SEARCH = true;

    public static $SingleCommand = false;

    public $command = [];

    private ?string $processTitle = null;

    private bool $ignoreValidationErrors = false;

    private ?Closure $code = null;

    public static function getAllInput()
    {
        $class = self::findClass('Mediatag');
        // utmdump($class);
    }
}
