{**
 * Поле ввода
 *   от этоо компонента должны наследоваться все компоненты полей формы
 *
 *  @param array   $validate  массив с параметрами валидации [ entity, field, scenario, msgError, msgSuccess]
 *  @param string  $name             Имя
 *  @param string  $id               Идентификатор
 *  @param string  $placeholder      По умолчанию
 *  @param string  $desc             Описание
 *  @param string  $value            Значение
 *  @param string  $label            Метка 
 *  @param string  $type
 *  @param string  $readonly
 *  @param string  $disabled
 *}
 
{extends "component@component.layout"}

{block 'options' append}
    {component_define_params params=[ 
        'validate',  
        'name', 
        'id', 
        'label', 
        'placeholder', 
        'desc', 
        'value', 
        'type',
        'readonly',
        'disabled'
    ]}
    
    {*
        Валидация
    *}
    {$validateRules = []}
    {if $validate}
        {if $validate.remote}
            {$validateRules['data-remote'] = "true"}
            {$validateRules['data-param-field'] = $validate.field|default:$name}
            {$validateRules['data-param-scenario'] = $validate.scenario|default:$validate.entity->_getValidateScenario()}
            
            {if is_object($validate.entity)}
                {$validateRules['data-param-entity'] = get_class($validate.entity)}
            {else}
                {$validateRules['data-param-entity'] = $validate.entity}
            {/if}
            {if $validate.url}
                {$validateRules['data-url'] = $validate.url}
            {/if}
            {if $validate.only_change}
                {$validateRules['data-only-change'] = "true"}
            {/if}
        {else}
            {if is_object($validate.entity)}
                {$validate.scenario = $validate.entity->_getValidateScenario()}
            {/if}
            {field_make_rules 
                entity      = $validate.entity 
                field       = $validate.field|default:$name
                scenario    = $validate.scenario
                assign      = "validateRules"}
        {/if}


    {/if}
    
    {if $placeholder}
        {$attr.placeholder = $placeholder}
    {/if}
    
    {if $name}
        {$attr.name = $name}
    {/if} 
    
    {if $readonly}
        {$attr.readonly = true}
    {/if}
    
    {if $disabled}
        {$attr.disabled = true}
    {/if}

    {if !$id}
        {$attr.id = "field{math equation='rand()'}"}
    {else}
        {$attr.id = $id}
    {/if}   
{/block}
