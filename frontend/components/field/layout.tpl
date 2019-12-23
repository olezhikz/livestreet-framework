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
    {if $validate}
        
        {field_make_rules 
            entity      = $validate.entity 
            field       = $name
            scenario    = $validate.scenario
            assign      = "validateRules"}
         
        {foreach $validateRules as $key => $valRules}
            {$attr[$key] = $valRules}
        {/foreach}

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
