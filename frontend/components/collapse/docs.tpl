{**
 * Коллапс Документация
 *}
 
{block 'content'}
    
    
    {test_heading text='Коллапс'}

    {capture 'test_example_content'}
        {component 'collapse'  button="Свернуть" content="Сворачиваемы контент"}<br>
        {cblock 'collapse' button="Свернуть" }Сворачиваемы контент{/cblock}
    {/capture}

    {capture 'test_example_code'}
        {ldelim}component 'collapse' button="Свернуть" content="Сворачиваемы контент"{rdelim}
        {ldelim}cblock 'collapse' button="Свернуть" {rdelim}Сворачиваемы контент{ldelim}/cblock{rdelim}
        
    
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}


{/block}
