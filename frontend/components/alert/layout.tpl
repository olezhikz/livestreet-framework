{**
 * Оповещение
 *
 * @param mixed   $title          Заголовок оповещения
 * @param mixed   $text           Текст оповещения
 *}

{extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'title'
        'text' 
    ]}
{/block}

