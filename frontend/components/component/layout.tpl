{**
 * Основной шаблон компонента от него наследуются все шаблоны компонентов
 
 * @param string  $mods = "success" Список модификторов основного блока (через пробел)
 * @param string  $popover          Всплывающий контент на элементе
 * @param string  $classes          Список классов основного блока (через пробел)
 * @param array   $attributes       Список атрибутов основного блока
 * @param string  $role             Вспомогательный атрибут role
 * @param string  $tag              Тег основного элемента
 *}
 
{block 'options'}
    {component_define_params params=[ 
        'attr',  
        'classes',
        'mods',
        'role',
        'popover',
        'tag'
    ]}
    
    {*    Функция смарти для отображения всплывающего элемента*}
    {function name="cpopover"}
        {if $popover}
            {if is_array($popover)}
                {component "popover" params=$popover}
            {else}
                {component "popover" content=$popover}
            {/if}
        {/if} 
    {/function}

{/block}

{block name="after_options"}
    
    {if $role}
        {$attr['role'] = $role}
    {/if}
    
{/block}


{block name="content"}{/block}

