<?php
/*
 * LiveStreet CMS
 * Copyright © 2013 OOO "ЛС-СОФТ"
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
 * @copyright 2013 OOO "ЛС-СОФТ"
 * @author Maxim Mzhelskiy <rus.engine@gmail.com>
 *
 */

/**
 * Модификатор declension: склонение существительных в зависимости от множественного числа
 *
 * @param      $iCount
 * @param      $mForms
 * @param null $sLang
 *
 * @return mixed
 */
function smarty_prefilter_default_name_argment($source, &$smarty)
{    echo "33";
   return preg_replace('\{component\s{2}\"', '{component name="', $source);
}