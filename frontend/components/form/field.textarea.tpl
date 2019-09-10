{**
 * Текст
 *
 * @param string  $readonly          Список классов основного блока (через пробел)
 * 
 *}
 
{extends "component@form.field"}

{component_define_params params=[ 'readonly', 'rows', 'entity']}

{block name="field_input"}
    {$attributes=$attributes|array_diff_key:(['value']|array_flip)}
    <textarea {cattr list=$validateRules}
        class="{$component} {cmods name=$component mods=$bmods delimiter="-"} {$classes}" 
        {cattr list=$attributes} rows="{$rows|default:3}">{$value}</textarea>
        
{/block}
    


