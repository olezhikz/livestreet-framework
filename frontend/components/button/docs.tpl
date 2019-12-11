{**
 * Кнопка
 *
 *}
 
{block 'content'}
    
    
    {test_heading text='Кнопки'}

    {capture 'test_example_content'}
        
        {plugin_docs_params params = [
            ['text', 'string', 'null', 'Текст кнопки'],
            ['icon', 'string', 'null', 'Иконка перед текстом'],
            ['badge', 'string', 'null', 'Метка после текста']
        ]}
        
        {component 'button' text='Кнопка' mods='primary'}
        {component 'button' text='Ссылка' mods='primary' url='http://example.com'}
    {/capture}

    {capture 'test_example_code'}
    {ldelim}component 'button' mods='primary' text='Кнопка'{rdelim}
    {ldelim}component 'button' mods='primary' text='Ссылка' url='http://example.com'{rdelim}
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}


    {**
     * Цвета
     *}
    {test_heading text='Цвета'}

    <p>Модификаторы <code>primary</code> <code>success</code> <code>info</code> <code>warning</code> <code>danger</code></p>

    {capture 'test_example_content'}
        {component 'button' text='Default'}
        {component 'button' text='Popver' mods='warning' popover="Popover text"}
        {component 'button' text='Primary' mods='primary'}
        {component 'button' text='Success' mods='success'}
        {component 'button' text='Info' mods='info'}
        {component 'button' text='Warning' mods='warning'}
        {component 'button' text='Danger' mods='danger'}
    {/capture}

    {capture 'test_example_code'}
    {ldelim}component 'button' text='Default'{rdelim}
    {ldelim}component 'button' text='Primary' mods='primary'{rdelim}
    {ldelim}component 'button' text='Success' mods='success'{rdelim}
    {ldelim}component 'button' text='Info' mods='info'{rdelim}
    {ldelim}component 'button' text='Warning' mods='warning'{rdelim}
    {ldelim}component 'button' text='Danger' mods='danger'{rdelim}
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}


    {**
     * Размеры
     *}
    {test_heading text='Размеры'}

    <p>Модификаторы <code>lg</code> <code>sm</code> </p>

    {capture 'test_example_content'}
        <p>{component 'button' text='Large button' mods='lg primary'}</p>
        <p>{component 'button' text='Small button' mods='sm success'}</p>
    {/capture}

    {capture 'test_example_code'}
    {ldelim}component 'button' text='Large button' mods='lg primary'{rdelim}
    {ldelim}component 'button' text='Small button' mods='sm success'{rdelim}
    
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}

    <h3>Кнопка во всю ширину родительского блока</h3>

    <p>Модификатор <code>block</code></p>

    {capture 'test_example_content'}
    <div style="background: #fafafa; padding: 20px;">
        {component 'button' text='Block button' mods='lg block success'}
    </div>
    {/capture}

    {capture 'test_example_code'}
    {ldelim}component 'button' text='Block button' mods='lg block success'{rdelim}
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}


    {**
     * Иконки
     *}
    {test_heading text='Иконки'}

    <p>Параметр <code>icon</code></p>

    {capture 'test_example_content'}
        {component 'button' text='Save' icon='check' mods="secondary"}
        {component 'button' text='icon' icon='bell' mods="success"}
    {/capture}

    {capture 'test_example_code'}
    {ldelim}component 'button' text='Save' icon='check'{rdelim}
    {ldelim}component 'button' text='icon' icon='bell'{rdelim}
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}


    {**
     * Отправка формы
     *}
    {test_heading text='Отправка формы'}

    <p>Опция <code>form</code> позволяет указать id формы для отправки, это бывает полезно если кнопку отправки необходимо разместить вне формы.</p>

    {capture 'test_code'}
    <form id="myform">
        ...
    </form>

    {ldelim}component 'button' text='Отправить' form='myform'{rdelim}
    {/capture}

    {test_code code=$smarty.capture.test_code}


    {**
     * Группировка кнопок
     *}
    {test_heading text='Группировка кнопок'}

    <p>Шаблон <code>group</code> позволяет группировать кнопки. По умолчанию кнопки группируются горизонтально, для вертикальной группировки необходимо добавить мод-ор <code>vertical</code>.</p>

    {capture 'test_example_content'}
        {component 'button' template='group' items=[
            [ 'text' => 'Left', 'mods' => 'lg secondary' ],
            [ 'text' => 'Middle', 'mods' => 'lg outline-secondary' ],
            [ 'text' => 'Middle', 'mods' => 'lg success' ],
            [ 'text' => 'Middle', 'mods' => 'lg danger' ],
            [ 'text' => 'Right', 'mods' => 'lg secondary' ]
        ]}
        <br>
        {component 'button' template='group' classes="mt-1" items=[
            [ 'text' => 'Left', 'mods' => 'success' ],
            [ 'text' => 'Middle', 'mods' => 'primary' ],
            [ 'text' => 'Middle', 'mods' => 'success' ],
            [ 'text' => 'Middle', 'mods' => 'secondary' ],
            [ 'text' => 'Right', 'mods' => 'danger' ]
        ]}
        
        <br>
        {component 'button' template='group' mods='vertical' classes="mt-1" items=[
            [ 'text' => 'Left', 'mods' => 'lg secondary' ],
            [ 'text' => 'Middle', 'mods' => 'lg outline-secondary' ],
            [ 'text' => 'Middle', 'mods' => 'lg success' ],
            [ 'text' => 'Middle', 'mods' => 'lg danger' ],
            [ 'text' => 'Right', 'mods' => 'lg secondary' ]
        ]}
    {/capture}

    {capture 'test_example_code'}
    {ldelim}component 'button' template='group' items=[
        [ 'text' => 'Left', 'mods' => 'lg secondary' ],
        [ 'text' => 'Middle', 'mods' => 'lg outline-secondary' ],
        [ 'text' => 'Middle', 'mods' => 'lg success' ],
        [ 'text' => 'Middle', 'mods' => 'lg danger' ],
        [ 'text' => 'Right', 'mods' => 'lg secondary' ]
    ]{rdelim}

    {ldelim}component 'button' template='group' items=[
        [ 'text' => 'Left', 'mods' => 'success' ],
        [ 'text' => 'Middle', 'mods' => 'primary' ],
        [ 'text' => 'Middle', 'mods' => 'success' ],
        [ 'text' => 'Middle', 'mods' => 'secondary' ],
        [ 'text' => 'Right', 'mods' => 'danger' ]
    ]{rdelim}

    {ldelim}component 'button' template='group' mods='vertical' items=[ ... ]{rdelim}
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}

    
    {**
     * Счетчик
     *}
    {test_heading text='Счетчик'}

    {capture 'test_example_content'}
        {component 'button' text='Комментарии' icon='comments' badge=[ text => 10 ]} <br><br>
        {component 'button' text='Комментарии' icon='comments' badge= 10  mods='primary'} <br><br>
        {component 'button' text='Комментарии' icon='comments' badge=11 mods='success'} <br><br>
        {component 'button' text='Комментарии' icon='comments' badge=12 mods='info'} <br><br>
        {component 'button' text='Комментарии' icon='comments' badge=13 mods='warning'} <br><br>
        {component 'button' text='Комментарии' icon='comments' badge=44 mods='danger'}
    {/capture}

    {capture 'test_example_code'}
    {ldelim}component 'button' text='Комментарии' icon='comments' badge=[ text => 10 ]{rdelim}
    {/capture}

    {test_example content=$smarty.capture.test_example_content code=$smarty.capture.test_example_code}
{/block}
