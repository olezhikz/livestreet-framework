{**
 * Карточка
 *
 * @param mixed   $content         Содержание
 * @param mixed   $header          Заоловок
 * @param mixed   $footer          Подвал
 *}

{extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'header',
        'content',
        'footer'
    ]}
{/block}

