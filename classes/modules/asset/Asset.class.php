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

require_once 'ParamsFilter.php';
require_once 'CssHtmlFilter.php';
require_once 'JsHtmlFilter.php';
require_once 'RemoteAsset.php';

use Assetic\Asset\FileAsset;
use Assetic\Asset\HttpAsset;
use Assetic\AssetWriter;
use Assetic\AssetManager;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetReference;
use Assetic\Asset\AssetInterface;
use Assetic\FilterManager;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\Worker\CacheBustingWorker;

/**
 * Модуль управления статическими файлами css стилей и js сриптов
 * Позволяет сжимать и объединять файлы для более быстрой загрузки
 *
 * @package framework.modules
 * @since 2.0
 */
class ModuleAsset extends Module
{
    
    protected $assets;
    
    protected $filters;
    
    protected $factory;

    protected $assetWriter;

    /**
     * Тип для файлов стилей
     */
    const ASSET_TYPE_CSS = 'css';
    /**
     * Тип для файлов скриптов
     */
    const ASSET_TYPE_JS = 'js';
    
    /**
     * Каталог для проверки блокировки
     *
     * @var null|string
     */
    protected $sDirMergeLock = null;
    /**
     * Список файлов по типам
     * @see Init
     *
     * @var array
     */
//    protected $assetCollection = array();

    /**
     * Инициалищация модуля
     */
    public function Init()
    {
        /**
         * Задаем начальную структуру для хранения списка файлов по типам
         */
        $this->InitAssets();
        
        /*
         * Инициируем фабрику ресурсов
         */
        $this->factory = new AssetFactory( Config::Get('path.cache_assets.server') );
        /*
         * Инициируем объект записи в папку web/assets
         */
        $this->assetWriter = new AssetWriter(Config::Get('path.cache_assets.server'));
    }

    /**
     * Задает начальную структуры для хранения списка файлов по типам
     */
    protected function InitAssets()
    {
        $am = new AssetManager();
        
        $this->assets = [
            self::ASSET_TYPE_CSS => new AssetManager(),
            self::ASSET_TYPE_JS => new AssetManager(),
        ];

        /*
         * Фильтры для разных ресурсов для Фабрики
         */
        $this->filters = new FilterManager();
        $this->filters->set(self::ASSET_TYPE_JS, new JsHtmlFilter());
        $this->filters->set(self::ASSET_TYPE_CSS, new CssHtmlFilter());
    }

    /**
     * Добавляет новый файл
     *
     * @param string $sFile Полный путь до файла
     * @param array $aParams Дополнительные параметры
     * @param string $sType Тип файла
     * @param bool $bReplace Если такой файл уже добавлен, то заменяет его
     *
     * @return bool
     */
    public function Add($sFile, $aParams, $sType, $bReaplace = false)
    {
        
        if (!$sType = $this->CheckAssetType($sType)) {
            return false;
        }
        /*
         * Определяем рабочий менеджер ресурсов
         */
        $assetManager = $this->assets[$sType];
        /**
         * В качестве уникального ключа использется имя или путь до файла
         */
        if ( isset($aParams['name']) ) {
            $sFileKey = $this->normalName($aParams['name']);
        }else{
            $sFileKey = $this->getAssetNameByPath($sFile);
        }
        
//        echo $sFileKey.PHP_EOL;
        /*
         * Если файл уже добавлен пропускаем
         */
        if($assetManager->has($sFileKey) and !$bReaplace){
            return false;
        }
        /**
         * Подготавливаем параметры
         */
        $aParams = $this->PrepareParams($aParams);
        /*
         * Определяем объект ассета HTTP удаленный или FILE локальный
         */
        $asset = $this->CreateAsset($sFile, $aParams);
        if(!$asset){
            return false;
        }
        /*
         * Добавляем фильтры исходя из параметров
         */
        $this->ensureFilters( $asset, $sType, $aParams);
        
        /*
         * Определяем есть ли зависимости
         */
        if($assetDependencies = $this->getDependencies($assetManager,$aParams)){
            $assetDependencies->add($asset);
            $asset = $assetDependencies;
        }
        
        $assetManager->set($sFileKey, $asset);
                
        return $asset;
    }
    
    /**
     * Генерирует name из path Убирает из строки все кроме букв и цифр
     * 
     * @param string $sPath
     * @return string
     */
    protected function getAssetNameByPath($sPath) {
        return $this->normalName( md5($sPath) . '_' . basename($sPath) );
    }
    
    /**
     * Имя для модуля asset не должно содержать некоторых символов
     * 
     * @param type $sName
     * @return string
     */
    protected function normalName($sName) {
        if (ctype_alnum(str_replace('_', '', $sName))) {
            return $sName;
        }
        return preg_replace([
            '/[^\w]/',
        ], [
            '_',
        ], $sName);
    }
    
    /**
     * Применяет фильтры к любому типу ресурса исходя из параметров
     * 
     * @param AssetInterface $asset
     * @param type $sType
     * @param array $aParams
     */
    protected function ensureFilters( AssetInterface $asset, $sType, array $aParams) {
        switch ($sType) {
            case self::ASSET_TYPE_JS:
                $this->ensureJsFilters( $asset,  $aParams);
                break;
            
            case self::ASSET_TYPE_CSS:
                $this->ensureCssFilters( $asset,  $aParams);
                break;
        }
        
        $asset->ensureFilter(new ParamsFilter($aParams));
                
    }
    
    /**
     * Применяет фильтры к CSS ресурсу исходя из параметров
     * 
     * @param AssetInterface $asset
     * @param array $aParams
     */
    protected function ensureCssFilters(AssetInterface $asset, array $aParams) {
        if($aParams['compress']){
            $asset->ensureFilter(new \Assetic\Filter\CssMinFilter());
        }
    }
    
    /**
     * Применяет фильтры к JS ресурсу исходя из параметров
     * 
     * @param AssetInterface $asset
     * @param array $aParams
     */
    protected function ensureJsFilters(AssetInterface $asset, array $aParams) {
        if($aParams['compress']){
            $asset->ensureFilter(new Assetic\Filter\JSqueezeFilter());
        }
    }
    
    /**
     * Определяем все зависимости ресурса по параметрам 
     * и отдаем коллекцию ссылок
     * 
     * @param AssetManager $assetManager
     * @param array $aParams
     * 
     * @return AssetCollection
     */
    protected function getDependencies($assetManager, $aParams){
        
        if(!$aParams['dependencies']){
            return false;
        } print_r($aParams);
        
        $assets = new AssetCollection();
        
        foreach ( $aParams['dependencies'] as $sKey) {
            $sKey = $this->normalName($sKey);
            if(!$assetManager->has( $sKey )){
                $this->Logger_Notice("Dependency {$sKey} not found");
                continue;
            }
            $assets->add(new AssetReference($assetManager, $sKey));
        }
        
        return $assets;
    }

    /**
     * Добавляет файл css стиля
     *
     * @param string $sFile Полный путь до файла
     * @param array $aParams Дополнительные параметры
     * @param bool $bPrepend Добавлять файл в начало общего списка или нет
     * @param bool $bReplace Если такой файл уже добавлен, то заменяет его
     *
     * @return bool
     */
    public function AddCss($sFile, $aParams,  $bReplace = false)
    {
        return $this->Add($sFile, $aParams, self::ASSET_TYPE_CSS,  $bReplace);
    }

    /**
     * Добавляет файл js скрипта
     *
     * @param string $sFile Полный путь до файла
     * @param array $aParams Дополнительные параметры
     * @param bool $bPrepend Добавлять файл в начало общего списка или нет
     * @param bool $bReplace Если такой файл уже добавлен, то заменяет его
     *
     * @return bool
     */
    public function AddJs($sFile, $aParams, $bReplace = false)
    {
        return $this->Add($sFile, $aParams, self::ASSET_TYPE_JS,  $bReplace);
    }

    /**
     * Проверяет корректность типа файла
     *
     * @param $sType
     *
     * @return bool
     */
    public function CheckAssetType($sType)
    {
        if(in_array($sType, array(self::ASSET_TYPE_CSS, self::ASSET_TYPE_JS))){
            return $sType;
        }
        
        return false;
    }

    /**
     * Производит предварительную обработку параметров
     *
     * @param $aParams
     *
     * @return array
     */
    public function PrepareParams($aParams)
    {
        $aResult = array();

        $aResult['merge'] = (isset($aParams['merge']) and !$aParams['merge']) ? false : true;
        $aResult['remote'] = (isset($aParams['remote']) and $aParams['remote']) ? true : false;
        $aResult['compress'] = (isset($aParams['compress']) and !$aParams['compress']) ? false : true;
        /*
         * Устанавливаем сжатие если в конфиге true
         */
        $aResult['compress'] = (Config::Get("module.asset.css.compress") and $aResult['compress']) ? true : false;
        $aResult['browser'] = (isset($aParams['browser']) and $aParams['browser']) ? $aParams['browser'] : null;
        $aResult['plugin'] = (isset($aParams['plugin']) and $aParams['plugin']) ? $aParams['plugin'] : null;
        $aResult['name'] = (isset($aParams['name']) and $aParams['name']) ? strtolower($aParams['name']) : null;
        $aResult['defer'] = (isset($aParams['defer']) and $aParams['defer']) ? true : false;
        $aResult['async'] = (isset($aParams['async']) and $aParams['async']) ? true : false;
        $aResult['dependencies'] = (isset($aParams['dependencies'])) ? $aParams['dependencies'] : [];
        
        return $aResult;
    }

    /**
     * Возвращает корректный WEB путь до файла
     *
     * @param string $sFile Исходный путь до файла, обычно он задается в конфиге при подключении css/js, либо через методы Asset_Add*
     * @param array $aParams
     *
     * @return string
     */
    public function GetFileWeb($sFile, $aParams = array())
    {
        return $this->NormalizeFilePath($sFile, $aParams);
    }

    /**
     * Возвращает HTML код подключения файлов в HEAD'ер страницы
     *
     * @return array    Список HTML оберток подключения файлов
     */
    public function BuildHeadItems() {
        $aHeaders = [
            self::ASSET_TYPE_CSS => $this->dump(self::ASSET_TYPE_CSS),
            self::ASSET_TYPE_JS => $this->dump(self::ASSET_TYPE_JS)
        ];        
        
        return $aHeaders;
    }
    
    /**
     * Возвращает HTML разметку с ресурсами <script></script> <link></link> итд
     * 
     * @param string $sType
     * @param array $aKeys
     * @param array $aFilters
     * @return string
     */
    public function dump(string $sType, array $aKeys = [], array $aFilters = [])
    {
        /**
         * Запускаем обработку
         */
        $this->Processing();
        
        /*
         * Если ключи не указаны выбираем все
         */
        if(!$aKeys){
            /*
             * Добавить @ к каждому элементу массива
             */
            $aKeys = array_map( function($sName){
                return '@'.$sName;
            }, $this->assets[$sType]->getNames());
        }
        
        $this->factory->setAssetManager($this->assets[$sType]);
//        $factory->setDebug(true);
//        $this->factory->addWorker(new CacheBustingWorker());
        $assets = $this->factory->createAsset($aKeys, $aFilters, [
            'output' => $sType.'/*.'.$sType
        ]);
        
        /*
         * Отправляем на кэширование
         */
        foreach ($assets as $asset) {
            $this->cache($asset);
        }
        
        return $assets->dump($this->filters->get($sType));
        
    }
    
    /**
     * Кэшируем файлы в публичную папку
     * 
     * @return boolean
     */
    protected function cache($asset) {
        /*
         * Ключ указателя кэширования с учетом скина
         */
        $sKeyCache = basename($asset->getTargetPath());

        try{
            /*
             * Если не кэшировали кэшируем
             */
            if(!$this->Cache_Get($sKeyCache)){
                $this->assetWriter->writeAsset($asset);
                
                //Указываем что записали
                $this->Cache_Set(true, $sKeyCache, ['assets']);
            }

        }catch (Exception $e){
            $this->Logger_Notice( $e->getMessage());
            return false;
        }
        
        return true;
    }
    
    
    /**
     * Производит обработку файлов
     */
    public function Processing()
    {
       
        /**
         * Сначала добавляем файлы из конфига
         */
        $aConfigAssets = (array)Config::Get('head.default');
        foreach ($aConfigAssets as $sType => $aAssets) {
            if (!$this->CheckAssetType($sType)) {
                continue;
            }
            /**
             * Перебираем файлы
             */
            foreach ($aAssets as $sFile => $aParams) {
                if (is_numeric($sFile)) {
                    $sFile = $aParams;
                    $aParams = array();
                }
                /**
                 * Подготавливаем параметры
                 */
                $aParams = $this->PrepareParams($aParams);
                /**
                 * В качестве уникального ключа использется имя или путь до файла
                 */
                $this->Add($sFile, $aParams, $sType);
            }
        }
        /**
         * Формируем файлы из шаблона
         */
        $aConfigAssets = (array)Config::Get('head.template');
        foreach ($aConfigAssets as $sType => $aAssets) {
            if (!$this->CheckAssetType($sType)) {
                continue;
            }
            /**
             * Перебираем файлы
             */
            foreach ($aAssets as $sFile => $aParams) {
                if (is_numeric($sFile)) {
                    $sFile = $aParams;
                    $aParams = array();
                }
                /**
                 * Подготавливаем параметры
                 */
                $aParams = $this->PrepareParams($aParams);
                /**
                 * В качестве уникального ключа использется имя или путь до файла
                 */
                $this->Add($sFile, $aParams, $sType);
            }
        }
        
        

//        foreach ($aTypes as $sType) {
//            /**
//             * Объединяем списки
//             */
//            $aFilesMain[$sType] = array_merge(
//                $this->aAssets[$sType]['prepend'],
//                $aFilesMain[$sType],
//                $this->aAssets[$sType]['append'],
//                $aFilesTemplate[$sType]
//            );
//            /**
//             * Выделяем файлы для конкретных браузеров
//             */
//            $aFilesBrowser = array_filter(
//                $aFilesMain[$sType],
//                function ($aParams) {
//                    return $aParams['browser'] ? true : false;
//                }
//            );
//            /**
//             * Выделяем файлы с атрибутом defer
//             */
//            $aFilesDefer = array_filter(
//                $aFilesMain[$sType],
//                function ($aParams) {
//                    return $aParams['defer'] ? true : false;
//                }
//            );
//            /**
//             * Выделяем файлы с атрибутом async
//             */
//            $aFilesAsync = array_filter(
//                $aFilesMain[$sType],
//                function ($aParams) {
//                    return $aParams['async'] ? true : false;
//                }
//            );
//            /**
//             * Исключаем файлы из основного списка
//             */
//            $aFilesMain[$sType] = array_diff_key($aFilesMain[$sType], $aFilesBrowser);
//            /**
//             * Если необходимо сливать файлы, то выделяем исключения
//             */
//            $aFilesNoMerge = array();
//            if (Config::Get("module.asset.{$sType}.merge")) {
//                $aFilesNoMerge = array_filter(
//                    $aFilesMain[$sType],
//                    function ($aParams) {
//                        return !$aParams['merge'];
//                    }
//                );
//                /**
//                 * Исключаем файлы из основного списка
//                 */
//                $aFilesMain[$sType] = array_diff_key($aFilesMain[$sType], $aFilesNoMerge);
//            }
//            /**
//             * Обрабатываем основной список
//             * Проверка необходимости мержа файлов
//             */
//            $bMergeComplete = false;
//            if (Config::Get("module.asset.{$sType}.merge")) {
//                /**
//                 * Список файлов для основного мержа
//                 */
//                $aFileNeedMerge = array_diff_key($aFilesMain[$sType], $aFilesDefer, $aFilesAsync);
//                if ($sFilePathMerge = $this->Merge($aFileNeedMerge, $sType,
//                    (bool)Config::Get("module.asset.{$sType}.compress"))
//                ) {
//                    $aResult[$sType][$sFilePathMerge] = array('file' => $sFilePathMerge);
//
//                    /**
//                     * Список файлов для мержа с атрибутом defer
//                     */
//                    $bMergeDeferComplete = false;
//                    $aFileNeedMerge = array_diff_key($aFilesDefer, $aFilesNoMerge);
//                    if ($aFileNeedMerge) {
//                        if ($sFilePathMerge = $this->Merge($aFileNeedMerge, $sType,
//                            (bool)Config::Get("module.asset.{$sType}.compress"))
//                        ) {
//                            $aResult[$sType][$sFilePathMerge] = array('file' => $sFilePathMerge, 'defer' => true);
//                            $bMergeDeferComplete = true;
//                        }
//                    } else {
//                        $bMergeDeferComplete = true;
//                    }
//
//                    /**
//                     * Список файлов для мержа с атрибутом async
//                     */
//                    $bMergeAsyncComplete = false;
//                    $aFileNeedMerge = array_diff_key($aFilesAsync, $aFilesNoMerge);
//                    if ($aFileNeedMerge) {
//                        if ($sFilePathMerge = $this->Merge($aFileNeedMerge, $sType,
//                            (bool)Config::Get("module.asset.{$sType}.compress"))
//                        ) {
//                            $aResult[$sType][$sFilePathMerge] = array('file' => $sFilePathMerge, 'async' => true);
//                            $bMergeAsyncComplete = true;
//                        }
//                    } else {
//                        $bMergeAsyncComplete = true;
//                    }
//
//                    if ($bMergeDeferComplete and $bMergeAsyncComplete) {
//                        $bMergeComplete = true;
//                    }
//                }
//            }
//            if (!$bMergeComplete) {
//                $aResult[$sType] = array_merge($aResult[$sType], $aFilesMain[$sType]);
//            }
//            /**
//             * Обрабатываем список исключения объединения
//             */
//            $aResult[$sType] = array_merge($aResult[$sType], $aFilesNoMerge);
//            /**
//             * Обрабатываем список для отдельных браузеров
//             */
//            $aResult[$sType] = array_merge($aResult[$sType], $aFilesBrowser);
//        }
//        return $aResult;
    }

    /**
     * Проверяет на блокировку
     * Если нет блокировки, то создает ее
     *
     * @return bool
     */
    protected function IsLockMerge()
    {
        $this->sDirMergeLock = Config::Get('path.tmp.server') . '/asset-merge-lock';
        if ($bResult = $this->Fs_IsLockDir($this->sDirMergeLock, 60 * 5)) {
            $this->sDirMergeLock = null;
        }
        return $bResult;
    }

    /**
     * Удаляет блокировку
     */
    protected function RemoveLockMerge()
    {
        if ($this->sDirMergeLock) {
            $this->Fs_RemoveLockDir($this->sDirMergeLock);
            $this->sDirMergeLock = null;
        }
    }

    /**
     * Производит объединение и сжатие файлов
     *
     * @param      $aAssetItems
     * @param      $sType
     * @param bool $bCompress
     *
     * @return string|bool Web путь до нового файла
     */
    protected function Merge($aAssetItems, $sType, $bCompress = false)
    {
        $sCacheDir = Config::Get('path.cache_assets.server') . "/" . Config::Get('view.skin');
        $sCacheFile = Config::Get('path.cache_assets.web') . "/" . Config::Get('view.skin') 
                . "/" . md5(serialize(array_keys($aAssetItems)) . '_head') . '.' . $sType;
        /**
         * Если файла еще нет, то создаем его
         */
        if (!file_exists($sCacheFile)) {
            /**
             * Но только в том случае, если еще другой процесс не начал его создавать - проверка на блокировку
             */
            if ($this->IsLockMerge()) {
                return false;
            }
            /**
             * Создаем директорию для кеша текущего скина,
             * если таковая отсутствует
             */
            if (!is_dir($sCacheDir)) {
                @mkdir($sCacheDir, 0777, true);
            }
            $sContent = '';
            foreach ($aAssetItems as $sFile => $aParams) {
                $sFile = isset($aParams['file']) ? $aParams['file'] : $aParams['file'];
                if (strpos($sFile, '//') === 0) {
                    /**
                     * Добавляем текущий протокол
                     */
                    $sFile = (Router::GetIsSecureConnection() ? 'https' : 'http') . ':' . $sFile;
                }
                $sFile = $this->Fs_GetPathServerFromWeb($sFile);
                /**
                 * Считываем содержимое файла
                 */
                if ($sFileContent = @file_get_contents($sFile)) {
                    /**
                     * Создаем объект
                     */
                    if ($oType = $this->CreateObjectType($sType)) {
                        $oType->setContent($sFileContent);
                        $oType->setFile($sFile);
                        unset($sFileContent);
                        $oType->prepare();
                        if ($bCompress and (!isset($aParams['compress']) or $aParams['compress'])) {
                            $oType->compress();
                        }
                        $sContent .= $oType->getContent();
                        unset($oType);
                    } else {
                        $sContent .= $sFileContent;
                    }
                }
            }
            /**
             * Создаем файл и сливаем туда содержимое
             */
            @file_put_contents($sCacheFile, $sContent);
            @chmod($sCacheFile, 0766);
            /**
             * Удаляем блокировку
             */
            $this->RemoveLockMerge();
        }
        return $this->Fs_GetPathWebFromServer($sCacheFile);
    }

    
    /**
     * Создает и возврашает объект
     * Определяем тип ресурса для библиотеки Assetic 
     *
     * @param string $sPath
     * @param array $aParams
     * 
     * @return AssetInterface
     */
    public function CreateAsset( $sPath, $aParams)
    {
        /*
         * Если удаленный и нужно/можно сливать
         */
        if($aParams['remote'] and $aParams['merge']){
            return new HttpAsset($sPath, [] ,true);
        }
        /*
         * Если удаленный и не нужно сливать
         */
        if($aParams['remote'] and !$aParams['merge']){
            return new RemoteAsset($sPath);
        }
        /*
         *  По умолчанию локальный
         */
        if (!is_file($sPath)) {
            $this->Logger_Notice("Asset File {$sPath} not found");
            return false;
        }
        return new FileAsset($sPath);

    }
   

    public function GetRealpath($sPath)
    {
        if (preg_match("@^(http|https):@", $sPath)) {
            $aUrl = parse_url($sPath);
            $sPath = $aUrl['path'];

            $aParts = array();
            $sPath = preg_replace('~/\./~', '/', $sPath);
            foreach (explode('/', preg_replace('~/+~', '/', $sPath)) as $sPart) {
                if ($sPart === "..") {
                    array_pop($aParts);
                } elseif ($sPart != "") {
                    $aParts[] = $sPart;
                }
            }
            return ((array_key_exists('scheme',
                $aUrl)) ? $aUrl['scheme'] . '://' . $aUrl['host'] : "") . "/" . implode("/", $aParts);
        } else {
            return realpath($sPath);
        }
    }

    public function Shutdown()
    {
        /**
         * Удаляем блокировку
         */
        $this->RemoveLockMerge();
    }


}