{**
 * Элемент Навигации
 *
 * @param bool      $active        Активный ли элемент
 * @param bool      $disabled      Отключен
 * @param string    $url      
 * @param string    $name          Уникальное имя
 * @param string    $badge         Значок
 * @param string    $icon          Иконка перед текстом
 * @param bool      $enable        Пказывать
 *}
 
{extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'active',
        'disabled',
        'url',
        'name',
        'icon',
        'badge',
        'enable',
        'text'
    ]}
   
{/block}


