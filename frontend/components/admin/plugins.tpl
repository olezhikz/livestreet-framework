{**
 * Список плагинов
 *
 * @param array $plugins Список плагинов
 *}

{component_define_params params=[ 'plugins' ]}

<div class="d-flex flex-column">
    {foreach $plugins as $plugin}

        {capture name="plugin"}
            <div class="media">
                <img class="mr-3" src=".../64x64" alt="Plugin image">
                <div class="media-body">
                  <h5 class="mt-0">{$plugin->getPackageInfo('name')}</h5>
                    {$plugin->getPackageInfo('description')}  
                    {* Активировать/деактивировать *}
                    {if $plugin->isActive()}
                        {component 'button'
                            bmods = "secondary"
                            url  = "{router page='admin'}plugins/?plugin={$plugin->getCode()}&action=deactivate&security_ls_key={$LIVESTREET_SECURITY_KEY}"
                            text = {lang 'admin.plugins.plugin.deactivate'}}
                    {else}
                        {component 'button'
                            bmods = "success"
                            url  = "{router page='admin'}plugins/?plugin={$plugin->getCode()}&action=activate&security_ls_key={$LIVESTREET_SECURITY_KEY}"
                            mods = 'primary'
                            text = {lang 'admin.plugins.plugin.activate'}}
                    {/if}

                    {* Применить обновление *}
                    {if $plugin->isOutdate() && $plugin->isActive()}
                        {component 'button'
                            bmods = "success"
                            url  = "{router page='admin'}plugins/?plugin={$plugin->getCode()}&action=apply_update&security_ls_key={$LIVESTREET_SECURITY_KEY}"
                            text = {lang 'admin.plugins.plugin.apply_update'}}
                    {/if}

                    {* Удалить *}
                        {component 'button'
                            bmods = "danger"
                            url        = "{router page='admin'}plugins/?plugin={$plugin->getPackageInfo('code')}&action=remove&security_ls_key={$LIVESTREET_SECURITY_KEY}"
                            attributes = [ 'onclick' => "return confirm('{lang 'common.remove_confirm'}');" ]
                            text       = {lang 'admin.plugins.plugin.remove'}}              
                </div>
            </div>
           
            
        {/capture}


        {component "card" 
            classes = "mt-2"
            content = [
                [
                    type => 'body',
                    content => $smarty.capture.plugin
                ]
            ]}

    {/foreach}
        
</div>
