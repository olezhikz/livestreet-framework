/**
 * Media
 *
 * @module ls/media
 *
 * @license   GNU General Public License, version 2
 * @copyright 2013 OOO "ЛС-СОФТ" {@link http://livestreetcms.com}
 * @author    Denis Shakhov <denis.shakhov@gmail.com>
 *
 * TODO: Фильтрация файлов по типу при переключении табов
 */

(function($) {
    "use strict";

    $.widget( "livestreet.bsMedia", $.livestreet.lsComponent, {
        /**
         * Дефолтные опции
         */
        options: {
            
            

            // Селекторы
            selectors: {
                uploader: '[data-type="uploader"]',
                library: '[data-type="library"]',
                modal:'@#mediaModal'
            },

            uploader_options: {},

            params: {}
            
        },
        
        

        /**
         * Конструктор
         *
         * @constructor
         * @private
         */
        _create: function () {
            this._super();
            /*
             * Инит библиотеки
             */
            this.elements.library.bsLibrary();
            
            this.elements.modal.on('show.bs.modal', function(e){
                this.elements.library.bsLibrary('loadFiles');
            }.bind(this));
            
            // Иниц-ия загрузчика 
            this.elements.uploader.bsUploader({
                i18n:{
                    errorDublicate:ls.lang.get('media.uploader.notices.errorDublicate')
                },
                onFileUpload:function(){
                    this.elements.library.bsLibrary('loadFiles')
                }.bind(this)
            });   
            
            this.elements.btn.attr('disable',true);
            this._on(this.elements.btn, {click: "select"});
            
        },
        
        attachFields: function(fields){
            fields.mousedown( function(e){
                this.option('field', e.currentTarget);
            }.bind(this));
        },
        
        select: function(e){
            this.elements.modal.modal('hide')
            let file = this.elements.library.bsLibrary('getSelectItem');
            let size = this.elements.library.bsLibrary('getSelectSize');
            if(file === null){
                return;
            }
            if(this.option('field') !== null){
                $(this.option('field')).bsMediaField('add', file, size);
                this.option('field', null);
            }
            this.insertEditor(file);
            
            this._trigger('onSelectFile', null, file);
        },
        
        insertEditor:function(file){
            this._load( 'insert' , { ids: [file.data('id')] }, function( response ) {
                this._trigger('onInsertEditor', null, response.sTextResult);
                this.hide();
            }.bind(this));
        },
        
        show:function(callback){
            this.elements.modal.modal('show');
        },
        
        hide:function(){
            this.elements.modal.modal('hide');
        }
        
        
    });
})(jQuery);