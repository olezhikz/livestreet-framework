{**
 * Элемент выпадающего списка
 *
 * @param string  $url          
 * @param mixed   $text         
 * @param string  $badge        Значок
 * @param mixed   $icon         Иконка
 *}

{extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'text',
        'url',
        'badge',
        'icon'
    ]}
{/block}


