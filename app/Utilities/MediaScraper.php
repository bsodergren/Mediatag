<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Utilities;

use const PHP_EOL;

use Mediatag\Core\MediaCache;
use Mediatag\Core\Mediatag;
use Symfony\Component\HttpClient\HttpClient;

class MediaScraper
{
    public static function getUrl($url)
    {
        $key     = md5($url);
        $content = MediaCache::get($key);
        // // utmdump([$url,$content]);
        if ($content == false) {
            $client   = HttpClient::create();
            $response = $client->request(
                'GET',
                $url
            );
            // // utmdump($response);

            $statusCode = $response->getStatusCode();
            // // utmdump([$url, $statusCode]);
            if ($statusCode == '200') {
                // $statusCode = 200
                $contentType = $response->getHeaders()['content-type'][0];
                // $contentType = 'application/json'
                // // utmdump([$url,$contentType]);

                $content = $response->getContent();

                // foreach ($client->stream($response) as $chunk) {
                //    Mediatag::$Console->writeln($chunk->getContent());

                // }
                // $content = '{"id":521583, "name":"symfony-docs", ...}'
                if ($contentType == 'text/plain;charset=UTF-8') {
                    // $streamInfo = $response->toStream();
                    $filename = $response->getHeaders()['content-disposition'][0];
                    preg_match('/filename="(.*)"/', $filename, $output_array);
                    $subtitleVTTFilename = __PLEX_HOME__ . '/Subtitles/' . $output_array[1];

                    $subtitleVTTFilename = str_replace('srt', 'vtt', $subtitleVTTFilename);
                    // // utmdump($subtitleVTTFilename);
                    if (! file_exists($subtitleVTTFilename)) {
                        $vtt = 'WEBVTT' . PHP_EOL . PHP_EOL . $content;
                        // Replace microseconds separator: 00,000 -> 00.000
                        $vtt = preg_replace('#(\d{2}),(\d{3})#', '${1}.${2}', $vtt);

                        // Write the .vtt file
                        file_put_contents($subtitleVTTFilename, $vtt);
                        MediaCache::put($key, $vtt);
                    }

                    return null;
                }
                if ($contentType == 'text/html; charset=utf-8') {
                    $content = $response->getContent();
                    // // utmdump($content);

                    return $content;
                }

                $content = $response->toArray();
                // // utmdump($content);
            } else {
                $content = '';
            }
            MediaCache::put($key, $content);
        }

        return $content;
        //  utmdd( $content['content']['strict_contents'][0]);
        // https://pornbox.com/contents/63004/subtitles/en
    }
}
