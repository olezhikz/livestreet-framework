{**
 * Коллапс
 *
 * @param mixed   $text         Текст кнопки или массив с параметрами кнопки
 * @param mixed   $toggle       Кнопка со всеми атрибутами
 * @param string  $content      Содержание

 *}
 
{extends "component@button.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'text',
        'toggle',
        'content',
        'id'
    ]}
{/block}