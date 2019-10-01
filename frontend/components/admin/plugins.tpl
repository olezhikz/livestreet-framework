{**
 * Список плагинов
 *
 * @param array $plugins Список плагинов
 *}

{component_define_params params=[ 'plugins' ]}

<table class="ls-table admin-plugins">
    <tbody>
        {foreach $plugins as $plugin}
            
            {capture name="plugin"}
                <h3>{$plugin.property->name->data}</h3>
                <p>{$plugin.property->description->data}</p>
            {/capture}

            
            {component "card" content = [
                [
                    type => 'body',
                    content => $smarty.capture.plugin
                ]
            ]}
            <tr {if $plugin.is_active}class="active"{/if}>
                {* Название и описание плагина *}
                <td>
                    <h3>{$plugin.property->name->data}</h3>
                    <p>{$plugin.property->description->data}</p>

                    {component 'list-group' items=[
                        [ 'label' => {lang 'admin.plugins.plugin.version'}, 'content' => $plugin.property->version|escape ],
                        [ 'label' => {lang 'admin.plugins.plugin.author'},  'content' => $plugin.property->author->data ],
                        [ 'label' => {lang 'admin.plugins.plugin.url'},     'content' => $plugin.property->homepage ]
                    ]}
                </td>

                {* Действия *}
                <div class="d-flex flex-collumn admin-plugins-actions">
                        {* Активировать/деактивировать *}
                            {if $plugin.is_active}
                                {component 'button'
                                    url  = "{router page='admin'}plugins/?plugin={$plugin.code}&action=deactivate&security_ls_key={$LIVESTREET_SECURITY_KEY}"
                                    text = {lang 'admin.plugins.plugin.deactivate'}}
                            {else}
                                {component 'button'
                                    url  = "{router page='admin'}plugins/?plugin={$plugin.code}&action=activate&security_ls_key={$LIVESTREET_SECURITY_KEY}"
                                    mods = 'primary'
                                    text = {lang 'admin.plugins.plugin.activate'}}
                            {/if}

                        {* Применить обновление *}
                        {if $plugin.apply_update && $plugin.is_active}
                                {component 'button'
                                    url  = "{router page='admin'}plugins/?plugin={$plugin.code}&action=apply_update&security_ls_key={$LIVESTREET_SECURITY_KEY}"
                                    text = {lang 'admin.plugins.plugin.apply_update'}}
                        {/if}

                        {* Ссылка на страницу настроек *}
                        {if $plugin.property->settings != "" && $plugin.is_active}
                                {component 'button'
                                    url  = $plugin.property->settings
                                    text = {lang 'admin.plugins.plugin.settings'}}
                        {/if}

                        {* Удалить *}
                            {component 'button'
                                url        = "{router page='admin'}plugins/?plugin={$plugin.code}&action=remove&security_ls_key={$LIVESTREET_SECURITY_KEY}"
                                attributes = [ 'onclick' => "return confirm('{lang 'common.remove_confirm'}');" ]
                                text       = {lang 'admin.plugins.plugin.remove'}}
                </div>
                
            </tr>
        {/foreach}
    </tbody>
</table>