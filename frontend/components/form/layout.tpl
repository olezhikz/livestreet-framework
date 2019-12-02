{**
 * Форма
 *
 * @param string   $action       Аттрибут action
 * @param string   $method       Аттрибут method
 * @param mixed    $validate     Параметры валидации
 * @param string   $name         Аттрибут name
 * @param string   $content      Содержание
 * @param string   $enctype      Аттрибут enctype
 * @param string   $items        Поля разбитые в массив
 *}
 
{extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'content',
        'action',
        'method',
        'validate',
        'name',
        'enctype',
        'items'
    ]}
     
{/block}
