{**
 * Модальноые окна Документация
 *}
 
{block 'content'}
    
    
    {test_heading text='Модальные окна'}

    {capture 'test_example_content'}
        {component "button"
            attr = [
                'data-toggle'   => "modal", 
                'data-target'   => "#exampleModal"
            ]
            mods    = "primary"
            text = "Пример модального окна"
        }
        
        {cblock "modal"
            closed  = true
            id      = "exampleModal"
            header  = "Заголовок"
            footer  = "Подвал"
        }
            Контент внутри модального окна
        {/cblock}
    {/capture}

    {capture 'test_example_code'}
        {ldelim}component "button"
            attr = [
                'data-toggle'   => "modal", 
                'data-target'   => "#exampleModal"
            ]
            mods    = "primary"
            text    = "Пример модального окна"
        {rdelim}
        
        {ldelim}cblock "modal"
            closed  = true
            id      = "exampleModal"
            header  = "Заголовок"
            footer  = "Подвал"
        {rdelim}
            Контент внутри модального окна
        {ldelim}/cblock{rdelim}
            
    
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}
    


{/block}