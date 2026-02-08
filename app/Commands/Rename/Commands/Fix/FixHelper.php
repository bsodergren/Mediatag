<?php

namespace Mediatag\Commands\Rename\Commands\Fix;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\DbMap;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\Filesystem\MediaFinder;
use Mediatag\Modules\TagBuilder\File\Reader as fileReader;
use Mediatag\Modules\TagBuilder\TagReader;
use Mediatag\Modules\VideoInfo\Section\VideoFileInfo;
use Mediatag\Utilities\Strings;
use Nette\Utils\FileSystem as nFileSystem;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Filesystem\Filesystem as SfSystem;
use Symfony\Component\Finder\Finder as SFinder;
use UTM\Utilities\Option;

use function array_key_exists;
use function count;
use function dirname;
use function is_array;

trait FixHelper {}
