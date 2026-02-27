<?php

namespace Mediatag\Commands\Test;

use Mediatag\Bundle\YtPilot\YtPilot;
use Mediatag\Core\Mediatag;
use Nette\Utils\FileSystem;

trait ytdlptest
{
    public const __YT_DL_FORMAT__ = '%(uploader)s/%(title)s-%(id)s.%(ext)s';

    public $url = 'https://members.nubiles-porn.com/video/watch/234884/shes-so-good-i-have-to-share-s1e2';

    public function execDownload(): array
    {
        $playlist_file = '/home/bjorn/Plex/XXX/Playlists/list.txt';

        $archive     = __PLEX_PL_DIR__ . '/ids/testArchive.txt';
        $DownloadDir = __PLEX_DOWNLOAD__ . '/Studio';
        FileSystem::createDir($DownloadDir);

        // Download video with best quality
        $result = YtPilot::make()
            ->downloadArchive($archive)
            ->url($this->url)
            // ->batchFile($playlist_file)
            ->maxDownloads(1)
            // ->overwrite()
            // ->metadata()
            ->thumbnail()
            ->withCredentials(CONFIG['NUB_USERNAME'], CONFIG['NUB_PASSWORD'])
            ->output(self::__YT_DL_FORMAT__)
            ->outputPath($DownloadDir)
            ->onDownloading(function (int $percentage, float $downloaded, float $total): void {
                Mediatag::$Display->BarSection2->overwrite("Downloading: {$percentage}% ");
                if ($percentage >= 100) {
                    Mediatag::$output->writeln("✓ Download completed!\n");
                }
            })
            ->download();

        if ($result->success) {
            Mediatag::$output->writeln("✓ Download completed!\n");
            Mediatag::$output->writeln("Video: {$result->videoPath}\n");
            Mediatag::$output->writeln("Audio: {$result->audioPath}\n");
            Mediatag::$output->writeln("Thumbnail: {$result->thumbnailPath}\n");
            Mediatag::$output->writeln("Metadata: {$result->metadataPath}\n");
            Mediatag::$output->writeln('Subtitles: ' . implode(', ', $result->subtitlePaths) . "\n");
            Mediatag::$output->writeln('Total files: ' . count($result->downloadedFiles) . "\n");
        } else {
            Mediatag::$output->writeln("✗ Download failed\n");
            Mediatag::$output->writeln("Error: $result->output \n");
        }

        // $this->hasSubtitles();
        // $formats = $this->pilot->getAvailableFormats();
        // foreach ($formats as $format) {
        //     Mediatag::$output->writeln("ID: {$format->id}, Resolution: {$format->resolution}, Codec: {$format->vcodec}\n");
        // }
    }

    public function hasSubtitles()
    {
        $subtitles = $this->pilot->getAvailableSubtitles();
        Mediatag::$output->writeln('Languages: ' . implode(', ', $subtitles->getLanguages()) . "\n");

        return ! empty($subtitles);
    }
}
