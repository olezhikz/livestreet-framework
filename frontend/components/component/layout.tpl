{**
 * Основной шаблон компонента от него наследуются все шаблоны компонентов
 *
 *}
 
{block 'options'}
    {component_define_params params=[ 
        'attributes',  
        'classes',
        'mods',
        'popover'
    ]}
{/block}

{block name="content"}{/block}

