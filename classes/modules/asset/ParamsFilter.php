<?php

/**
 * Description of HtmlFilter
 *
 * @author oleg
 */
class ParamsFilter implements Assetic\Filter\FilterInterface{
    
    protected $aParams;

    public function __construct($aParams) {
        $this->aParams = $aParams;
    }
    
    public function getParams(){
        return $this->aParams;
    }

    public function filterDump(\Assetic\Asset\AssetInterface $asset) {
        
    }

    public function filterLoad(\Assetic\Asset\AssetInterface $asset) {
        
    }

}
