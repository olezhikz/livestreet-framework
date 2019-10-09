<?php
/*-------------------------------------------------------
*
*   LiveStreet Engine Social Networking
*   Copyright © 2008 Mzhelskiy Maxim
*
*--------------------------------------------------------
*
*   Official site: www.livestreet.ru
*   Contact e-mail: rus.engine@gmail.com
*
*   GNU General Public License, version 2:
*   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*
---------------------------------------------------------
*/

/**
 * Основные константы
 */
define('LS_VERSION_FRAMEWORK', '2.0.1');

/**
 * Вспомогательная функция загружает файлы по шаблону https://www.php.net/manual/ru/function.glob.php
 */
$fIncludeDir = function($sDirInclude){
    $aIncludeFiles = glob($sDirInclude);       
    if ($aIncludeFiles and count($aIncludeFiles)) {
        foreach ($aIncludeFiles as $sPath) {
            require_once($sPath);
        }
    }
};

/**
 * Инклудим все *.php файлы из каталога {path.root.framework}/include/ - это файлы ядра
 */
$fIncludeDir(dirname(__DIR__) . '/include/*.php');


/**
 * Загружаем основной конфиг фреймворка
 */
Config::LoadFromFile(dirname(__FILE__) . '/config.php');

/*
 * Устанавливаем в конфиг корневой путь проекта
 */
if(defined('LS_ROOT_DIR')){
    Config::Get('path.root.server', LS_ROOT_DIR);
}

/**
 * Загружаем основной конфиг приложения
 */
Config::LoadFromFile(Config::Get('path.application.server') . '/config/config.php', false);

/**
 * Получаем текущее окружение
 */
$sEnvironmentCurrent = Engine::GetEnvironment();


/**
 * Загружает конфиги модулей вида /config/modules/[module_name]/config.php
 */
$sDirConfig = Config::get('path.application.server') . '/config/modules/';

if (is_dir($sDirConfig) and $hDirConfig = opendir($sDirConfig)) {
    while (false !== ($sDirModule = readdir($hDirConfig))) {
        if ($sDirModule != '.' and $sDirModule != '..' and is_dir($sDirConfig . $sDirModule)) {
            $sFileConfig = $sDirConfig . $sDirModule . '/config.php';
            
            Config::setFromFile( "module.$sDirModule", $sFileConfig);
            
        }
    }
    closedir($hDirConfig);
}

/**
 * Инклудим все *.php файлы из каталога {path.root.application}/include/ - пользовательские файлы
 */
$fIncludeDir(Config::get('path.application.server') . '/include/*.php');

/**
 * Подгружаем конфиг окружения
 */
if (file_exists(Config::Get('path.application.server') . "/config/config.{$sEnvironmentCurrent}.php")) {
    Config::LoadFromFile(Config::Get('path.application.server') . "/config/config.{$sEnvironmentCurrent}.php", false);
}

/**
 * Загружает конфиги плагинов вида 
 * [plugin_dir]/config/config.php
 * [plugin_dir]/config/config.{$sEnvironmentCurrent}.php
 * и include-файлы [plugin_dir]/include/*.php
 */
Engine::getInstance()->LoadConfigPlugins();

foreach (Engine::getInstance()->GetPlugins() as $sPlugin => $oPlugin) {
    /**
    * Смотрим конфиг плагина в /application/config/plugins/[plugin_name]/config.php
    */
    $sFileUserConfig = Config::get('path.application.server') . "/config/plugins/{$sPlugin}/config.php";
    Config::setFromFile("plugin.$sPlugin", $sFileUserConfig);
    /**
     * Смотрим конфиг плагина текущего окружения в /application/config/plugins/[plugin_name]/config.[environment].php
     */
    $sFileUserConfig = Config::get('path.application.server') . "/config/plugins/{$sPlugin}/config.{$sEnvironmentCurrent}.php";
    Config::setFromFile("plugin.$sPlugin", $sFileUserConfig);
}

