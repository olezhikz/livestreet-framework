{**
 * Группа кнопок
 *
 * @param array  $items             Набор кнопок
 
 *}
 
{extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'items'
    ]}
    
{/block}


