{**
 * Коллапс Документация
 *}
 
{block 'content'}
    
    
    {test_heading text='Форма'}

    {capture 'test_example_content'}
        {cblock 'form'}
            {component 'field.text' name="login" label="Login"}
            
            {component 'field.password' name="pass" label="Пароль"}
            
            {component 'field.textarea' name="text" label="Текст"}
            
            {component 'field.select' 
                items   = [
                    [ value => 1, text => "option 1"],
                    [ value => 2, text => "option 2"],
                    [ value => 3, text => "option 3"],
                    [ value => 4, text => "option 4"],
                    [ value => 5, text => "option 5"]
                ]   
                name    = "select" 
                selected = 2
                label   = "Выбор"}
            
            {component 'field.select' 
                items   = [
                    [ value => 1, text => "option 1"],
                    [ value => 2, text => "option 2"],
                    [ value => 3, text => "option 3"],
                    [ value => 4, text => "option 4"],
                    [ value => 5, text => "option 5"]
                ]   
                multiple    = true
                name        = "select" 
                selected    = 2
                label       = "Выбрать несколько"}
            
            {component 'field.checkbox' 
                name        = "check1" 
                label       = "Флажок 1"}


            {component 'field.checkbox' 
                name        = "check2" 
                checked     = true
                label       = "Флажок 2"}
            
            <div class="form-group">
                {component 'field.radio' 
                    name        = "check4" 
                    checked     = true
                    label       = "выбор 1"}
            
                {component 'field.radio' 
                    name        = "check4" 
                    label       = "выбор 2"}
            </div>
            
            <div class="form-group">
                {component 'field.radio' 
                    name        = "check5" 
                    checked     = true
                    mods        = "inline"
                    label       = "выбор 1"}
            
                {component 'field.radio' 
                    name        = "check5" 
                    mods        = "inline"
                    label       = "выбор 2"}
            </div>
            
            {component 'field.range' 
                name        = "range" 
                label       = "Диапазон"}
                
            {component 'field.file' 
                name        = "file" 
                label       = "Файл"}
        {/cblock}
    {/capture}

    {capture 'test_example_code'}
        {ldelim}cblock 'form'{rdelim}
            <div class="form-group">
                {ldelim}component 'field.text' name="login" label="Login"{rdelim}
            </div>
            <div class="form-group">
                {ldelim}component 'field.password' name="pass" label="Пароль"{rdelim}
            </div>
        {ldelim}/cblock{rdelim}
    
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}
    


{/block}