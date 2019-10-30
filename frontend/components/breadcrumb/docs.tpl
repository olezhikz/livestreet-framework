{**
 * Хлебные крошки
 *
 *}
 
{block 'content'}
    
    
    {test_heading text='Хлебные крошки'}

    {capture 'test_example_content'}
        {component 'breadcrumb' items=[
                [text=>"Главная", url => '#1'], 
                [text=>"1 раздел", url => '#2']
            ] 
            }
        
    {/capture}

    {capture 'test_example_code'}
        {ldelim}component 'breadcrumb' items=[
                [text=>"Главная", url => '#1'], 
                [text=>"1 раздел", url => '#2']
            ] 
            {rdelim}
       
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}
   
{/block}
