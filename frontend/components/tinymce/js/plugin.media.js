/**
 * LiveStreet
 *
 * @license   GNU General Public License, version 2
 * @copyright 2013 OOO "ЛС-СОФТ" {@link http://livestreetcms.com}
 * @author    Oleg Demidov
 */

tinymce.PluginManager.add('livestreet', function(editor, url) {
    

    // Ссылка на пользователя
    editor.addButton('ls-user', {
        icon: 'emoticons',
        tooltip: 'User',
        onclick: function() {
            // Open window
            editor.windowManager.open({
                title: 'Add user',
                body: [
                    { type: 'textbox', name: 'login', label: 'Login' }
                ],
                onsubmit: function(e) {
                    editor.insertContent('<a href="' + PATH_ROOT + '/profile/' + e.data.login + '">' + e.data.login + '</a>');
                }
            });
        }
    });

    // Вставка медиа-объектов
    editor.addButton('ls-media', {
        icon: 'image',
        tooltip: 'Insert media',
        onclick: function() {
            $( editor.getElement() ).lsEditor( 'showMedia' );
        }
    });

    
});