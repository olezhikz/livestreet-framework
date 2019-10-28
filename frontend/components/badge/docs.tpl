{**
 * Кнопка
 *
 *}
 
{block 'content'}
    
    
    {test_heading text='Значки'}

    {capture 'test_example_content'}
        {component 'badge' mods="success" text='user'}
        {component 'badge' mods="primary" text='22'}
        {component 'badge' mods="danger" text={component 'icon' icon='user'}}
    {/capture}

    {capture 'test_example_code'}
        {ldelim}component 'badge' mods="success" text='user'{rdelim}
        {ldelim}component 'badge' mods="primary" text='22'{rdelim}
        {ldelim}component 'badge' mods="danger" text={ldelim}component 'icon' icon='user'{rdelim}{rdelim}
    
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}


{/block}
