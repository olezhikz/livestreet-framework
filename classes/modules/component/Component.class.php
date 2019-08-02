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
 * Модуль управления компонентами frontenda'а - независимые единицы (кирпичики) шаблона, состоящие из tpl, css, js
 *
 * @package framework.modules
 * @since 2.0
 */
class ModuleComponent extends Module
{

    /**
     * Список компонентов для подключения
     * В качестве ключей указывается название компонента, а в значениях возможные параметры
     *
     * @var array
     */
    protected $aComponentsList = array();
    /**
     * Кеш для данных компонентов - json и каталоги
     * Для каждого компонента есть ключи paths и json
     *
     * @var array
     */
    protected $aComponentsData = array();
    /**
     * Служебный счетчик для предотвращения зацикливания
     *
     * @var int
     */
    protected $iCountDependsRecursive = 0;
    
    /**
     * Констанаты ресурсов JSON
     */
    const DATA_SCRIPTS = "scripts";
    const DATA_STYLES = "styles";
    const DATA_TEMPLATES = "templates";
    
    /**
     * Инициализация модуля
     */
    public function Init()
    {
        $this->InitComponentsList();
    }

    /**
     * Инициализация начального списка необходимых для загрузки компонентов
     */
    public function InitComponentsList()
    {       
        /*
        * Конфиг скинов должен загрузиться раньше инициализации компонентов
        */
        if(!Engine::getInstance()->isInitModule('ModuleViewer')){
            $this->Viewer_GetHtmlTitleSeparation();
        }
        
        if ($aList = Config::Get('components') and is_array($aList)) {
            
            
            func_array_simpleflip($aList, array());
            $this->aComponentsList = array_merge_recursive($this->aComponentsList, $aList);
        }
    }

    /**
     * Выполняет загрузку необходимых компонентов
     * Под загрузкой понимается автоматическое подключение необходимых css, js
     */
    public function LoadAll()
    {
        /**
         * Подгрузка из кеша данных компонентов
         */
        $this->RetrieveComponentsDataCache();
        /**
         * Для каждого компонента считываем данные из json
         */
        $aComponentsName = array_keys($this->aComponentsList);
        /**
         * Используем кеширование построения дерева компонентов
         */
        $bCacheUse = Config::Get('module.component.cache_tree');
        $sCacheKey = 'components-tree-' . json_encode($aComponentsName);

        if (!$bCacheUse or false === ($aTree = $this->Cache_Get($sCacheKey))) {
            /**
             * Строим дерево компонентов с учетом зависимостей
             */
            $aTree = array();
            foreach ($aComponentsName as $sName) {
                list($sComponentPlugin, $sComponentName) = $this->ParseName($sName);
                $aTree[$sName] = array();
                /**
                 * Считываем данные компонента
                 */
                $aData = $this->GetComponentData($sName);
                $aData = $aData['json'];
                /**
                 * Проверяем зависимости
                 */
                if (isset($aData['dependencies']) and is_array($aData['dependencies'])) {
                    foreach ($aData['dependencies'] as $mKey => $mValue) {
                        if (!is_int($mKey) and $mValue === false) {
                            /**
                             * Пропускаем отмененную зависимость
                             */
                            continue;
                        }
                        $sNameDepend = is_int($mKey) ? $mValue : $mKey;
                        list($sComponentDependPlugin, $sComponentDependName) = $this->ParseName($sNameDepend);
                        if (is_null($sComponentDependPlugin) and $sComponentPlugin) {
                            $sNameDepend = $sComponentPlugin . ':' . $sComponentDependName;
                        }
                        $sNameDepend = trim($sNameDepend, ':');
                        $aTree[$sName][] = strtolower($sNameDepend);
                    }
                }
            }
            /**
             * Сортируем компоненты с учетом зависимостей
             */
            $this->iCountDependsRecursive = 0;
            $aTree = $this->GetSortedByDepends($aTree);

            if ($bCacheUse) {
                $this->Cache_Set($aTree, $sCacheKey, array(), 60 * 60 * 24);
            }
        }

        /**
         * Подключаем каждый компонент
         */
        foreach ($aTree as $sName => $aDepends) {
            $this->Load($sName);
        }
        /**
         * Информация по компонентам сохраняем в кеше
         */
        $this->StoreComponentsDataCache();
    }

    public function StoreComponentsDataCache()
    {
        if (!Config::Get('module.component.cache_data')) {
            return;
        }

        $sCacheKey = 'components-data-' . json_encode(array_keys($this->aComponentsList));
        $this->Cache_Set($this->aComponentsData, $sCacheKey, array(), 60 * 60 * 24);
    }

    public function RetrieveComponentsDataCache()
    {
        if (!Config::Get('module.component.cache_data')) {
            return;
        }
        $sCacheKey = 'components-data-' . json_encode(array_keys($this->aComponentsList));
        if (false !== ($aComponentsData = $this->Cache_Get($sCacheKey))) {
            foreach ($aComponentsData as $sName => $aData) {
                $this->aComponentsData[$sName] = $aData;
            }
        }
    }

    /**
     * Загружает/подключает компонент
     *
     * @param $sName
     */
    public function Load($sName)
    {
        /**
         * Json данные
         */
        $aData = $this->GetComponentData($sName);
        /**
         * Подключаем скрипты
         */
        $this->loadData($sName, $aData, self::DATA_SCRIPTS);
        /**
         * Подключаем стили
         */
        $this->loadData($sName, $aData, self::DATA_STYLES);
    
        
    }
    
    /**
     * Заружает выбранный тип ресурсов компонента в Asset
     * 
     * @param string $sName //имя компонента
     * @param string $sType //тип ресурсов
     * @param array $aData //данные из GetComponentData
     */
    protected function loadData(string $sName, array $aData, string $sType) {
        
        $aDataMeta = $aData['json'];
        
        if (isset($aDataMeta[$sType]) and is_array($aDataMeta[$sType])) {
            foreach ($aDataMeta[$sType] as $mName => $mAsset) {
                $aParams = array();
                if (is_array($mAsset)) {
                    $sAsset = isset($mAsset['file']) ? $mAsset['file'] : 'not_found_file_param';
                    unset($mAsset['file']);
                    $aParams = $mAsset;
                } else {
                    $sAsset = $mAsset;
                }
                if ($sAsset === false) {
                    continue;
                }
                
                foreach ($aData['paths'] as $sPath) {
                    $sFile = $sPath . '/' . $sAsset;
                    if (file_exists($sFile)) {
                        break;
                    }
                }
                /*
                 * формируем имя ресурса
                 */
                $aParams['name'] = getNameAsset($mName, $mAsset);
                /*
                 * Получаем зависимости с учетом типа ресурса
                 */
                if(isset($aDataMeta['dependencies']) and $aDataMeta['dependencies']){
                    $aParams['dependencies'] = $this->getAssetDependencies($aDataMeta['dependencies'], $sType);
                }else{
                    $aParams['dependencies'] = [];
                }
                /*
                 * Добавляем в набор зависимости отдельного ресурса
                 */
                if(isset($mAsset['dependencies']) and $mAsset['dependencies']){
                    if(!is_array($mAsset['dependencies'])){
                        $mAsset['dependencies'] = [$mAsset['dependencies']];
                    }
                    $aParams['dependencies'] = array_merge($aParams['dependencies'], $mAsset['dependencies']);
                }
                $this->Asset_Add($sFile, $aParams, $sType);
            }
        }
    }
    
    /**
     * * Получить зависимости в удобном для модуля asset виде
     * 
     * @param array $aDependencies
     * @param string $sType
     * @return array
     */
    protected function getAssetDependencies( array $aDependencies, string $sType) {
        /*
         * Получаем список зависимостей определенного типа
         */
        if(!isset($aDependencies[$sType])){
            return [];
        }
        $aTypeDepends = $aDependencies[$sType];
        
        $aDepends = [];
        foreach ($aTypeDepends as  $sComponentName) {
            /*
             * Достаем список ресурсов компонента определенного типа
             */
            $aData = $this->GetComponentData($sComponentName)['json'];
            if(!isset($aData[$sType])){
                continue;
            }
            /*
             * Вставляем все зависимости
             */
            foreach ($aData[$sType] as $mName => $mAsset) {
                $aDepends[] = getNameAsset($mName, $mAsset);
            }
        }
        
        return $aDepends; 
    }
    
    /**
     * Пулучить имя полное имя ресурса
     * 
     * @param string $sKey
     * @param string $mAsset
     * @return string
     */
    protected function getNameAsset(string $sKey, string $mAsset) {
        $sFileName = (is_int($sKey) ? basename($mAsset) : $sKey);

        if(isset($mAsset['name']) and $mAsset['name']){
            $sFileName = $mAsset['name'];
        }
        
        return "component@{$sName}.{$sFileName}";
    }

    /**
     * Добавляет новый компонент в список для загрузки
     *
     * @param $sName
     * @param $aParams
     */
    public function Add($sName, $aParams = array())
    {
        $sName = strtolower($sName);
        if (!array_key_exists($sName, $this->aComponentsList)) {
            $this->aComponentsList[$sName] = $aParams;
        }
    }

    /**
     * Удаляет компонент из списка загрузки
     *
     * @param $sName
     */
    public function Remove($sName)
    {
        $sName = strtolower($sName);
        unset($this->aComponentsList[$sName]);
    }

    /**
     * Удаляет все компоненты из загрузки
     */
    public function RemoveAll()
    {
        $this->aComponentsList = array();
    }

    /**
     * Возвращает полные серверные пути до компонента
     *
     * @param string $sName Имя компонента. Может содержать название плагина, например, "page:alert" - компонент alert плагина page
     * @return string
     */
    public function GetPaths($sName)
    {
        $aData = $this->GetComponentData($sName);
        return $aData['paths'];
    }

    /**
     * Возвращает полный серверный путь до компонента
     * Т.к. путей может быть несколько, то возвращаем первый по приоритету
     *
     * @param string $sName Имя компонента. Может содержать название плагина, например, "page:alert" - компонент alert плагина page
     * @return string
     */
    public function GetPath($sName)
    {
        $aPaths = $this->GetPaths($sName);
        return reset($aPaths);
    }

    /**
     * Возвращает полный web путь до компонента с учетом текущей схемы (http/https)
     * Т.к. путей может быть несколько, то возвращаем первый по приоритету
     *
     * @param $sName
     * @return bool
     */
    public function GetWebPath($sName)
    {
        if ($sPathServer = $this->GetPath($sName)) {
            return $this->Fs_GetPathWebFromServer($sPathServer);
        }
        return false;
    }

    /**
     * Возвращает путь до шаблона
     * Путь может быть как абсолютным, так и относительным корня шаблона
     * Метод учитывает возможное наследование плагинами, а также учитывает приоритет шаблона (tpl шаблона -> application -> framework)
     *
     * @param $sNameFull
     * @param $sTemplate
     * @param $bCheckDelegate
     * @return string
     */
    public function GetTemplatePath($sNameFull, $sTemplate = null, $bCheckDelegate = true)
    {
        list($sPlugin, $sName, $sTemplateParse) = $this->ParseName($sNameFull);
        /**
         * По дефолту используем в качестве имени шаблона название компонента
         */
        if (!$sTemplate) {
            $sTemplate = $sName;
        }
        
        if($sTemplateParse){
            $sTemplate = $sTemplateParse;
        }
        if ($bCheckDelegate) {
            /**
             * Базовое название компонента
             */
            $sNameBase = ($sPlugin ? "{$sPlugin}:" : '') . "component.{$sName}.{$sTemplate}";
            /**
             * Проверяем наследование по базовому имени
             */
            $sNameBaseInherit = $this->Plugin_GetDelegate('template', $sNameBase);
            if ($sNameBaseInherit != $sNameBase) {
                return $sNameBaseInherit;
            }
        }
        /**
         * Компонент не наследуется, поэтому получаем до него полный серверный путь
         */
        $aData = $this->GetComponentData($sNameFull);
        $aDataJson = $aData['json'];
        foreach ($aData['paths'] as $sPath) {
            if (isset($aDataJson['templates'][$sTemplate])) {
                $sTpl = $aDataJson['templates'][$sTemplate];
            } else {
                $sTpl = "{$sTemplate}.tpl";
            }
            $sFile = $sPath . '/' . $sTpl;
            if (file_exists($sFile)) {
                return $sFile;
            }
        }
        return false;
    }

    /**
     * Возвращает полный серверный путь до css/js компонента
     *
     * @param $sNameFull
     * @param $sAssetType
     * @param $sAssetName
     * @return bool|string
     */
    public function GetAssetPath($sNameFull, $sAssetType, $sAssetName)
    {
        $aData = $this->GetComponentData($sNameFull);

        if (in_array($sAssetType, array('scripts', 'js'))) {
            $sAssetType = 'scripts';
            $sAssetExt = 'js';
        } else {
            $sAssetType = 'styles';
            $sAssetExt = 'css';
        }
        /**
         * Получаем путь до файла из json
         */
        $aDataJson = $aData['json'];
        if (isset($aDataJson[$sAssetType][$sAssetName])) {
            $sAsset = $aDataJson[$sAssetType][$sAssetName];
        } else {
            $sAsset = "{$sAssetName}.{$sAssetExt}";
        }
        if ($sAsset === false) {
            return false;
        }
        foreach ($aData['paths'] as $sPath) {
            $sFile = $sPath . '/' . $sAsset;
            if (file_exists($sFile)) {
                return $sFile;
            }
        }
        return false;
    }

    /**
     * Парсит имя компонента
     * Имя может содержать название плагина - plugin:component
     *
     * @param $sName
     * @return array Массив из двух элементов, первый - имя плагина, воторой - имя компонента. Если плагина нет, то null вместо его имени
     */
    protected function ParseName($sName)
    {
        if(!preg_match('/^(component@)?(([\w]+):)?([\w-]+)\.?([\w\.-]+)?$/', $sName, $aMatches)){
            return array('', '', '');
        }
        
        $sTemplate = isset($aMatches[5])?$aMatches[5]:'';
        
        return array($aMatches[3], $aMatches[4], $sTemplate);
        
    }

    /**
     * Вспомогательный метод для сортировки компонентов по зависимостям
     *
     * @param $aComp
     * @param $aSorted
     * @param $sName
     * @return bool
     */
    protected function GetDepends($aComp, $aSorted, $sName)
    {
        if (isset($aComp[$sName])) {
            foreach ($aComp[$sName] as $sItem) {
                if (!isset($aSorted[$sItem])) {
                    $this->iCountDependsRecursive++;
                    if ($this->iCountDependsRecursive > 2000) {
                        return false;
                    } else {
                        return $this->GetDepends($aComp, $aSorted, $sItem);
                    }
                }
            }
        }
        return $sName;
    }

    /**
     * Сортирует компоненты по зависимостям - зависимые подключаются ниже
     *
     * @param $aComp
     * @return array|bool
     */
    protected function GetSortedByDepends($aComp)
    {
        $aSorted = array();
        foreach ($aComp as $sName => $void) {
            do {
                if ($sCompDepend = $this->GetDepends($aComp, $aSorted, $sName)) {
                    if (isset($aComp[$sCompDepend])) {
                        $aSorted[$sCompDepend] = $aComp[$sCompDepend];
                    } else {
                        $aSorted[$sCompDepend] = array();
                    }
                } else {
                    $aSorted = false;
                    break;
                }
            } while ($sCompDepend != $sName);
        }
        return $aSorted;
    }

    /**
     * Возвращает данные компонента
     *
     * @param $sName
     * @return array
     */
    protected function GetComponentData($sName)
    {
        /**
         * Смотрим в кеше
         */
        if (isset($this->aComponentsData[$sName])) {
            return $this->aComponentsData[$sName];
        }
        /**
         * Получаем список каталогов, где находится компонент и json мета информацию
         */
        $aPaths = $this->GetComponentPaths($sName);
        $this->aComponentsData[$sName] = array(
            'json'  => $this->GetComponentJson($aPaths),
            'paths' => $aPaths,

        );
        return $this->aComponentsData[$sName];
    }

    /**
     * Возвращает список каталогов, где находится компонент.
     * Каталоги возвращаются согласно приоритету - сначала идут самые приоритетные.
     *
     * @param $sName
     * @return array
     */
    protected function GetComponentPaths($sName)
    {
        list($sPlugin, $sName) = $this->ParseName($sName);
        $sPath = 'components/' . $sName;
        $aPaths = array();
        
            
        if ($sPlugin) {
            /**
             * Проверяем наличие компонента в каталоге текущего шаблона плагина
             */
            $sPathTemplate = Plugin::GetTemplatePath($sPlugin);
            if (file_exists($sPathTemplate . $sPath)) {
                $aPaths[] = $sPathTemplate . $sPath;
            }
            /**
             * Проверяем наличие компонента в общем каталоге плагина
             */
            $sPathTemplate = Config::Get('path.application.plugins.server') . "/{$sPlugin}/frontend";
            if (file_exists($sPathTemplate . '/' . $sPath)) {
                $aPaths[] = $sPathTemplate . '/' . $sPath;
            }
        }
        
        /**
        * Проверяем наличие компонента в каталоге текущего шаблона
        */
        $sPathTemplate = $this->Fs_GetPathServerFromWeb(Config::Get('path.skin.web'));
        if (file_exists($sPathTemplate . '/' . $sPath)) {
            $aPaths[] = $sPathTemplate . '/' . $sPath;
        }

        /**
         * Проверяем на компонент приложения
         */
        $sPathTemplate = Config::Get('path.application.server') . '/frontend';
        if (file_exists($sPathTemplate . '/' . $sPath)) {
            $aPaths[] = $sPathTemplate . '/' . $sPath;
        }
        /**
         * Проверяем на компонент фреймворка
         */
        $sPathTemplate = Config::Get('path.framework.server') . '/frontend';
        if (file_exists($sPathTemplate . '/' . $sPath)) {
            $aPaths[] = $sPathTemplate . '/' . $sPath;
        }
        return $aPaths;
    }

    /**
     * Возвращает json данные компонента с учетом наследования
     *
     * @param $aPaths
     * @return array|mixed
     */
    protected function GetComponentJson(&$aPaths)
    {
        /**
         * Получаем пути в обратном порядке, т.к. будем мержить данные
         */
        $aPaths = array_reverse($aPaths);
        $aPathsNew = array();
        $aJson = array();
        foreach ($aPaths as $sPath) {
            $sFileJson = $sPath . '/component.json';
            if (file_exists($sFileJson)) {
                if ($sContent = @file_get_contents($sFileJson)) {
                    if ($aData = @json_decode($sContent, true)) {
                        if (isset($aData['mode']) and $aData['mode'] == 'delegate') {
                            $aJson = $aData;
                            /**
                             * Удаляем прошлые каталоги
                             */
                            $aPathsNew = array();
                        } else {
                            $aJson = func_array_merge_assoc($aJson, $aData);
                        }
                    } elseif (!is_array($aData)) {
                        $this->Logger_Error('Invalid format component.json', array('file' => $sFileJson));
                    }
                }
            }
            $aPathsNew[] = $sPath;
        }
        /**
         * Подменяем пути
         */
        $aPaths = array_reverse($aPathsNew);
        return $aJson;
    }

    /**
     * Возвращает отрендеренный шаблон компонента
     *
     * @param string $sComponent Имя компонента
     * @param string|null $sTemplate Название шаблона, если null то будет использоваться шаблон по имени компонента
     * @param array $aParams Список параметров, которые необходимо прогрузить в шаблон. Параметры прогружаются как локальные.
     * @return string
     */
    public function Fetch($sComponent, $sTemplate = null, $aParams = array())
    {
        $oViewer = $this->Viewer_GetLocalViewer();
        $oViewer->Assign($aParams, null, true);
        return $oViewer->Fetch('component@' . $sComponent . ($sTemplate ? '.' . $sTemplate : ''));
    }
}
