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
 * Абстракция плагина, от которой наследуются все плагины
 * Файл плагина должен находиться в каталоге /plugins/plgname/ и иметь название PluginPlgname.class.php
 *
 * @package framework.engine
 * @since 1.0
 */
abstract class Plugin extends LsObject
{
    /**
     * Путь к шаблонам с учетом наличия соответствующего skin`a
     *
     * @var array
     */
    static protected $aTemplatePath = array();
    /**
     * Web-адрес директорий шаблонов с учетом наличия соответствующего skin`a
     *
     * @var array
     */
    static protected $aTemplateWebPath = array();
    /**
     * Массив делегатов плагина
     *
     * @var array
     */
    protected $aDelegates = array();
    /**
     * Массив наследуемых классов плагина
     *
     * @var array
     */
    protected $aInherits = array();
    /**
     * Объект с данными пакета composer.json
     * @var \Packages\Package 
     */
    protected $package = null;

    /**
     * Метод инициализации плагина
     *
     */
    public function Init()
    {
    }
    /**
     * 
     * @param \Packages\Package $package
     */
    public function setPackageInfo(\Packages\Package $package) {
        $this->package = $package;
    }
    /**
     * 
     * @param string $name
     * @return mixed|null
     */
    public function getPackageInfo(string $name) {
        if(!$this->package){
            return null;
        }
        return $this->package->get($name);
    }
    
    public function isActive() {
        return in_array($this->GetPluginCode($this), array_keys(Engine::getInstance()->GetPlugins()));
    }

    public function isOutdate() {
        if(!$version = $this->PluginManager_GetVersionByCode(self::GetPluginCode($this))){
            return false;
        }
        
        return (is_null($version->getVersion()) or version_compare($version->getVersion(), (string) $this->GetVersion(),
                            '<')) ? true : false;
    }
    /**
     * Метод, который вызывается перед самой инициализацией ядра
     */
    public function BeforeInitEngine()
    {
        $rc = new ReflectionClass(get_class($this));
        $sDir =  dirname($rc->getFileName());
        
        $sPlugin = strtolower(str_replace('Plugin', '', $rc->getShortName()));
        /*
         * Добавляем путь до плагина в конфиг
         */
        Config::Set("path.plugin.$sPlugin.server", $sDir);
        Config::Set("path.plugin.$sPlugin.template", self::GetTemplatePath($sPlugin));
                
        Config::setFromFile("plugin.$sPlugin", $sDir . '/config/config.php');
        Config::setFromFile("plugin.$sPlugin", $sDir . '/config/config.'. Engine::GetEnvironment().'.php');
        
        /**
        * Смотрим конфиг плагина в /application/config/plugins/[plugin_name]/config.php
        */
        $sFileUserConfig = Config::get('path.application.server') . "/config/plugins/{$sPlugin}/config.php";
        Config::setFromFile("plugin.$sPlugin", $sFileUserConfig);
        /**
        * Смотрим конфиг плагина текущего окружения в /application/config/plugins/[plugin_name]/config.[environment].php
        */
        $sFileUserConfig = Config::get('path.application.server') . "/config/plugins/{$sPlugin}/config.".Engine::GetEnvironment().".php";
        Config::setFromFile("plugin.$sPlugin", $sFileUserConfig);
        
        /*
         * Подключаем include
         */
        $aIncludeFiles = glob($sDir . '/include/*.php');       
        if ($aIncludeFiles and count($aIncludeFiles)) {
            foreach ($aIncludeFiles as $sPath) {
                require_once($sPath);
            }
        }
        
    }
    
    /**
     * Передает информацию о делегатах в модуль ModulePlugin
     * Вызывается Engine перед инициализацией плагина
     * @see Engine::LoadPlugins
     */
    final function Delegate()
    {
        $aDelegates = $this->GetDelegates();
        foreach ($aDelegates as $sObjectName => $aParams) {
            foreach ($aParams as $sFrom => $sTo) {
                $this->Plugin_Delegate($sObjectName, $sFrom, $sTo, get_class($this));
            }
        }

        $aInherits = $this->GetInherits();
        foreach ($aInherits as $sObjectName => $aParams) {
            foreach ($aParams as $sFrom => $sTo) {
                $this->Plugin_Inherit($sFrom, $sTo, get_class($this));
            }
        }
    }

    /**
     * Возвращает массив наследников
     *
     * @return array
     */
    final function GetInherits()
    {
        $aReturn = array();
        if (is_array($this->aInherits) and count($this->aInherits)) {
            foreach ($this->aInherits as $sObjectName => $aParams) {
                if (is_array($aParams) and count($aParams)) {
                    foreach ($aParams as $sFrom => $sTo) {
                        if (is_int($sFrom)) {
                            $sFrom = $sTo;
                            $sTo = null;
                        }
                        list($sFrom, $sTo) = $this->MakeDelegateParams($sObjectName, $sFrom, $sTo);
                        $aReturn[$sObjectName][$sFrom] = $sTo;
                    }
                }
            }
        }
        return $aReturn;
    }

    /**
     * Возвращает массив делегатов
     *
     * @return array
     */
    final function GetDelegates()
    {
        $aReturn = array();
        if (is_array($this->aDelegates) and count($this->aDelegates)) {
            foreach ($this->aDelegates as $sObjectName => $aParams) {
                if (is_array($aParams) and count($aParams)) {
                    foreach ($aParams as $sFrom => $sTo) {
                        if (is_int($sFrom)) {
                            $sFrom = $sTo;
                            $sTo = null;
                        }
                        list($sFrom, $sTo) = $this->MakeDelegateParams($sObjectName, $sFrom, $sTo);
                        $aReturn[$sObjectName][$sFrom] = $sTo;
                    }
                }
            }
        }
        return $aReturn;
    }

    /**
     * Преобразовывает краткую форму имен делегатов в полную
     *
     * @param $sObjectName    Название типа объекта делегата
     * @see ModulePlugin::aDelegates
     * @param $sFrom    Что делегируем
     * @param $sTo        Что делегирует
     * @return array
     */
    public function MakeDelegateParams($sObjectName, $sFrom, $sTo)
    {
        /**
         * Если не указан делегат TO, считаем, что делегатом является
         * одноименный объект текущего плагина
         */
        if ($sObjectName == 'template') {
            if (!$sTo) {
                $sTo = self::GetTemplatePath(get_class($this)) . $sFrom;
            } else {
                $sTo = preg_replace("/^_/", $this->GetTemplatePath(get_class($this)), $sTo);
            }
        } else {
            if (!$sTo) {
                $sTo = get_class($this) . '_' . $sFrom;
            } else {
                $sTo = preg_replace("/^_/", get_class($this) . '_', $sTo);
            }
        }
        return array($sFrom, $sTo);
    }

    /**
     * Метод активации плагина
     *
     * @return bool
     */
    public function Activate()
    {
        return true;
    }

    /**
     * Метод деактивации плагина
     *
     * @return bool
     */
    public function Deactivate()
    {
        return true;
    }

    /**
     * Метод удаления плагина
     *
     * @return bool
     */
    public function Remove()
    {
        return true;
    }

    /**
     * Транслирует на базу данных запросы из указанного файла
     * @see ModuleDatabase::ExportSQL
     *
     * @param  string $sFilePath Полный путь до файла с SQL
     * @return array
     */
    protected function ExportSQL($sFilePath)
    {
        return $this->Database_ExportSQL($sFilePath);
    }

    /**
     * Выполняет SQL
     * @see ModuleDatabase::ExportSQLQuery
     *
     * @param string $sSql Строка SQL запроса
     * @return array
     */
    protected function ExportSQLQuery($sSql)
    {
        return $this->Database_ExportSQLQuery($sSql);
    }

    /**
     * Проверяет наличие таблицы в БД
     * @see ModuleDatabase::IsTableExists
     *
     * @param string $sTableName Название таблицы, необходимо перед именем таблицы добавлять "prefix_", это позволит учитывать произвольный префикс таблиц у пользователя
     * <pre>
     * prefix_topic
     * </pre>
     * @return bool
     */
    protected function IsTableExists($sTableName)
    {
        return $this->Database_IsTableExists($sTableName);
    }

    /**
     * Проверяет наличие поля в таблице
     * @see ModuleDatabase::IsFieldExists
     *
     * @param string $sTableName Название таблицы, необходимо перед именем таблицы добавлять "prefix_", это позволит учитывать произвольный префикс таблиц у пользователя
     * @param string $sFieldName Название поля в таблице
     * @return bool
     */
    protected function IsFieldExists($sTableName, $sFieldName)
    {
        return $this->Database_IsFieldExists($sTableName, $sFieldName);
    }

    /**
     * Добавляет новый тип в поле enum(перечисление)
     * @see ModuleDatabase::AddEnumType
     *
     * @param string $sTableName Название таблицы, необходимо перед именем таблицы добавлять "prefix_", это позволит учитывать произвольный префикс таблиц у пользователя
     * @param string $sFieldName Название поля в таблице
     * @param string $sType Название типа
     */
    protected function AddEnumType($sTableName, $sFieldName, $sType)
    {
        $this->Database_AddEnumType($sTableName, $sFieldName, $sType);
    }

    /**
     * Удаляет тип в поле таблицы с типом enum
     * @see ModuleDatabase::RemoveEnumType
     *
     * @param string $sTableName Название таблицы, необходимо перед именем таблицы добавлять "prefix_", это позволит учитывать произвольный префикс таблиц у пользователя
     * @param string $sFieldName Название поля в таблице
     * @param string $sType Название типа
     */
    protected function RemoveEnumType($sTableName, $sFieldName, $sType)
    {
        $this->Database_RemoveEnumType($sTableName, $sFieldName, $sType);
    }

    /**
     * Возвращает версию плагина
     *
     * @return string|null
     */
    public function GetVersion()
    {
        if (!$this->package) {
            return null;
        }
        return $this->package->get('version');
    }

    /**
     * Возвращает полный серверный путь до плагина
     *
     * @param string $sName
     * @return string
     */
    static public function GetPath($sName)
    {
        $reflector = new ReflectionClass( 'Plugin' . ucfirst(self::GetPluginCode($sName)) );
        return dirname($reflector->getFileName());
    }

    /**
     * Возвращает полный web-адрес до плагина
     *
     * @param string $sName
     * @return string
     */
    static public function GetWebPath($sName)
    {
        $sPath = self::GetPath($sName);
        return Engine::getInstance()->Fs_GetPathWebFromServer($sPath); 
    }

    /**
     * Возвращает правильный серверный путь к директории шаблонов с учетом текущего шаблона
     * Если пользователь использует шаблон которого нет в плагине, то возвращает путь до шабона плагина 'default'
     *
     * @param string $sName Название плагина или его класс
     * @return string|null
     */
    static public function GetTemplatePath($sName)
    {
        $sName = self::GetPluginCode($sName);
        if (!isset(self::$aTemplatePath[$sName])) {
            $aPaths = glob(self::GetPath($sName) . '/frontend/skin/*',GLOB_ONLYDIR);
            $sTemplateName = ($aPaths and in_array(Config::Get('view.skin'), array_map('basename', $aPaths)))
                ? Config::Get('view.skin')
                : 'default';

            $sDir = self::GetPath($sName) . "/frontend/skin/{$sTemplateName}/";
            self::$aTemplatePath[$sName] = is_dir($sDir) ? $sDir : null;
        }
        return self::$aTemplatePath[$sName];
    }

    /**
     * Устанавливает значение серверного пути до шаблонов плагина
     *
     * @param  string $sName Имя плагина
     * @param  string $sTemplatePath Серверный путь до шаблона
     * @return bool
     */
    static public function SetTemplatePath($sName, $sTemplatePath)
    {
        if (!is_dir($sTemplatePath)) {
            return false;
        }
        self::$aTemplatePath[$sName] = $sTemplatePath;
        return true;
    }

    /**
     * Устанавливает значение web-пути до шаблонов плагина
     *
     * @param  string $sName Имя плагина
     * @param  string $sTemplatePath Серверный путь до шаблона
     */
    static public function SetTemplateWebPath($sName, $sTemplatePath)
    {
        self::$aTemplateWebPath[$sName] = $sTemplatePath;
    }

    /**
     * Возвращает код плагина
     *
     * @param string|object $mPlugin Объект любого класса плагина или название плагина
     *
     * @return string
     */
    static public function GetPluginCode($mPlugin)
    {
        if (is_object($mPlugin)) {
            $mPlugin = get_class($mPlugin);
        }
        return preg_match('/^Plugin([\w]+)(_[\w]+)?$/Ui', $mPlugin, $aMatches)
            ? func_underscore($aMatches[1])
            : func_underscore($mPlugin);
    }
    
    public function getCode() {
        return self::GetPluginCode($this);
    }
}