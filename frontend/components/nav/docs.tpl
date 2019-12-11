{**
 * Меню
 *
 *}
 
{block 'content'}
    
    
    {test_heading text='Меню'}

    {capture 'test_example_content'}
        {plugin_docs_params params = [
            ['items', 'array', 'null', 'Массив элементов меню [text, name]'],
            ['activeItem', 'string', 'null', 'Имя name активного пункта']
        ]}
        
        {component 'nav' 
            mods="pills" 
            activeItem='second' 
            items=[
                [
                    text => 'Первый пункт',
                    name => 'first'
                ],
                [
                    text => 'Второй пункт',
                    name => 'second'
                ],
                [
                    text => 'Третий пункт',
                    name => 'other3'
                ],
                [
                    text => 'Четвертй пункт',
                    name => 'other4',
                    disabled => true
                ]
            ]}
        
    {/capture}

    {capture 'test_example_code'}
        {ldelim}component 'nav' 
            mods="pills" 
            activeItem='second' 
            items=[
                [
                    text => 'Первый пункт',
                    name => 'first'
                ],
                [
                    text => 'Второй пункт',
                    name => 'second'
                ],
                [
                    text => 'Третий пункт',
                    name => 'other3'
                ],
                [
                    text => 'Четвертй пункт',
                    name => 'other4',
                    disabled => true
                ],
            ]{rdelim}
       
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}
    
    {test_heading text='Табы'}

    {capture 'test_example_content2'}
        {component 'nav' 
            mods="tabs" 
            activeItem='second' 
            items=[
                [
                    text => 'Первый пункт',
                    name => 'first'
                ],
                [
                    text => 'Второй пункт',
                    name => 'second'
                ],
                [
                    text => 'Третий пункт',
                    name => 'other3'
                ],
                [
                    text => 'Четвертй пункт',
                    name => 'other4',
                    disabled => true
                ]
            ]}
        
    {/capture}

    {capture 'test_example_code2'}
        {ldelim}component 'nav' 
            mods="tabs" 
            activeItem='second' 
            items=[
                [
                    text => 'Первый пункт',
                    name => 'first'
                ],
                [
                    text => 'Второй пункт',
                    name => 'second'
                ],
                [
                    text => 'Третий пункт',
                    name => 'other3'
                ],
                [
                    text => 'Четвертй пункт',
                    name => 'other4',
                    disabled => true
                ]
            ]{rdelim}
       
    {/capture}

    {test_example content=$smarty.capture.test_example_content2 code=$smarty.capture.test_example_code2}
   
{/block}
