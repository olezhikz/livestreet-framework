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
 * Инициализирует параметры компонета
 *
 * @param   array $aParams
 * @param   Smarty $oSmarty
 * @return  string
 */
function smarty_function_component_define_params($aParams, &$oSmarty)
{
    if (isset($aParams['params'])) {
        if (is_array($aParams['params'])) {
            $aDefineParams = $aParams['params'];
        } 
    } else {
        trigger_error("component_define_params: missing 'params' parameter", E_USER_WARNING);
        return;
    }
    
    $aVars = $oSmarty->getTemplateVars('component_vars');
    
    foreach ($aDefineParams as $key => $mValue) {
        
        if(is_int($key) && isset($aVars[$mValue])){
            $oSmarty->assign($mValue, $aVars[$mValue]);
            continue;
        }
        
        if(!is_int($key)){
            if(isset($aVars[$key])){
                $oSmarty->assign($key, $aVars[$key]);
            }else{
                $oSmarty->assign($key, $mValue);
            }
        }
        
    }
    /*
     * Устанавливаем результирующий список параметров компонента
     */
    $aComponentParams = $oSmarty->getTemplateVars('component_params');
    $aDefineParams = array_merge(is_array($aComponentParams)?$aComponentParams:[], $aDefineParams);
    $oSmarty->assign('component_params', $aDefineParams);

    return false;
}