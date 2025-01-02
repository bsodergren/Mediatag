<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core\Helper;

use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Command\Command as SynCmd;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use UTM\Utilities\Option;

trait MediaExecute
{
    public static $optionArg = [];

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Mediatag::$IoStyle = new SymfonyStyle($input, $output);
        if (Option::istrue('trunc')) {
            Mediatag::$dbconn->truncate();

            return SynCmd::SUCCESS;
        }
        if (\count($input->getArguments()) > 0) {
            $cmdArgument = $input->getArgument(self::CMD_NAME);
            utmdump([$input->getArguments(), $cmdArgument]);
            if (null !== $cmdArgument) {
                self::$optionArg = array_merge(self::$optionArg, [$cmdArgument]);
            }
        }
        // $args = ;

        $class   = self::getProcessClass();
        $Process = new $class(...array_merge([$input, $output], self::$optionArg));
        $Process->process();

        return SynCmd::SUCCESS;
    }
}
