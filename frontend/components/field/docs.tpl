{**
 * Коллапс Документация
 *}
 
{block 'content'}
    
    
    {test_heading text='Форма'}

    {capture 'test_example_content'}
        {componentb 'form'}
            <div class="form-group">
                {component 'field.text' name="login" label="Login"}
            </div>
            <div class="form-group">
                {component 'field.password' name="pass" label="Пароль"}
            </div>
        {/componentb}
    {/capture}

    {capture 'test_example_code'}
        {ldelim}component 'collapse' button="Свернуть" content="Сворачиваемы контент"{rdelim}
        {ldelim}componentb 'collapse' button="Свернуть" {rdelim}Сворачиваемы контент{ldelim}/componentb{rdelim}
        
    
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}


{/block}