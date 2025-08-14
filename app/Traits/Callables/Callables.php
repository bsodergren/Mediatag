<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits\Callables;

/*
 * Command like Metatag writer for video files.
 */

use Nette\Utils\Strings;

trait Callables
{
    // public function parseArchive($line)
    // {
    //     $key = Strings::after($line, ' ');
    //     if (!\array_key_exists($key, $this->json_Array)) {
    //         return $line;
    //     }

    //     return false;
    // }

    // public function filemap($line)
    // {
    //     if ('' != $line) {
    //         $ph_id = Strings::after($line, '=');
    //         if (str_contains($ph_id, '&')) {
    //             $ph_id = Strings::before($ph_id, '&');
    //         }
    //         $file_map[$ph_id] = [
    //             'url' => Strings::after($line, '-> '),
    //             'old' => Strings::before($line, ' ->'),
    //         ];

    //         return $file_map;
    //     }

    //     return false;
    // }

    // public function getpremiumListIds($line)
    // {
    //     $ph_id = Strings::after($line, '=');
    //     if (str_contains($ph_id, '&')) {
    //         $ph_id = Strings::before($ph_id, '&');
    //     }

    //     return $ph_id;
    // }

    // public function compactPlaylist($line)
    // {
    //     $ph_id = Strings::after($line, '=');
    //     if (str_contains($ph_id, '&')) {
    //         $ph_id = Strings::before($ph_id, '&');
    //     }
    //     if (null === $ph_id) {
    //         $ph_id = Strings::after($line, 'watch/');
    //         if (null !== $ph_id) {
    //             $ph_id = Strings::before($ph_id, '/');
    //         }
    //     }
    //     // utmdd([$line,$ph_id]);
    //     if (!\in_array($ph_id, $this->ids)) {
    //         if (str_contains($line, 'view_video.php')) {
    //             return $line;
    //         }
    //         if (str_contains($line, 'watch')) {
    //             return $line;
    //         }
    //     }

    //     return false;
    // }

    // public function toList($line)
    // {
    //     // utminfo(func_get_args());

    //     if ('' != $line) {
    //         $Replacement = $line;
    //         $match       = $line;
    //         if (str_contains($line, ':')) {
    //             $match       = Strings::before($line, ':');
    //             $Replacement = Strings::after($line, ':');
    //             if ('' == $Replacement) {
    //                 $Replacement = null;
    //             }
    //         }
    //         $key = strtolower($match);
    //         $key = str_replace(' ', '_', $key);
    //         $key = str_replace('+', '', $key);
    //         $key = str_replace('(', '', $key);
    //         $key = str_replace(')', '', $key);

    //         return [$key => $Replacement];
    //     }

    //     return false;
    // }

    // public function toArray($line)
    // {
    //     // utminfo(func_get_args());

    //     if ('' != $line) {
    //         $Replacement = null;
    //         $match       = $line;
    //         if (str_contains($line, ':')) {
    //             $match       = Strings::before($line, ':');
    //             $Replacement = Strings::after($line, ':');
    //         }
    //         $key = strtolower($match);
    //         $key = str_replace(' ', '_', $key);
    //         $key = str_replace('+', '', $key);
    //         $key = str_replace('(', '', $key);
    //         $key = str_replace(')', '', $key);

    //         return [$key => $Replacement];
    //     }

    //     return false;
    // }
}
