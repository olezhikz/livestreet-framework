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
    /**
     *
     * @var LS\Module\Asset\AssetFactory 
     */
    protected $factory;

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
        $this->factory = new \LS\Module\Asset\AssetFactory(Config::Get('module.asset'));
        
        /*
         * Фильтры для разных ресурсов
         */
        $this->filters = new \LS\Module\Asset\FilterManager();
        foreach (Config::Get('module.asset.filters') as $key => $filter) {
            $this->filters->set($key, $filter);
        }
        
        $this->loadFromConfig();
                
    }
    
    
    /**
     * Загрузить все ресурсы из конфигов в фабрику
     */
    protected function loadFromConfig()
    {
        $aConfig = array_merge_recursive(
            (array)Config::Get('head.default'), //Сначала добавляем файлы из конфига
            (array)Config::Get('head.template') //Формируем файлы из шаблона
        );
        
        $parser = new \LS\Module\Asset\ConfigParser($this->filters);
        
        $this->assets = $parser->parse($aConfig);
   
    }
        
    /**
     * Добавляем фильтр в набор
     * 
     * @param type $sName
     * @param Assetic\Filter\FilterInterface $filter
     */
    public function AddFilter($sName, Assetic\Filter\FilterInterface $filter) {
        $this->filters->set($sName, $filter);
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
     *          'assetName' => array(
                    'file' => __DIR__.'/Loader/test.js', 
                    WorkerDepends::DEPENDS_KEY => [
                        'assetJsHTTP'
                    ],
                    'filters' => [
                        'js_min'
                    ]
                ),
     * )
     * @param array $aAssets 
     * @param bool $bReplace Заменить все существующие
     */
    public function AddFromConfig(array $aAssets, bool $bReplace = false) {
        $parser = new \LS\Module\Asset\ConfigParser($this->filters);
        
        $assetsAdd = $parser->parse($aAssets);
        
        foreach ($assetsAdd->getNames() as $name) {
            if($this->assets->has($name) and !$bReplace){
                continue;
            }
            $this->assets->set($name, $assetsAdd->get($name));
        }
    }
    
    /**
      Добавляет новый файл
     *
     * @param string $sName Имя ресурса
     * @param Assetic\Asset\AssetInterface $asset Ресурс
     * 
     * @return boolean
     */
    public function Add(string $sName, Assetic\Asset\AssetInterface $asset, bool $bReplace = false)
    {
        if($this->assets->has($sName) and !$bReplace){
            return false;
        }
        
        $this->assets->set($sName, $asset);
                
        return true;
    }
    
    public function Get(string $sName) {
        return $this->assets->get($sName);
    }
    
    protected function prepareFactory() {
        $this->factory->setFilterManager($this->filters);
        $this->factory->setAssetManager($this->assets);
        
        $this->factory->addWorker(new LS\Module\Asset\Worker\WorkerDepends());
        
        if(Config::Get('module.asset.merge')){
            $this->factory->addWorker(new LS\Module\Asset\Worker\WorkerMerge());
        } 
        
        $this->factory->addWorker(new LS\Module\Asset\Worker\WorkerTargetPath());
    }
    
    /**
     * Возвращает набор ресурсов по списку имен
     * 
     * @param array $aInputs
     * @return LS\Module\Asset\AssetManager
     */
    public function CreateAsset(array $aInputs) {
        $this->prepareFactory();    
        
        $assets = $this->factory->createAsset($aInputs);
        
        return $assets;
    }
    
    /**
     * Возвращает набор ресурсов определенного типа
     * 
     * @param LS\Module\Asset\AssetManager
     */
    public function CreateAssetType(string $sType)
    {
        $this->prepareFactory();
        
        $assets = $this->factory->createAssetType($sType);
        
        return $assets;
    }
    
    protected function writeAssets(LS\Module\Asset\AssetManager $assets) {
        
        $sKey = $this->factory->generateAssetKey($assets);
        
        $sDir = Config::Get('path.cache_assets.server').'/'.$sKey;
        
        if(file_exists($sDir)){
            return;
        }
            
        $writer = new Assetic\AssetWriter($sDir);

        $writer->writeManagerAssets($assets);
        
    }
    
    public function Write() {
                
        $this->prepareFactory();
        
        $aAssetSorted = $this->factory->createAssetSorted();
        
        foreach ($aAssetSorted as  $assets) {  
            
            $this->writeAssets($assets);
        }  
        
    }
    
    public function BuildHTML(string $sType) {
        $assets = $this->CreateAssetType($sType);
        
        
    }
    
    public function Shutdown()
    {
        /**
         * Удаляем блокировку
         */
        $this->RemoveLockMerge();
    }


}