{**
 * Коллапс Документация
 *}
 
{block 'content'}
    
    
    {test_heading text='Коллапс'}

    {capture 'test_example_content'}
{*        {component 'collapse' button="Свернуть" content="Сворачиваемы контент"}*}
        {componentb 'collapse' button="Свернуть" }ss{/componentb}
    {/capture}

    {capture 'test_example_code'}
        {ldelim}component 'collapse' button="Свернуть" content="Сворачиваемы контент"{rdelim}
    
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}


{/block}