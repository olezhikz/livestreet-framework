{**
 * Меню
 *
 *}
 
{block 'content'}
    
    
    {test_heading text='Меню'}

    {capture 'test_example_content'}
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
   
{/block}
