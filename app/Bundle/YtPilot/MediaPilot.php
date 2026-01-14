<?php

namespace Mediatag\Bundle\YtPilot;

use Mediatag\Bundle\YtPilot\YtPilot;

use Mediatag\Bundle\YtPilot\DTO\DownloadResult;
use Mediatag\Bundle\YtPilot\DTO\FormatItem;
use Mediatag\Bundle\YtPilot\DTO\PlatformConfig;
use Mediatag\Bundle\YtPilot\DTO\SubtitleList;
use Mediatag\Bundle\YtPilot\Enums\ProxyProtocol;
use Mediatag\Bundle\YtPilot\Enums\TikTokApi;
use Mediatag\Bundle\YtPilot\Enums\TwitterApi;
use Mediatag\Bundle\YtPilot\Exceptions\MissingUrlException;
use Mediatag\Bundle\YtPilot\Services\Binary\BinaryLocatorService;
use Mediatag\Bundle\YtPilot\Services\Binary\FfmpegBinaryService;
use Mediatag\Bundle\YtPilot\Services\Binary\ManifestService;
use Mediatag\Bundle\YtPilot\Services\Binary\ReleaseResolverService;
use Mediatag\Bundle\YtPilot\Services\Binary\YtDlpBinaryService;
use Mediatag\Bundle\YtPilot\Services\Conversion\ConversionService;
use Mediatag\Bundle\YtPilot\Services\Filesystem\PathService;
use Mediatag\Bundle\YtPilot\Services\Http\DownloaderService;
use Mediatag\Bundle\YtPilot\Services\Metadata\MediaInfoService;
use Mediatag\Bundle\YtPilot\Services\Parsing\FormatsParserService;
use Mediatag\Bundle\YtPilot\Services\Parsing\SubtitlesParserService;
use Mediatag\Bundle\YtPilot\Services\Platform\PlatformService;
use Mediatag\Bundle\YtPilot\Services\Process\ProcessRunnerService;

class MediaPilot extends YtPilot
{
    private ?string $downloadArchive = null;
    private ?string $batchFile = null;

    public function downloadArchive(?string $filePath): self
    {
        $this->downloadArchive = $filePath;

        return $this;
    }
    public function url(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function batchFile(?string $filePath): self
    {
        $this->batchFile = $filePath;

        return $this;
    }

    /** @return list<string> */
    public function buildCommand(): array
    {
        $binary  = $this->locator->requireYtDlp($this->ytDlpPath);
        $command = [$binary];

        if (!$this->hasAnyTarget()) {
            $this->downloadVideo = true;
            $this->downloadAudio = true;
        }

        if ($this->formatSelector !== null) {
            $command[] = '-f';
            $command[] = $this->formatSelector;
        }

        if ($this->downloadArchive !== null) {
            $command[] = '--download-archive';
            $command[] = $this->downloadArchive;
        }
        if ($this->batchFile !== null) {
            $command[] = '-a';
            $command[] = $this->batchFile;
        }

        if ($this->outputTemplate !== null) {
            $command[] = '-o';
            $command[] = $this->outputTemplate;
        }

        if ($this->audioOnly) {
            $command[] = '-x';

            if ($this->audioFormat !== null) {
                $command[] = '--audio-format';
                $command[] = $this->audioFormat;
            }

            if ($this->audioQuality !== null) {
                $command[] = '--audio-quality';
                $command[] = $this->audioQuality;
            }
        }

        if ($this->downloadSubtitles) {
            $command[] = '--write-subs';

            if ($this->subtitleLanguages !== []) {
                $command[] = '--sub-langs';
                $command[] = implode(',', $this->subtitleLanguages);
            }

            if ($this->subtitleFormat !== null) {
                $command[] = '--sub-format';
                $command[] = $this->subtitleFormat;
            }
        }

        if ($this->downloadAutoSubtitles) {
            $command[] = '--write-auto-subs';

            if ($this->subtitleLanguages !== []) {
                $command[] = '--sub-langs';
                $command[] = implode(',', $this->subtitleLanguages);
            }
        }

        if ($this->downloadMetadata) {
            $command[] = '--write-info-json';
        }

        if ($this->downloadThumbnail) {
            $command[] = '--write-thumbnail';
        }

        if ($this->skipDownload) {
            $command[] = '--skip-download';
        }

        if ($this->simulate) {
            $command[] = '--simulate';
        }

        if ($this->overwrite) {
            $command[] = '--force-overwrites';
        }

        if ($this->cookiesFile !== null) {
            $command[] = '--cookies';
            $command[] = $this->cookiesFile;
        } elseif ($this->cookiesBrowser !== null) {
            $command[] = '--cookies-from-browser';
            $command[] = $this->buildBrowserCookieArg();
        } elseif ($this->noCookies) {
            $command[] = '--no-cookies';
        }

        if ($this->username !== null) {
            $command[] = '--username';
            $command[] = $this->username;
        }

        if ($this->password !== null) {
            $command[] = '--password';
            $command[] = $this->password;
        }

        if ($this->platformConfig !== null) {
            $command = [...$command, ...$this->platformConfig->toCommandArgs()];
        }

        foreach ($this->extractorArgs as $key => $value) {
            $command[] = '--extractor-args';
            $command[] = "{$key}={$value}";
        }

        if ($this->proxyUrl !== null) {
            $command[] = '--proxy';
            $command[] = $this->proxyUrl;
        } elseif ($this->noProxy) {
            $command[] = '--proxy';
            $command[] = '';
        }

        if ($this->geoVerificationProxy !== null) {
            $command[] = '--geo-verification-proxy';
            $command[] = $this->geoVerificationProxy;
        }

        if ($this->sourceAddress !== null) {
            $command[] = '--source-address';
            $command[] = $this->sourceAddress;
        }

        if ($this->forceIpv4) {
            $command[] = '--force-ipv4';
        }

        if ($this->forceIpv6) {
            $command[] = '--force-ipv6';
        }

        $ffmpegLocation = $this->resolveFfmpegLocation();
        if ($ffmpegLocation !== null) {
            $command[] = '--ffmpeg-location';
            $command[] = $ffmpegLocation;
        }

        $command[] = '--no-warnings';
        $command[] = '--newline';
        $command[] = $this->url;

        return $command;
    }

    private function requireUrl(): void
    {
        // if ($this->url === null || $this->url === '') {
        //     throw MissingUrlException::required();
        // }
    }

}
