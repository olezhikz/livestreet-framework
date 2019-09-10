{**
 * Группа кнопок переключателей
 *
 * @param string  $classes        Список классов основного блока (через пробел)
 * @param array   $attributes     Список атрибутов основного блока
 * @param array   $items     Список атрибутов основного блока
 *}

{$component = "btn"}
 
{component_define_params params=[ 'items', 'classes', 'attributes', 'name', 'bmods' ]}

{block 'button_toggle_content'}
    <div class="btn-group btn-group-toggle {cmods name=$component mods=$bmods delimiter="-"} {$classes}" 
         {cattr list=$attributes} data-toggle="buttons">
        {foreach $items as $item}
            <label class="{$component} {cmods name=$component mods=$item.bmods delimiter="-"} {$item.classes} {if $item.checked}active{/if}" 
                   {cattr list=$item.attributes}>
                <input type="radio" name="{$name}" value="{$item.value}" {if $item.id}id="{$item.id}"{/if}
                       autocomplete="off" {if $item.checked}checked{/if}> 
                {if $item.icon}
                    {if is_array($item.icon)}
                        {component "icon" params=$item.icon}
                    {else}
                        {component "icon" icon=$item.icon display='s' classes="{if $text}mr-1{/if}"}
                    {/if}                    
                {/if}
                {$item.text}

            </label>
        {/foreach}
    </div>   
{/block}