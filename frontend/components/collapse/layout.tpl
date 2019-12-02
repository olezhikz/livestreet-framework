{**
 * Коллапс
 *
 * @param mixed   $text         Текст кнопки или массив с параметрами кнопки
 * @param mixed   $toggle       Кнопка со всеми атрибутами
 * @param string  $content      Содержание

 *}
 
{extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'content',
        'button'
    ]}
{/block}
