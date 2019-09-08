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

    protected $builders = [];


    /**
     * Инициалищация модуля
     */
    public function Init()
    {
       
        $this->assets = new LS\Module\Asset\AssetManager();
        /*
         * Фильтры
         */
        $this->filters = new \LS\Module\Asset\FilterManager();
        foreach (Config::Get('module.asset.filters') as $key => $filter) {
            $this->filters->set($key, new $filter());
        }
        /*
         * Построители HTML
         */
        $this->builders['js'] = \LS\Module\Asset\Builder\BuilderJsHTML::class;
        $this->builders['css'] = \LS\Module\Asset\Builder\BuilderCssHTML::class;
                        
    }
    
    
    /**
     * Загрузить все ресурсы из конфигов
     */
    public function Load()
    {
        $aConfig = array_merge_recursive(
            (array)Config::Get('head.default'), //Сначала добавляем файлы из конфига
            (array)Config::Get('head.template') //Формируем файлы из шаблона
        );
        
        $parser = new \LS\Module\Asset\ConfigParser($this->filters);
        
        $assets = $parser->parse($aConfig);
        
        foreach ($assets->getNames() as $name) {
            $this->assets->set($name, $assets->get($name));
        }
   
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
     *              'loader' => '/LS/Modle/Asset/Loader/FileLoader'
                    'depends' => [
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
    /**
     * Создать фабрику из фильтров и ресурсов загруженных в модуль
     * с учетом обработки зависимостей, слияния, и обработки путей публикации
     * 
     * @return \LS\Module\Asset\AssetFactory
     */
    protected function prepareFactory() {
        $factory = new \LS\Module\Asset\AssetFactory();
        
        $factory->setFilterManager(clone $this->filters);
        $factory->setAssetManager(clone $this->assets);
        
        $factory->addWorker(new LS\Module\Asset\Worker\WorkerDepends());
        
        if(Config::Get('module.asset.merge')){
            $factory->addWorker(new LS\Module\Asset\Worker\WorkerMerge());
        } 
        
        $factory->addWorker(new LS\Module\Asset\Worker\WorkerTargetPath());
        
        $this->factory = $factory;
        
        return $factory;
    }
    
    /**
     * Возвращает набор ресурсов по списку имен
     * 
     * @param array $aInputs
     * @return LS\Module\Asset\AssetManager
     */
    public function CreateAsset(array $aInputs) {
        $factory = $this->prepareFactory();    
        
        $assets = $factory->createAsset($aInputs);
        
        return $assets;
    }
    
    /**
     * Возвращает набор ресурсов определенного типа
     * 
     * @param LS\Module\Asset\AssetManager
     */
    public function CreateAssetType(string $sType)
    {
        $factory = $this->prepareFactory();
        
        $assets = $factory->createAssetType($sType);
        
        return $assets;
    }
    /**
     * Записывает список ресурсов в директорию 
     * assets/{имя шаблона}/{ключ_списка}/{тип ресурса js|css|image}/*
     * 
     * @param LS\Module\Asset\AssetManager $assets
     * @return type
     */
    protected function writeAssets(LS\Module\Asset\AssetManager $assets, string $sDir) {
        
        $writer = new Assetic\AssetWriter($sDir);
        /*
         * Публикуем ресурсы если не опубликованы
         */
        
        foreach ($assets->getNames() as $name) {
            $asset = $assets->get($name);
            
            if(file_exists($sDir.'/'.$asset->getTargetPath())){
                continue;
            }

            if(!$asset->getParamsOne('public')){
                continue;
            }
            
            $writer->writeAsset($asset);  
            
        }
        
    }
        
    public function BuildHTML(string $sType) {
        /*
         * СОздаем фабрику 
         */
        $factory = $this->prepareFactory();
        /*
         * Генерируем набор ресурсов отсортированных по типам
         */
        $assets = $factory->createAssetType($sType);      
        /*
         * Публикуем ресурсы если не опубликованы
         */
        $this->writeAssets($assets, Config::Get('path.cache_assets.server'));
        /*
         * Выбираем построитель HTML по типу создаем и передаем путь
         */
        $builder = new $this->builders[$sType](Config::Get('path.cache_assets.web'));
        
        /*
         * Добавляем ресурсы в построитель
         */
        foreach ($assets->getNames() as $name) {
            
            $asset = $assets->get($name);
                        
            $builder->add($asset);
        }
        
        return $builder->build();
    }
    
    public function Shutdown()
    {
        /**
         * Удаляем блокировку
         */
        $this->RemoveLockMerge();
    }


}
