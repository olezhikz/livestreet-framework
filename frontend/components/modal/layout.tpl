{**
 * Модальное окно
 *
 * @param string  $header           Заголовок
 * @param string  $content          Контент
 * @param string  $footer           Подвал
 * 
 *}
 
 {extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'header',  
        'footer', 
        'id'        
    ]}
    
{/block}
