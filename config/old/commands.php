<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Config;

use Mediatag\Commands\Clip\Command as ClipCommand;
use Mediatag\Commands\Create\Command as CreateCommand;
use Mediatag\Commands\Db\Command as DBCommand;
use Mediatag\Commands\Download\Command as DlCommand;
use Mediatag\Commands\Gallery\Command as GalleryCommand;
use Mediatag\Commands\Map\Command as MapCommand;
use Mediatag\Commands\Playlist\Command as PlaylistCommand;
use Mediatag\Commands\Rename\Command as RenameCommand;
use Mediatag\Commands\Show\Command as ShowCommand;
use Mediatag\Commands\Test\Command as TestCommand;
use Mediatag\Commands\Update\Command as UpdateCommand;
// %%NEW_USE%%

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

return new FactoryCommandLoader([

    // 'app:heavy' => static fn(): Command => new HeavyCommand(),
    'create'   => static fn (): MediaCommand => new CreateCommand,
    'playlist' => static fn (): MediaCommand => new PlaylistCommand,
    'download' => static fn (): MediaCommand => new DlCommand,

    'map'      => static fn (): MediaCommand => new MapCommand,
    'test'     => static fn (): MediaCommand => new TestCommand,
    'update'   => static fn (): MediaCommand => new UpdateCommand,
    'show'     => static fn (): MediaCommand => new ShowCommand,
    'db'       => static fn (): MediaCommand => new DBCommand,
    'gallery'  => static fn (): MediaCommand => new GalleryCommand,
    'rename'   => static fn (): MediaCommand => new RenameCommand,
    'clip'     => static fn (): MediaCommand => new ClipCommand,

    // %%NEW_COMMAND%%
]);
