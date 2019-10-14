{**
 * Основной шаблон компонента от него наследуются все шаблоны компонентов
 *
 *}
 
{block 'options'}
    {component_define_params params=[ 
        'attr',  
        'classes',
        'mods'
    ]}
{/block}

{block name="content"}{/block}

