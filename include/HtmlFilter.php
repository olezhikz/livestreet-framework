<?php

/**
 * Description of HtmlFilter
 *
 * @author oleg
 */
class HtmlFilter{
    
    protected $aParams;

    public function __construct($aParams) {
        $this->aParams = $aParams;
    }
    
    public function getParams(){
        return $this->aParams;
    }

}
