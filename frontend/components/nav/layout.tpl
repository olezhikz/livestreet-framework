{**
 * Навигация
 *
 * @param array     $items
 * @param string    $activeItem
 * @param array     $itemsParams    Параметры элементов меню по умолчанию
 * @param string    $hook           Хук для установки параметров плагинами
 *}
 
{extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'items', 
        'activeItem',
        'itemsParams',
        'hook'
    ]}
    
    {* Получаем пункты установленные плагинами *}
    {if $hook}
        {hook run="nav_{$hook}" assign='hookItems' params=$hookParams items=$items array=true}
        {$items = ( $hookItems ) ? $hookItems : $items}
    {/if}

{/block}

