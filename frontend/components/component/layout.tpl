{**
 * Основной шаблон компонента от него наследуются все шаблоны компонентов
 
 * @param string  $mods = "success" Список модификторов основного блока (через пробел)
 * @param string  $classes          Список классов основного блока (через пробел)
 * @param array   $attributes       Список атрибутов основного блока
 * @param string  $role             Вспомогательный атрибут role
 * @param string  $tag              Тег основного элемента
 *}
 
{block name="before_options"}{/block}
 
{block 'options'}
    {component_define_params params=[ 
        'attr',  
        'classes',
        'mods',
        'role',
        'tag'
    ]}
    
{/block}

{block name="after_options"}
    
    {if $role}
        {$attr['role'] = $role}
    {/if}
    
{/block}

{strip}
    {block name="before_content"}{/block}

    {block name="content"}{/block}

    {block name="after_content"}{/block}
{/strip}
