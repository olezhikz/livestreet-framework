<?php
/**
 * LiveStreet CMS
 * Copyright © 2014 OOO "ЛС-СОФТ"
 *
 * ------------------------------------------------------
 *
 * Official site: www.livestreetcms.com
 * Contact e-mail: office@livestreetcms.com
 *
 * GNU General Public License, version 2:
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * ------------------------------------------------------
 *
 * @link http://www.livestreetcms.com
 * @copyright 2014 OOO "ЛС-СОФТ"
 * @author Maxim Mzhelskiy <rus.engine@gmail.com>
 *
 */


/**
 * Определяем окружение
 * В зависимости от окружения будет дополнительно подгружаться необходимый конфиг.
 * array('{окружение}' => array('{хост1}', '{хост2}')) если хост совпадет выставится окружение
 * Например, для окружения "production" будет загружен конфиг /application/config/config.production.php
 * По дефолту работает окружение "local"
 */
$sEnv = Engine::DetectEnvironment(array(
    'production' => array('your-machine-name'),
));

/**
 * Проверяем на необходимость выставить тестовое окружение
 */
if (isset($bUseEnvironmentTesting)) {
    Engine::SetEnvironment($sEnv = 'testing');
}

/*
 * Загружаем конфиги
 */
require_once dirname(__DIR__).'/config/loader.php';

