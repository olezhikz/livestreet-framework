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
 * @param   array $params
 * @return  string
 */
function smarty_function_asset( $params )
{
    if ( ! $params['path'] ) return '';
    
    $params['type'] = $params['type'] ? $params['type'] : 'js';
    $params['name'] = $params['name'] ? $params['name'] : pathinfo($params['path'], PATHINFO_FILENAME);
    
    Ls::$app->Asset_AddFromConfig( [
            $params['type'] => [
                $params['name'] => array(
                    'file' => $params['path'], 
                    'loader' => $params['loader'] ? $params['loader'] : '/LS/Modle/Asset/Loader/FileLoader',
                    'filters' => $params['filters'] ? $params['filters'] : [],
                    'attr' => $params['attr'] ? $params['attr'] : []
                ),
            ]
        ]
    );
    
    if($params['html']){
        $assets = Ls::$app->Asset_CreateAsset($params['name']);

        return Ls::$app->Asset_Build($params['type'], $assets);
    }else{
        Ls::$app->Asset_GetWebPath($params['name']);
    }
    
}