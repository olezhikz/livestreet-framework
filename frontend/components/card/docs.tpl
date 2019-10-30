{**
 * Карточки
 *
 *}
 
{block 'content'}
    
    
    {test_heading text='Карточки'}

    {capture 'test_example_content'}
        {component 'card' 
            content = 'Содержание карточки' 
            header  = "Верхняя часть карточки"
            footer  = "Подвал"}
    {/capture}

    {capture 'test_example_code'}
        {ldelim}component 'card' 
            content = 'Содержание карточки' 
            header  = "Верхняя часть карточки"
            footer  = "Подвал"{rdelim}
    
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}


{/block}
