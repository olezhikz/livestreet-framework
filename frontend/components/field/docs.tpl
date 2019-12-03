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
              
            <div class="form-group py-3">
   
                <label for="">Группировка полей</label>
                
                {component "field.group" 
                    prependItems = [
                        {component 'button' text='Button' mods="outline-secondary"},
                        "<div class='input-group-text'>Text pre</div>",
                        {component 'dropdown' text='<span class="sr-only"></span>' mods="outline-secondary"}
                    ]
                    items = [
                        {component 'field.text' name="textt" group=false}
                    ]
                    appendItems = [
                        {component 'button' text='Button' mods="outline-secondary"},
                        "<div class='input-group-text'>Text pre</div>"
                    ]
                }
            </div>
        {/cblock}
    {/capture}

    {capture 'test_example_code'}
        {ldelim}cblock 'form'{rdelim}
            {ldelim}component 'field.text' name="login" label="Login"{rdelim}
            
            {ldelim}component 'field.password' name="pass" label="Пароль"{rdelim}
            
            {ldelim}component 'field.textarea' name="text" label="Текст"{rdelim}
            
            {ldelim}component 'field.select' 
                items   = [
                    [ value => 1, text => "option 1"],
                    [ value => 2, text => "option 2"],
                    [ value => 3, text => "option 3"],
                    [ value => 4, text => "option 4"],
                    [ value => 5, text => "option 5"]
                ]   
                name    = "select" 
                selected = 2
                label   = "Выбор"{rdelim}
            
            {ldelim}component 'field.select' 
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
                label       = "Выбрать несколько"{rdelim}
            
            {ldelim}component 'field.checkbox' 
                name        = "check1" 
                label       = "Флажок 1"{rdelim}


            {ldelim}component 'field.checkbox' 
                name        = "check2" 
                checked     = true
                label       = "Флажок 2"{rdelim}
            
            <div class="form-group">
                {ldelim}component 'field.radio' 
                    name        = "check4" 
                    checked     = true
                    label       = "выбор 1"{rdelim}
            
                {ldelim}component 'field.radio' 
                    name        = "check4" 
                    label       = "выбор 2"{rdelim}
            </div>
            
            <div class="form-group">
                {ldelim}component 'field.radio' 
                    name        = "check5" 
                    checked     = true
                    mods        = "inline"
                    label       = "выбор 1"{rdelim}
            
                {ldelim}component 'field.radio' 
                    name        = "check5" 
                    mods        = "inline"
                    label       = "выбор 2"{rdelim}
            </div>
            
            {ldelim}component 'field.range' 
                name        = "range" 
                label       = "Диапазон"{rdelim}
                
            {ldelim}component 'field.file' 
                name        = "file" 
                label       = "Файл"{rdelim}
              
            <div class="form-group py-3">
   
                <label for="">Группировка полей</label>
                
                {ldelim}component "field.group" 
                    prependItems = [
                        {ldelim}component 'button' text='Button' mods="outline-secondary"{rdelim},
                        "<div class='input-group-text'>Text pre</div>",
                        {ldelim}component 'dropdown' text='<span class="sr-only"></span>' mods="outline-secondary"{rdelim}
                    ]
                    items = [
                        {ldelim}component 'field.text' name="textt" group=false{rdelim}
                    ]
                    appendItems = [
                        {ldelim}component 'button' text='Button' mods="outline-secondary"{rdelim},
                        "<div class='input-group-text'>Text pre</div>"
                    ]
                {rdelim}
            </div>
        {ldelim}/cblock{rdelim}
    
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}
    


{/block}