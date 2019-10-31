{**
 * Выпадаеющее меню
 *
 * @param mixed   $title          Заголовок оповещения
 * @param mixed   $text           Текст оповещения
 *}

{extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'items',
        'text',
        'disabled',
        'icon',
        'badge'
    ]}
{/block}
