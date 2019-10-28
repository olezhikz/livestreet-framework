{**
 * Кнопка
 *
 *}
 
{block 'content'}
    
    
    {test_heading text='Иконки'}

    {capture 'test_example_content'}
        {component 'icon' icon='user'}
        {component 'icon' icon='bell'}
        {component 'icon' icon='comment'}
        {component 'icon' icon='thumbs-up'}
        {component 'icon' icon='paper-plane'}
        {component 'icon' icon='star'}
    {/capture}

    {capture 'test_example_code'}
        {ldelim}component 'icon' icon='user'{rdelim}
        {ldelim}component 'icon' icon='bell'{rdelim}
        {ldelim}component 'icon' icon='comment'{rdelim}
        {ldelim}component 'icon' icon='thumbs-up'{rdelim}
        {ldelim}component 'icon' icon='paper-plane'{rdelim}
        {ldelim}component 'icon' icon='star'{rdelim}
    
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}


{/block}
