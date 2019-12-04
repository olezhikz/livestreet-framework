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
            text = "Пример модальноо окна"
        }
        
        {cblock "modal"
            closed  = true
            id      = "exampleModal"
            header  = "Заголовок"
            footer  = "Подвал"
        }
            Контент внутри модальноо окна
        {/cblock}
    {/capture}

    {capture 'test_example_code'}
        {ldelim}cblock 'form'{rdelim}
            
    
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}
    


{/block}