{**
 * Модальноые окна Документация
 *}
 
{block 'content'}
    
    
    {test_heading text='Модальные окна'}

    {capture 'test_example_content'}
        
    {/capture}

    {capture 'test_example_code'}
        {ldelim}cblock 'form'{rdelim}
            
    
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}
    


{/block}