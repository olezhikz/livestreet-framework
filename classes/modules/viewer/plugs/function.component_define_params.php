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
     * Прибавляем полезные параметры компонента
     */
    $aDefineParams = [ 'component' => null, 'template' => null];
    /*
     * Приводим к ассоциативному виду 
     */
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
    $aVars = $oSmarty->getTemplateVars('vars');
    if(!$aVars){
        $aVars = [];
    }
    
    /**
     * Формируем параметры
     */
    $aParams = [];
    foreach ($aDefineParams as $key => $value) {
        if(!array_key_exists($key, $aVars)){
            if(!is_null($value)){
                $aParams[$key] = $value;
            }
            continue;
        }
        $aParams[$key] = $aVars[$key];
        
    }
    /*
     * Добавляем все параметры в шаблон
     */
    $oSmarty->append('params', $aParams, true);
    /*
     * Загружаем по ссылке все переменные шаблона из массива
     * для привязки переменных к значениям массива
     */
    foreach ($aParams as $key => &$value) 
    {
        $oSmarty->assign($key, $value);
    }    
    /*
     * Ключи предыдущих объявленных параметров
     * Сливапем с текущими ключами объявленных параметров
     */
    foreach (array_keys($aDefineParams) as $value) 
    {
        $oSmarty->append('define_params', $value);
    }
    

    return false;
}