<?php

/**
 * Description of CssHtmlFilter
 *
 * @author oleg
 */
class JsHtmlFilter implements Assetic\Filter\FilterInterface{
    
    
    //put your code here
    public function filterDump(\Assetic\Asset\AssetInterface $asset) {
        /*
         * Вытягиваем папраметры из фильтра параметров
         */
        $aParams = [];
        foreach ($asset->getFilters() as $filter) {
            if($filter instanceof ParamsFilter){
                $aParams = $filter->getParams();
            }
        }
        
        $asset->setContent('<script src="'. Config::Get('path.cache_assets.web'). '/'. $asset->getTargetPath(). '"></script>');
    }

    public function filterLoad(\Assetic\Asset\AssetInterface $asset) {
        
    }

}
