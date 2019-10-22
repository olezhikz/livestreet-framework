{**
 * Кнопка
 *
 *}
 
{extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'text',
        'disabled'
    ]}
{/block}
