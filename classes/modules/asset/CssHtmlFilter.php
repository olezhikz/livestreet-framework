<?php

/**
 * Description of CssHtmlFilter
 *
 * @author oleg
 */
class CssHtmlFilter implements Assetic\Filter\FilterInterface{
    
    
    //put your code here
    public function filterDump(\Assetic\Asset\AssetInterface $asset) {
        $asset->setContent('<link rel="stylesheet" type="text/css" href="'.$asset->getTargetPath().'" />');
    }

    public function filterLoad(\Assetic\Asset\AssetInterface $asset) {
        
    }

}
