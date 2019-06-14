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
 * Проверяем на необходимость выставить тестовое окружение
 */
if (isset($bUseEnvironmentTesting)) {
    Engine::SetEnvironment($sEnv = 'testing');
}

require dirname(__DIR__). '/vendor/autoload.php';