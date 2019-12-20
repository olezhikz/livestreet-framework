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
 * @author Oleg Demidov
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
     * Берем все переданные компоненту параметры
     */
    $aVars = $oSmarty->getTemplateVars('_params');
    if(!is_array($aVars)){
        $aVars = [];
    }
    /*
     * Приводим к ассоциативному массиву
     */ 
    foreach ($aParams['params'] as $key => $value) {
        if(is_integer($key)){
            $aParams['params'][$value] = null;
            unset($aParams['params'][$key]);
        }
    }
    /*
     * Получаем массив параметров по пересечению с обьявленными
     */
    $aVars = array_intersect_key($aVars, $aParams['params']);
    /*
     * Добавляем значения по умолчанию
     */
    $aVars = array_merge($aParams['params'], $aVars);
    /*
     * Загружаем ссылки
     */
    $oSmarty->appendByRef('params', $aVars, true);
    
    foreach ($aVars as $key => &$value) {
        $oSmarty->assignByRef($key, $value);
    }
    /*
     * Сливаем с текущими ключами объявленных параметров
     */
    foreach ($aParams['params'] as $sDefKey => $val) 
    {
        $oSmarty->append('define_params', $sDefKey);
    }
    

    return false;
}