{**
 * Аккордион
 *
 * @param array   $items       Элементы Аккордиона [ [text, content], ... ]

 *}
 
{extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'items',
        'activeItem'
    ]}
{/block}