{**
 * Навигация
 *
 * @param array     $items
 * @param string    $activeItem
 * 
 *}
 
{extends "component@button.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'items', 
        'activeItem' 
    ]}
{/block}


