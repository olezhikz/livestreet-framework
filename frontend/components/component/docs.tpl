{**
 * Компонент Документация
 *}
 
{block 'content'}
    
    
    {test_heading text='Основной шаблон компонента от него наследуются все компоненты'}

    {capture 'test_example_content'}
        {plugin_docs_params params = [
            ['mods', 'string', 'null', 'Список модификторов основного блока (через пробел)'],
            ['classes', 'string', 'null', 'Список классов основного блока (через пробел)'],
            ['attr', 'array', 'null', 'Список атрибутов основного блока'],
            ['role', 'string', 'null', 'Вспомогательный атрибут role'],
            ['tag', 'string', 'null', 'Тег основного элемента'],
            ['hook', 'string', 'null', 'Хук компонента'],
            ['content', 'string', 'null', 'Контент если имеется']
        ]}
        
    {/capture}

    {capture 'test_example_code'}
        Эти параметры доступны для всех компонентов наследующихся от component.layout   
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}
    
    {test_heading text='Вызов'}

    {capture 'test_example_content'}
               
    {/capture}

    {capture 'test_example_code'}
        {ldelim}component "name" template="template" param1="value" ...{rdelim}
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}
    

    {test_heading text='Короткая запись'}

    {capture 'test_example_content'}
               
    {/capture}

    {capture 'test_example_code'}
        {ldelim}component "name.template" param1="value" ...{rdelim}
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}
    
    {test_heading text='Если имеется много контента внутри'}

    {capture 'test_example_content'}
            
    {/capture}

    {capture 'test_example_code'}
        {ldelim}cblock "name.template" param1="value" ...{rdelim}Много контента ...{ldelim}/cblock{rdelim}
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}

{/block}