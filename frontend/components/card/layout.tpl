{**
 * Карточка
 *
 * @param mixed   $content         Содержание
 * @param mixed   $title           Текст оповещения
 *}

{extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'title'
        'text' 
    ]}
{/block}

