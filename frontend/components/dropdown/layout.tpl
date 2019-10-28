{**
 * Выпадаеющее меню
 *
 * @param mixed   $title          Заголовок оповещения
 * @param mixed   $text           Текст оповещения
 *}

{extends "component@button.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'items'
    ]}
{/block}
