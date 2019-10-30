{**
 * Карусель
 *
 *}
 
{block 'content'}
    
    
    {test_heading text='Карусель'}

    {capture 'test_example_content'}
        {component 'carousel' classes="slide" indicators=true controls=true  items=[
            [ src => 'https://getbootstrap.com/docs/4.1/assets/img/bootstrap-stack.png', alt => 'sdsd'],
            [ src => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSYryhpWpw66LNJRr4_Zj-PKB1xEhdsTKl5cCPuDbIpKvMAYKKG', alt => 'sdsd']
        ]}
    {/capture}

    {capture 'test_example_code'}
        {ldelim}component 'carousel' classes="slide" indicators=true controls=true  items=[
            [ src => 'https://getbootstrap.com/docs/4.1/assets/img/bootstrap-stack.png', alt => 'sdsd'],
            [ src => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSYryhpWpw66LNJRr4_Zj-PKB1xEhdsTKl5cCPuDbIpKvMAYKKG', alt => 'sdsd']
        ]{rdelim}
    
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}


{/block}
