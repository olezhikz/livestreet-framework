<?php
/*
 * LiveStreet CMS
 * Copyright © 2013 OOO "ЛС-СОФТ"
 *
 * ------------------------------------------------------
 *
 * Official site: www.livestreetcms.com
 * Contact e-mail: office@livestreetcms.com
 *
 * GNU General Public License, version 2:
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * ------------------------------------------------------
 *
 * @link http://www.livestreetcms.com
 * @copyright 2013 OOO "ЛС-СОФТ"
 * @author Maxim Mzhelskiy <rus.engine@gmail.com>
 *
 */

/**
 * Плагин для смарти
 * Выводит HTML ресурса
 *
 * @author  Denis Shakhov
 * @param   array $aParams
 * @return  string
 */
function smarty_function_asset( $aParams )
{
    if (empty($aParams['name'])) {
        trigger_error("smarty_function_asset: missing 'name' parametr", E_USER_WARNING);
        return;
    }
    
    if (empty($aParams['type'])) {
        trigger_error("smarty_function_asset: missing 'type' parametr", E_USER_WARNING);
        return;
    }
    
    if (!empty($aParams['path'])) {
        LS::E()->Asset_AddFromConfig( [
                $aParams['type'] => [
                    $aParams['name'] => array(
                        'file' => $aParams['path'], 
                        'loader' => $aParams['loader'] ? $aParams['loader'] : '/LS/Modle/Asset/Loader/FileLoader',
                        'filters' => $aParams['filters'] ? $aParams['filters'] : [],
                        'attr' => $aParams['attr'] ? $aParams['attr'] : []
                    ),
                ]
            ]
        );
    }
    
    $assets = LS::E()->Asset_CreateAsset([$aParams['name']]);

    return LS::E()->Asset_Build($aParams['type'], $assets);
    
    
}