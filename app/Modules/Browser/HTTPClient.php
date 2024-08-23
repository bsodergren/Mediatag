<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Browser;

use Mediatag\Core\Mediatag;


use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\Response;

class HTTPClient extends AbstractBrowser
{
    protected function doRequest($request): Response
    {
        utminfo();

        // ... convert request into a response

        return new Response($content, $status, $headers);
    }
}
