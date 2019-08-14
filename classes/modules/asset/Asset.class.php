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
        foreach (self::$aTypes as $sType) {
            $this->assets[$sType] = new AssetManager();
        }
        
        /*
         * Фильтры для разных ресурсов
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
     * Добавить ресурсы из массива параметров
     * 
     * array(
     *      "js|css" => 
     *          array(
     *              $sNameAsset => array(
     *               "file" => "path/to/asset"
     *                  "attr" => array(), Аттрибуты тега для вставки ресурса
     *                  "merge" => true|false
     *              )
     *          )
     * )
     * @param array $aAssets 
     * @param bool $bReplace Заменить все существующие
     */
    public function AddAssets(array $aAssets, bool $bReplace = false) {
        foreach (self::$aTypes as $sType) {
            if(!isset($aAssets[$sType]) or !is_array($aAssets[$sType])){
                continue;
            }
            foreach ($aAssets[$sType] as $sName => $aAsset) {
                $this->Add($sType, $sName, $aAsset, $bReplace);
            }
        }
    }
    
    /**
      Добавляет новый файл
     *
     * @param string $sName Имя ресурса
     * @param array $aAsset Массив с параметрами ресурса
     * @param string $sType Тип ресурса css|js|other
     * @param bool $bReplace Если такой файл уже добавлен, то заменяет его
     * 
     * @return boolean
     */
    public function Add(
            string $sType,
            string $sName, 
            array $aAsset,              
            bool $bReplace = false)
    {
        if (!$sType = $this->CheckAssetType($sType)) {
            return false;
        }
        /*
         * Определяем объект ресурса
         */
        $asset = $this->CreateAsset($aAsset);
        
        $this->Set($sType, $sName, $asset, $bReplace);  
                
        return $asset;
    }
    
    /**
     * Создает и возврашает объект
     * Определяем тип ресурса для библиотеки Assetic 
     *
     * @param array $aAsset
     * 
     * @return AssetInterface
     */
    public function CreateAsset(array $aAsset)
    {
        $aVars = [ ];
        
        if ((false !== strpos($aAsset['file'], '://') || 0 === strpos($aAsset['file'], '//')) ) {
            /*
            * Если удаленный и нужно/можно сливать
            */
            if($aAsset['merge']){
                $asset  =  new HttpAsset($aAsset['file'], [] ,true, $aVars);
            }else{
                $asset = new RemoteAsset($aAsset['file'], [] ,true, $aVars);
            }
        }
        /*
        *  По умолчанию локальный
        */
        if (!is_file($aAsset['file'])) {
            throw new Exception("Asset File {$aAsset['file']} not found");
        }
        $asset  = new FileAsset($aAsset['file'],[] , null, null, $aVars);
        
        /**
         * Подготавливаем параметры
         */
        $aParams = $this->prepareParams($aAsset);
        
        $this->ensureFilters($asset, $sType, $aParams); 
        
        return $asset;
    }
    /**
     * Добавить ресурс в менеджер определенного типа
     * 
     * @param string $sType
     * @param string $sName
     * @param AssetInterface $asset
     */
    public function Set(
            string $sType, 
            string $sName,
            AssetInterface $asset, 
            bool $bReplace = false) 
    {
        /**
         * В качестве уникального ключа использется имя
         */
        $sName = $this->normalName($sName);   
        /*
         * Если файл уже добавлен пропускаем
         */
        if($this->assets[$sType]->has($sName) and !$bReplace){
            return false;
        }
        
        $this->assets[$sType]->set($this->normalName($sName), $asset);
    }
    
    /**
     * Добавить ресурс в менеджер определенного типа
     * 
     * @param string $sType
     * @param string $sName
     * @param AssetInterface $asset
     */
    public function CollectionSet(
            string $sType, 
            string $sName,
            AssetInterface $asset, 
            bool $bReplace = false) 
    {
        /**
         * В качестве уникального ключа использется имя
         */
        $sName = $this->normalName($sName); 
        
        if(!$this->assets[$sType]->has($sName)){
            $this->assets[$sType]->set($sName, new AssetCollection());
        }
        
        $this->assets[$sType]->get($sName)->add($asset);
        
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
                if($aParams['compress']){
                    $asset->ensureFilter(new Assetic\Filter\JSqueezeFilter());
                }
                break;
            
            case self::ASSET_TYPE_CSS:
                if($aParams['compress']){
                    $asset->ensureFilter(new \Assetic\Filter\CssMinFilter());
                }
                break;
        }
        
        $asset->ensureFilter(new ParamsFilter($aParams));
                
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
    public function AddCss($sName, $aParams,  $bReplace = false)
    {
        return $this->Add(self::ASSET_TYPE_CSS, $sName, $aParams,  $bReplace);
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
    public function AddJs($sName, $aParams, $bReplace = false)
    {
        return $this->Add(self::ASSET_TYPE_JS, $sName, $aParams,  $bReplace);
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
    protected function prepareParams($aParams)
    {
        $aResult = array();

        $aResult['file'] = (isset($aParams['file']) ) ? $aParams['file'] : null;
        $aResult['merge'] = (isset($aParams['merge']) and !$aParams['merge']) ? false : true;
        $aResult['compress'] = (isset($aParams['compress']) and !$aParams['compress']) ? false : true;
        /*
         * Устанавливаем сжатие если в конфиге true
         */
        $aResult['compress'] = (Config::Get("module.asset.css.compress") and $aResult['compress']) ? true : false;
        $aResult['browser'] = (isset($aParams['browser']) and $aParams['browser']) ? $aParams['browser'] : null;
        $aResult['plugin'] = (isset($aParams['plugin']) and $aParams['plugin']) ? $aParams['plugin'] : null;
        $aResult['attr'] = (isset($aParams['attr']) and is_array($aParams['attr'])) ? $aParams['attr'] : [];
        $aResult[self::DEPENDS_KEY] = (isset($aParams[self::DEPENDS_KEY])) ? $aParams[self::DEPENDS_KEY] : [];
        
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
        
        print_r($this->assets);
        $aHeaders = [
            self::ASSET_TYPE_CSS => [],//$this->dump(self::ASSET_TYPE_CSS),
            self::ASSET_TYPE_JS => []//$this->dump(self::ASSET_TYPE_JS)
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
        $this->AddAssets($this->Parse( (array)Config::Get('head.default') ));
        /**
         * Формируем файлы из шаблона
         */
        $this->AddAssets($this->Parse( (array)Config::Get('head.template') ));
   
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