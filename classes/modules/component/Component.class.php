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
 * @author Oleg Demidov
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
     * Список загруженных компонентов
     *
     * @var array
     */
    protected $aComponentsLoaded = [];
    /*
     * Список компонентов поставленных в очередь
     * 
     * @var array
     */
    protected $aComponentsQuene = [];
    
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
         * Подключаем каждый компонент
         */
        foreach ($aComponentsName as $sName) {
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
     * @return type
     * @throws Exception
     */
    public function Load($sName)
    {   
        /*
         * Если компонент не существует останавливаем
         */
        if(!$this->GetComponentPaths($sName)){
            throw new OutOfBoundsException("Component {$sName} not found");
        }
        /*
         * Если компонент загружен пропускаем
         */
        if(in_array($sName, $this->aComponentsLoaded)){
            return;
        }
        /*
         * Определяем не зациклены ли зависимости
         */
        if(in_array($sName, $this->aComponentsQuene)){
            throw new OutOfBoundsException("Warning: Cicle dependency component {$sName}");
        }
        $this->aComponentsQuene[] = $sName;
        /**
         * Json данные
         */
        $aData = $this->GetComponentData($sName);
        
        
        $aDataJson = $aData['json'];
        if(!$aDataJson){
            return;
        }
        /*
        * Загружаем компоненты от которых зависим
        */
        if(isset($aDataJson['dependencies']) and is_array($aDataJson['dependencies'])){
            foreach ($aDataJson['dependencies'] as $sComponentDepend) {
                $this->Load($sComponentDepend);
            }
        }
        if(isset($aDataJson['assets'])){  
            $this->Asset_AddFromConfig( $aDataJson['assets'] );
        }
        /*
         * Компонент загружен
         */
        $this->aComponentsLoaded[] = $sName;
        /*
         * Убираем загруженные из очереди
         */
        $this->aComponentsQuene = array_diff($this->aComponentsQuene, $this->aComponentsLoaded);
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
     * @param string $sNameFull
     * @param string $sAssetType
     * @param string $sAssetName
     * @return bool|string
     */
    public function GetAssetPath(string $sNameFull, string $sAssetType, string $sAssetName)
    {
        $aData = $this->GetComponentData($sNameFull);

        /**
         * Получаем путь до файла из json
         */
        $aDataJson = $aData['json'];
        if (!isset($aDataJson['assets'][$sAssetType][$sAssetName])) {
            throw new OutOfBoundsException("Not found asset `{$sAssetName}` in component `{$sNameFull}`");
        }
         
        return $this->getPathToAsset($aData['paths'], $aDataJson['assets'][$sAssetType][$sAssetName]);
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
        
        $sTemplate = isset($aMatches[5])?$aMatches[5]:null;
        
        return array($aMatches[3], $aMatches[4], $sTemplate);
        
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
        list($sPlugin, $sName, $sTemplate) = $this->ParseName($sName);
                
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
        /*
         * Применяем пути к ресурсам
         */
        $this->parseData($aJson, $aPaths);
        
        return $aJson;
    }
    
    /**
     * Применить пути к ресурсам компонента 
     * и включить имя компонента в имена ресурсов
     * 
     * @param array $aDataComponent
     * @param array $aPath
     * @return array|null
     */
    protected function parseData(array &$aDataComponent, array $aPaths) {
        if(!isset($aDataComponent['assets'])){
            return;
        } 
        /*
         * Приводим к единому виду
         */
        
        $aNewAssets = [];
        
        foreach ($aDataComponent['assets'] as $sType => $aAssets) {
            $aNewAssets[$sType] = [];
            
            foreach ($aAssets as $sName => $mAsset) {
                $sAssetName = $this->generateAssetName($sType, $aDataComponent['name'], $sName);
                
                if(is_string($mAsset)){
                    $aNewAssets[$sType][$sAssetName] = $this->getPathToAsset($aPaths, $mAsset);
                    continue;
                }

                if(isset($mAsset['file'])){
                    if(false === strpos($mAsset['file'], '://') && 0 !== strpos($mAsset['file'], '//')){
                        $mAsset['file'] = $this->getPathToAsset($aPaths, $mAsset['file']);
                    }
                }
                
                if(isset($mAsset['publicDir'])){
                    $mAsset['publicDir'] = $this->getPathToAsset($aPaths, $mAsset['publicDir']);
                }
                
                $aNewAssets[$sType][$sAssetName] = $mAsset;
            }
            
        }
        
        $aDataComponent['assets'] = $aNewAssets;
        
    }
    
    protected function generateAssetName($sType, $sComponentName, $sNameAsset) {
        $sComponentNameNormal = preg_replace('/[^\w]/', '_', $sComponentName);
        $sNameAssetNormal = preg_replace('/[^\w]/', '_', $sNameAsset);
        return "component_{$sComponentNameNormal}_{$sType}_{$sNameAssetNormal}";
    }
    
    /**
     * Получить валидный путь до ресурса
     * 
     * @param array $aPathsComponent Список путей до компонента
     * @param string $sPathAsset Путь до ресурса внутри компонента
     * @return boolean|string
     */
    protected function getPathToAsset(array $aPathsComponent, string $sPathAsset) {
        foreach ($aPathsComponent as $sPath) {
            $sFile = $sPath . '/' . $sPathAsset;
            if (file_exists($sFile)) {
                return $sFile;
            }
        }
        return false;
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
