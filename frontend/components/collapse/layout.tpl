{**
 * Коллапс
 *
 * @param mixed   $button       Текст кнопки или массив с параметрами кнопки
 * @param string  $content      Содержание

 *}
 
{extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'content',
        'button'
    ]}
{/block}