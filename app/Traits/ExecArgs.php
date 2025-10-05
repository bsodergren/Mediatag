<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use Mediatag\Modules\Metatags\Artist;

trait ExecArgs
{
    public function getOptionArgs()
    {
        // utminfo(func_get_args());

        return $this->optionArgs;
    }

    public function addOptionArg($option)
    {
        // utminfo(func_get_args());

        $this->optionArgs[] = $option;
    }

    public function getCmdArgs($meta_tag, $meta_value)
    {
        // utminfo(func_get_args());

        if ($meta_tag == 'artist') {
            $this->addOptionArg('--rDNSatom');
            if ($meta_value != '') {
                $xml_value = Artist::ArtistXML($meta_value);
                $this->addOptionArg($xml_value);
                $this->addOptionArg('name=iTunMOVI');
                $this->addOptionArg('domain=com.apple.iTunes');
            } else {
                $this->addOptionArg('');
                $this->addOptionArg('name=');
                $this->addOptionArg('domain=');
            }

            $this->addOptionArg('--albumArtist=' . $meta_value);
        } elseif ($meta_tag == 'studio') {
            $this->addOptionArg('--album=' . $meta_value);
        } elseif ($meta_tag == 'network') {
            $this->addOptionArg('--TVNetwork=' . $meta_value);
        } else {
            $this->addOptionArg('--' . $meta_tag . '=' . $meta_value);
        }
    }
}
