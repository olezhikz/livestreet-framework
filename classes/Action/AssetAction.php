<?php

/*
 * LiveStreet CMS
 * Copyright © 2013 OOO "ЛС-СОФТ"
 *
 * ------------------------------------------------------
 *
 * Official site: www.livestreetcms.com
 * Contact e-mail: end-fin@yandex.ru
 *
 * GNU General Public License, version 2:
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * ------------------------------------------------------
 *
 * @link https://vk.com/u_demidova
 * @copyright 2013 OOO "ЛС-СОФТ"
 * @author Oleg Demidov <end-fin@yandex.ru>
 *
 */
namespace LS\Action;
/**
 * Description of ActionAsset
 *
 * @author oleg
 */
class AssetAction extends \Action{

    protected function RegisterEvent() {
        
        $this->AddEventPreg('/^(.+)?$/', '/^(.+)?$/', '/^(.+)?$/', '/^(.+)?$/', '/^(.+)?$/', ['EventIndex', 'index']);
    }

    public function Init() {

    }
    
    protected function EventIndex() {
        /*
         * Выбираем все параметры из url
         */
        $sUrlAsset = str_replace(\Config::Get('path.cache_assets.web'), '', \Router::GetPathWebCurrent());
        
        $aParams = explode('/', trim($sUrlAsset, '/'));
        /*
         * Забираем у параметров значения оставляя только относительный путь
         */
        $sTypeAsset = array_shift($aParams);
        $sNameAsset = array_shift($aParams);
               
        /*
         * Находим ресурс
         */
        $this->Component_LoadAll();
        
        $this->Asset_Load();
        
        $assets = $this->Asset_GetAssetManager();
        
        if(!in_array($sNameAsset, $assets->getNames())){
            return $this->EventNotFound();
        }
        
        $asset = $assets->get($sNameAsset);
        
        $sPublicDir =  $asset->getParamsOne('publicDir');
        
        $sAssetPath = $sPublicDir . '/' . implode('/', $aParams);
        
        if(!file_exists($sAssetPath)){
            return $this->EventNotFound();
        }
        
        header('Content-Type: text/css');
        echo file_get_contents($sAssetPath);
    }

}
