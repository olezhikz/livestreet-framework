{**
 * Карусель
 *
 * @param array    $items       Элементы
 *}
 
{extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'items'
    ]}
{/block}