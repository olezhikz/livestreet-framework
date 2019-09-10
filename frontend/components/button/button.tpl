{**
 * Кнопка
 *
 * @param mixed   $text             Массив либо строка с текстом уведомления. Массив должен быть в формате: `[ [ title, msg ], ... ]`
 * @param string  $bmods="success"  Список модификторов основного блока (через пробел)
 * @param string  $bg="light"       Модификтор фона
 * @param string  $classes          Список классов основного блока (через пробел)
 * @param array   $attributes       Список атрибутов основного блока
 *}{strip}
 


{component_define_params params=[ 'text', 'url', 'active','icon', 'disabled' , 'bmods', 'bg', 'classes', 'attributes', 
    'type', 'value', 'tag', 'com', 'popover', 'badge' ]}

{* Название компонента *}
{$component = $com|default:"btn"}
{$tag = $tag|default:"button"}

{block 'button_options'}{/block}

{block 'button_content'}{strip}
    {if $url}
        <a class="{$component} {cmods name=$component mods=$bmods delimiter="-"} {$classes}" 
           {if $popover}{component "popover" params=$popover} {/if} 
           {cattr list=$attributes} {if $disabled}aria-disabled="true"{/if} href="{$url}" role="button">
            {if $icon}
                {if is_array($icon)}
                    {component "icon" params=$icon}
                {else}
                    {component "icon" icon=$icon display='s' classes="{if $text}mr-1{/if}"}
                {/if}                    
            {/if}
            {$text}
        </a>
    {else}
        {if $tag != "input"}
            <{$tag} type="{$type|default:"button"}" class="{$component} {cmods name=$component mods=$bmods delimiter="-"} {$classes}" 
                {if $popover}
                    {if is_array($popover)}
                        {component "popover" params=$popover}
                    {else}
                        {component "popover" content=$popover}
                    {/if}
                {/if} 
                {cattr list=$attributes}>
                {if $icon}
                    {if is_array($icon)}
                        {component "icon" params=$icon}
                    {else}
                        {component "icon" icon=$icon display='s' classes="{if $text}mr-1{/if}"}
                    {/if}                    
                {/if}
                <span btn-text>{$text}</span>
                {if $badge}
                    {if is_array($badge)}
                        {component "badge" params=$badge}
                    {else}
                        {component "badge" text=$badge bmods=$bmods}
                    {/if}                    
                {/if}
            </{$tag}>
        {else}
            <input class="{$component} {cmods name=$component mods=$bmods delimiter="-"} {$classes}" 
                   {if $popover}{component "popover" params=$popover}{/if}
                {cattr list=$attributes} type="{$type|default:"button"}" value="{$value}">
        {/if}
    {/if}    
{/strip}{/block}{/strip}