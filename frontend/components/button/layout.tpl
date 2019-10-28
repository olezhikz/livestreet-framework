{**
 * Кнопка
 *
 * @param string  $text             Текст кнопки
 * @param string  $disabled         Отключена ли кнопка. Атрибут disabled
 * @param string  $icon             Иконка перед текстом
 * @param string  $tag = 'button'   Тег элемента кнопки
 * @param string  $form             Id формы которую нужно отправить если кнопка вне формы
 * @param string  $badge            Значок
 *}
 
{extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'text',
        'disabled',
        'icon',
        'tag',
        'form',
        'url',
        'badge'
    ]}
    
    {$tag = $tag|default:"button"}
    
    {$role = $role|default:"button"}
    
{/block}

{block "after_options" append}
    
    {if $form}
        {$attr['form'] = $form}
    {/if}
    
    {if $url}
        {$tag = 'a'}
        {$attr['href'] = $url}
    {/if}
    
{/block}
