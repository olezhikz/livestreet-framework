<?php

/**
 * Description of HtmlAsset
 *
 * @author oleg
 */
class RemoteAsset  extends \Assetic\Asset\BaseAsset{
    
    /**
     * Constructor.
     *
     * @param string  $sourceUrl    The source URL
     * @param array   $filters      An array of filters
     * @param Boolean $ignoreErrors
     * @param array   $vars
     *
     * @throws \InvalidArgumentException If the first argument is not an URL
     */
    public function __construct($sourceUrl, $filters = array(), $ignoreErrors = false, array $vars = array())
    {
        $this->targetPath = $sourceUrl;

        parent::__construct($filters, null, null, $vars);
    }

    public function getLastModified() {
        if (false !== @file_get_contents($this->sourceUrl, false, stream_context_create(array('http' => array('method' => 'HEAD'))))) {
            foreach ($http_response_header as $header) {
                if (0 === stripos($header, 'Last-Modified: ')) {
                    list(, $mtime) = explode(':', $header, 2);

                    return strtotime(trim($mtime));
                }
            }
        }
    }

    public function load(\Assetic\Filter\FilterInterface $additionalFilter = null) {
        
    }
}
