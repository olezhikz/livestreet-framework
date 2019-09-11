<?php

/*
 * LiveStreet CMS
 * Copyright © 2013 OOO "ЛС-СОФТ"
 *
 * ------------------------------------------------------
 *
 * Official site: www.livestreetcms.com
 * Contact e-mail: end-fin@yandex.ru
 *
 * GNU General Public License, version 2:
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * ------------------------------------------------------
 *
 * @link https://vk.com/u_demidova
 * @copyright 2013 OOO "ЛС-СОФТ"
 * @author Oleg Demidov <end-fin@yandex.ru>
 *
 */

/**
 * Description of ActionAsset
 *
 * @author oleg
 */
class ActionAsset extends Action{

    protected function RegisterEvent() {
        $this->SetDefaultEvent('index');
        
        $this->RegisterEvent('index', "EventIndex");
    }

    public function Init() {
        
    }
    
    protected function EventIndex() {
        echo 'assets';
    }

}
