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
    if (!isset($aParams['params']) or !is_array($aParams['params'])) {
        trigger_error("component_define_params: missing 'params' parameter", E_USER_WARNING);
        return;
    }
    
    /*
     * Приводим к ассоциативному виду 
     */
    $aDefineParams = [];
    foreach ($aParams['params'] as $key => $value) {
        if(is_integer($key)){
            $aDefineParams[$value] = null;
        }else{
            $aDefineParams[$key] = $value;
        }
    }
    
    /*
     * Параметры переданые компоненту
     */
    $aParams = $oSmarty->getTemplateVars('params');
    /*
     * Загружаем по ссылке все переменные шаблона из массива
     * для привязки переменных к значениям массива
     */
    foreach ($aDefineParams as $key => $value) {
        if(!array_key_exists($key, $aParams)){
            if(!is_null($value)){
                $oSmarty->assignByRef($key, $value);
            }
            continue;
        }
        $oSmarty->assignByRef($key, $aParams[$key]);
        
    }
    
    /*
     * Ключи предыдущих объявленных параметров
     * Сливапем с текущими ключами объявленных параметров
     */
    $oSmarty->append('define_params', array_keys($aDefineParams));

    return false;
}