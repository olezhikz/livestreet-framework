{**
 * Аккордион Документация
 *}
 
{block 'content'}
    
    
    {test_heading text='Аккордион'}

    {capture 'test_example_content'}
        {component 'accordion' 
            activeItem = 'two'
            items = [
                [
                    text => 'link 1',
                    content => 'Content 1',
                    name => 'one'
                ],
                [
                    text => 'link 2',
                    content => 'Content 2',
                    name => 'two'
                ],
                [
                    text => 'link 3',
                    content => 'Content 3',
                    name => 'three'
                ],
                [
                    text => 'link 4',
                    content => 'Content 4',
                    name => 'for'
                ]
            
            ]}
    {/capture}

    {capture 'test_example_code'}
        {ldelim}component 'accordion' 
            activeItem = 'two'
            items = [
                [
                    text => 'link 1',
                    content => 'Content 1',
                    name => 'one'
                ],
                [
                    text => 'link 2',
                    content => 'Content 2',
                    name => 'two'
                ],
                [
                    text => 'link 3',
                    content => 'Content 3',
                    name => 'three'
                ],
                [
                    text => 'link 4',
                    content => 'Content 4',
                    name => 'for'
                ]
            
            ]{rdelim}
    
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}


{/block}