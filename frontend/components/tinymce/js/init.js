/**
 * LiveStreet
 *
 * @license   GNU General Public License, version 2
 * @copyright 2013 OOO "ЛС-СОФТ" {@link http://livestreetcms.com}
 * @author    Oleg Demidov
 */

ls.hook.add('ls_template_init_end', function(){ 
    
    /*
     * Инициализация tinymce
     */
    let plugins = [
        'autoresize',
        'code',
        'codesample',
        'emoticons',
        'hr',
        'image',
        'imagetools',
        'insertdatetime',
        'link',
        'lists',
        'media',
        'paste',
        'preview',
        'print',
        'spellchecker',
        'table',
        'textcolor',
        'wordcount'
    ];

    let toolbar = [
        'styleselect', 
        '|', 
        'bold', 
        'italic', 
        'strikethrough', 
        'underline',
        'blockquote',
        'table',
        '|',
        'bullist',
        'numlist',
        '|',
        'link',
        'media',
        'removeformat',
        'pagebreak',
        'code',
        'fullscreen'
    ];

    plugins = plugins.concat(ls.registry.get('component.tinimce.plugins'));

    toolbar = toolbar.concat(ls.registry.get('component.tinimce.toolbar'));

    let options = {
        convert_urls : 0,
        remove_script_host : 0,
        selector: '[data-editor="tinymce"]',
        plugins: plugins.join(' ') ,
//                plugins: "autoresize autosave bbcode charmap code codesample colorpicker contextmenu directionality emoticons fullpage fullscreen 
//                help hr image imagetools importcss insertdatetime legacyoutput link lists media nonbreaking noneditable pagebreak paste preview print save 
//                searchreplace spellchecker tabfocus table template textcolor textpattern toc visualblocks visualchars wordcount",
        language: LANGUAGE,
        toolbar: toolbar.join(' '),
        init_instance_callback: function (editor) {
            editor.on('change', function (e) {
                $(editor.getElement())
                    .html(editor.getContent())
                    .change();
            });
        }
    };


    tinymce.init(options);
}, 100);