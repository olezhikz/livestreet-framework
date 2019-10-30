{**
 * Хлебные крошки
 *
 * @param array   $items            Крошки
 *}

{extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'items'
    ]}
{/block}
