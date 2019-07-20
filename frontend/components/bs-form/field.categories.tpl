{**
 * Select
 *
 * @param string  $readonly          Список классов основного блока (через пробел)
 * 
 *}
 
{extends "component@bs-form.field"}



{block name="field_options"}
    
    {component_define_params params=[ 'disabled', 'categories', 'categoriesSelected', 'params']}
    
    {$name = $name|default:$params.form_field}
    {$label = $label|default:$params.label}
    
    {if $custom}
        {$component = "custom-select"}
        {$type = ""}
    {/if}
    {if $disabled}
        {$attributes.disabled = true}
    {/if}
    
    {$selected = []}
    {foreach $categoriesSelected as $category}
        {$selected[] = $category->getId()}
    {/foreach}
    
    {if $size}
        {$classes ="{$classes} {$component}-{$size}"}
    {/if}
{/block}

{block name="field_input"}
    <select {if $params.validate_require}required{/if}
            class="{$component} {cmods name=$component mods=$bmods delimiter="-"} {$classes}" {cattr list=$attributes}>
        <option {if !$selected}selected{/if}>{$aLang.field.select.no_select}</option>
        {foreach $categories as $category}
            <option value="{$category.entity->getId()}"{if in_array($category.entity->getId(), $selected)}selected{/if}>{$category.entity->getTitle()}</option>
        {/foreach}
    </select>
{/block}
    
{block name="out_content"}
    {component "bs-form.group" 
        custom=$customfalse 
        classes=$classesGroup 
        bmods=$bmodsGroup 
        componentGroup=$componentGroup 
        content=$smarty.capture.content}
{/block}


