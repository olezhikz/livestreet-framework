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

use Assetic\Asset\FileAsset;
use Assetic\Asset\HttpAsset;
use Assetic\AssetWriter;
use Assetic\AssetManager;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetReference;
use Assetic\Asset\AssetInterface;
use Assetic\FilterManager;
use Assetic\Factory\AssetFactory;
use Assetic\Asset\RemoteAsset;
use Assetic\Filter\CssHtmlFilter;
use Assetic\Filter\JsHtmlFilter;
use Assetic\Filter\ParamsFilter;
use Assetic\Factory\Worker\PublicWorker;

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
    
    public static $aTypes = array(
        self::ASSET_TYPE_JS,
        self::ASSET_TYPE_CSS
    );
    
    
    const DEPENDS_KEY = 'dependencies';

    /**
     * Каталог для проверки блокировки
     *
     * @var null|string
     */
    protected $sDirMergeLock = null;
    
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
        
    }

    /**
     * Задает начальную структуры для хранения списка файлов по типам
     */
    protected function InitAssets()
    {        
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
     * 
     * @return AssetManager
     */
    public function GetAssets() {
        return $this->assets;
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
    public function Add($sFile, $aParams, $sType, $bReplace = false)
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
        
        
        /*
         * Если файл уже добавлен пропускаем
         */
        if($assetManager->has($sFileKey) and !$bReplace){
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
        
        /*
         * Добавляем фильтры исходя из параметров
         */
        $this->ensureFilters( $asset, $sType, $aParams);
        
        $assetManager->set($sFileKey, $asset);
                
        return $asset;
    }
    
    public function AddAssets($aAssets) {
        
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
        if(in_array($sType, self::$aTypes)){
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
        /**
         * Запускаем обработку
         */
        $this->Processing();
        
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
        /*
         * Создаем опции
         */
        $aOptions = [
            'output' => $sType.'/*.'.$sType
        ];
        /*
         * Генерируем хэш набора ресурсов заранее
         */ 
        $sHash = 'asset'.$this->factory->generateAssetName($aKeys, $aFilters, $aOptions);
        if(!$this->Cache_Get($sHash)){
            /*
            * Добавляем обработчик публикации
            */
            $this->factory->addWorker(new PublicWorker(Config::Get('path.cache_assets.server')));

            //Указываем что записали
            $this->Cache_Set(true, $sHash, ['assets']);
        }
        /*
         * Добавляем хэш в опции 
         */
        $aOptions['name'] = $sHash;
        /*
         * Создаем набор ресурсов
         */
        $assets = $this->factory->createAsset($aKeys, $aFilters, $aOptions);
        
        return $assets->dump($this->filters->get($sType));
        
    }
    
    
    /**
     * Производит обработку файлов
     */
    public function Processing()
    {
       
        /**
         * Сначала добавляем файлы из конфига
         */
        $this->loadAssetsConfig((array)Config::Get('head.default'));
        /**
         * Формируем файлы из шаблона
         */
        $this->loadAssetsConfig((array)Config::Get('head.template'));
        
   
    }
    
    /**
     * Загрузить список файлов из конфига
     * 
     * @param array $aConfigAssets
     */
    protected function loadAssetsConfig(array $aConfigAssets) {
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
        $aVars = [ ];
        
        if ((false !== strpos($sPath, '://') || 0 === strpos($sPath, '//')) ) {
            /*
            * Если удаленный и нужно/можно сливать
            */
            if($aParams['merge']){
                $asset  =  new HttpAsset($sPath, [] ,true, $aVars);
            }else{
                $asset = new RemoteAsset($sPath, [] ,true, $aVars);
            }
        }else{
            /*
            *  По умолчанию локальный
            */
           if (!is_file($sPath)) {
               throw new Exception("Asset File {$sPath} not found");
           }
           $asset  = new FileAsset($sPath,[] , null, null, $aVars);
        }      
        
        
        return $asset;
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
    
    public function Parse(array $aAssets) {
        foreach (ModuleAsset::$aTypes as $sType) {
            if(!isset($aAssets[$sType])){
                continue;
            }
            
            /*
             * Перебираем ресурсы
             */
            foreach ($aAssets[$sType] as $sName => $mAsset) {
                $aAssetNew = $mAsset;
                $sNameNew = $sName;
                
                if(is_int($sName)){
                    $sNameNew = preg_replace('/\..+$/i', '', basename($mAsset));
                }
                
                if(is_string($mAsset)){
                    $aAssetNew = [
                        'file' => $mAsset
                    ];
                }
                
                if(isset($mAsset['name'])){
                    $sNameNew = $mAsset['name'];
                }
                
                unset($aAssets[$sType][$sName]);
                
                $aAssets[$sType][$sNameNew] = $aAssetNew;
                
            }
        }
        return $aAssets;
        
    }

    public function Shutdown()
    {
        /**
         * Удаляем блокировку
         */
        $this->RemoveLockMerge();
    }


}