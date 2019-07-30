<?php

/**
 * Description of CssHtmlFilter
 *
 * @author oleg
 */
class JsHtmlFilter extends HtmlFilter implements Assetic\Filter\FilterInterface{
    //put your code here
    public function filterDump(\Assetic\Asset\AssetInterface $asset) {
        return '<script src="'.$asset->getTargetPath().'"></script>';
    }

    public function filterLoad(\Assetic\Asset\AssetInterface $asset) {
        
    }

}
