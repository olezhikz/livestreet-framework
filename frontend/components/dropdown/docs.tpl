{**
 * Выпадаеющее меню
 *
 *}
 
{block 'content'}
    
    
    {test_heading text='Выпадаеющее меню'}

    {capture 'test_example_content'}
        {component 'dropdown' 
            mods = "success" 
            text = 'Dropdown'
            items = [
                [
                    text => "item 1"
                ]
            ]}
        
    {/capture}

    {capture 'test_example_code'}
        {ldelim}component 'dropdown' 
            mods = "success" 
            text = 'Dropdown'
            items = [
                [
                    text => "item 1"
                ]
            ]{rdelim}
    
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}


{/block}
