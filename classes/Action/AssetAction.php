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
        
        $this->AddEventPreg('/^(.+)?$/', '/^(.+)?$/', '/^(.+)?$/', '/^(.+)?$/', ['EventIndex', 'index']);
    }

    public function Init() {

    }
    
    protected function EventIndex() {
        
        $aParams = [
            $this->sCurrentAction,
            $this->sCurrentEvent
        ];
        
        $aParams = array_merge( $aParams, $this->GetParams() );
        
        $aPathAsset = explode('/', \Config::Get('path.cache_assets.server'));
        
        $sNameAsset = array_pop($aParams);       
        
        print_r($aPathAsset);
        print_r($aParams);
        
        $this->Component_LoadAll();
        
        $this->Asset_Load();
        
        $assets = $this->Asset_GetAssetManager();
        
        if(in_array($sNameAsset, $assets->getNames())){
            print_r($assets->get($sNameAsset));
        }
        
    }

}
