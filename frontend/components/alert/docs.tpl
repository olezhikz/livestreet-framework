{**
 * Оповещение
 *
 *}
 
{block 'content'}
    
    
    {test_heading text='Оповещение'}

    {capture 'test_example_content'}
        {component 'alert' mods="success" text='Текст оповещения' title="Заголовок оповещения"}
        {component 'alert' mods="primary" text='Текст оповещения' title="Заголовок оповещения"}
        {component 'alert' mods="danger" text='Текст оповещения' title="Заголовок оповещения"}
        {component 'alert' mods="warning" text='Текст оповещения' title="Заголовок оповещения" dismissible=true}
    {/capture}

    {capture 'test_example_code'}
        {ldelim}component 'alert' mods="success" text='Текст оповещения' title="Заголовок оповещения"{rdelim}
        {ldelim}component 'alert' mods="primary" text='Текст оповещения' title="Заголовок оповещения"{rdelim}
        {ldelim}component 'alert' mods="danger" text='Текст оповещения' title="Заголовок оповещения"{rdelim}
        {ldelim}component 'alert' 
            mods="warning" 
            text='Текст оповещения' 
            title="Заголовок оповещения" 
            dismissible=true{rdelim}
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}


{/block}
