{**
 * Кнопка
 *
 *}
 
{extends "component@component.abstract"}

{block 'options' append}
    {component_define_params params=[ 
        'text', 
        'disabled'
    ]}
{/block}
