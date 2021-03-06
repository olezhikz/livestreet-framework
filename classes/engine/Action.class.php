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
 * Абстрактный класс экшена.
 *
 * От этого класса наследуются все экшены в движке.
 * Предоставляет базовые метода для работы с параметрами и шаблоном при запросе страницы в браузере.
 *
 * @package framework.engine
 * @since 1.0
 */
abstract class Action extends LsObject
{
    
    const RESPONSE_TYPE_HTML = 'html';
    const RESPONSE_TYPE_JSON = 'json';
    const RESPONSE_TYPE_JSON_IFRAME = 'json_iframe';
    const RESPONSE_TYPE_JSONP = 'jsonp';

    /**
     * Список зарегистрированных евентов
     *
     * @var array
     */
    protected $aRegisterEvent = array();
    /**
     * Список евентов, которые нужно обрабатывать внешним обработчиком
     *
     * @var array
     */
    protected $aRegisterEventExternal = array();
    /**
     * Список параметров из URL
     * <pre>/action/event/param0/param1/../paramN/</pre>
     *
     * @var array
     */
    protected $aParams = array();
    /**
     * Список совпадений по регулярному выражению для евента
     *
     * @var array
     */
    protected $aParamsEventMatch = array('event' => array(), 'params' => array());
    /**
     * Шаблон экшена
     * @see SetTemplate
     * @see SetTemplateAction
     *
     * @var string|null
     */
    protected $sActionTemplate = null;
    /**
     * Переменные
     * @var type array
     */
    protected $aVars = [];
    /**
     * Дефолтный евент
     * @see SetDefaultEvent
     *
     * @var string|null
     */
    protected $sDefaultEvent = null;
    /**
     * Текущий евент
     *
     * @var string|null
     */
    protected $sCurrentEvent = null;
    /**
     * Имя текущий евента
     * Позволяет именовать экшены на основе регулярных выражений
     *
     * @var string|null
     */
    protected $sCurrentEventName = null;
    /**
     * Текущий экшен
     *
     * @var null|string
     */
    protected $sCurrentAction = null;
    /**
     * @var PhpComp\Http\Message\ServerRequest
     */
    protected $request;
    /**
     * @var Psr\Http\Message\ResponseInterface
     */
    protected $response;
    /**
     * @var string тип ответа 
     */
    protected $sResponseType = self::RESPONSE_TYPE_HTML;
    /**
     * Конструктор
     * 
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     */
    public function __construct(
        Psr\Http\Message\ServerRequestInterface $request, 
        Psr\Http\Message\ResponseInterface $response
    )
    {
        parent::__construct(); 
        
        $this->request = $request;
        $this->response = $response;
        
        $this->RegisterEvent();
        $this->sCurrentAction   = $request->getAttribute('action');
        $this->sCurrentEvent    = $request->getAttribute('event');
        $this->aParams          = $request->getAttribute('params');
    }

    /**
     * Позволяет запускать не публичные методы экшена через объект
     *
     * @param string $sCall
     *
     * @return mixed
     */
    public function ActionCall($sCall)
    {
        $aArgs = func_get_args();
        unset($aArgs[0]);
        return call_user_func_array(array($this, $sCall), $aArgs);
    }

    /**
     * Проверяет метод экшена на существование
     *
     * @param string $sCall
     *
     * @return bool
     */
    public function ActionCallExists($sCall)
    {
        return method_exists($this, $sCall);
    }

    /**
     * Возвращает свойство объекта экшена
     *
     * @param string $sVar
     *
     * @return mixed
     */
    public function ActionGet($sVar)
    {        
        return $this->$sVar;
    }

    /**
     * Устанавливает свойство объекта экшена
     *
     * @param string $sVar
     * @param null|mixed $mValue
     */
    public function ActionSet($sVar, $mValue = null)
    {
        $this->$sVar = $mValue;
    }

    /**
     * Добавляет евент в экшен
     * По сути является оберткой для AddEventPreg(), оставлен для простоты и совместимости с прошлыми версиями ядра
     * @see AddEventPreg
     *
     * @param string $sEventName Название евента
     * @param string $sEventFunction Какой метод ему соответствует
     */
    protected function AddEvent($sEventName, $sEventFunction)
    {
        $this->AddEventPreg("/^{$sEventName}$/i", $sEventFunction);
    }

    /**
     * Добавляет евент в экшен, используя регулярное выражение для евента и параметров
     *
     */
    protected function AddEventPreg()
    {
        $iCountArgs = func_num_args();
        if ($iCountArgs < 2) {
            throw new Exception("Incorrect number of arguments when adding events");
        }
        $aEvent = array();
        /**
         * Последний параметр может быть массивом - содержать имя метода и имя евента(именованный евент)
         * Если указан только метод, то имя будет равным названию метода
         */
        $aNames = (array)func_get_arg($iCountArgs - 1);
        $aEvent['method'] = $aNames[0];
        /**
         * Определяем наличие внешнего обработчика евента
         */
        $aEvent['external'] = null;
        $aMethod = explode('::', $aEvent['method']);
        if (count($aMethod) > 1) {
            $aEvent['method'] = $aMethod[1];
            $aEvent['external'] = $aMethod[0];
        }

        if (isset($aNames[1])) {
            $aEvent['name'] = $aNames[1];
        } else {
            $aEvent['name'] = $aEvent['method'];
        }
        if (!$aEvent['external']) {
            if (!method_exists($this, $aEvent['method'])) {
                throw new Exception("Method of the event not found: " . $aEvent['method']);
            }
        }
        $aEvent['preg'] = func_get_arg(0);
        $aEvent['params_preg'] = array();
        for ($i = 1; $i < $iCountArgs - 1; $i++) {
            $aEvent['params_preg'][] = func_get_arg($i);
        }
        $this->aRegisterEvent[] = $aEvent;
    }

    /**
     * Регистрируем внешние обработчики для евентов
     *
     * @param string $sEventName
     * @param string|array $sExternalClass
     */
    protected function RegisterEventExternal($sEventName, $sExternalClass)
    {
        $this->aRegisterEventExternal[$sEventName] = $sExternalClass;
    }

    /**
     * Запускает евент на выполнение
     * Если текущий евент не определен то  запускается тот которые определен по умолчанию(default event)
     *
     * @return mixed
     */
    public function ExecEvent()
    {
        
        
        if ($this->sCurrentEvent == null) {
            $this->sCurrentEvent = $this->GetDefaultEvent();
            $this->request = $this->request->withAttribute('event', $this->sCurrentEvent);
        }
        
        foreach ($this->aRegisterEvent as $aEvent) {
            if (preg_match($aEvent['preg'], $this->sCurrentEvent, $aMatch)) {
                $this->aParamsEventMatch['event'] = $aMatch;
                $this->aParamsEventMatch['params'] = array();
                foreach ($aEvent['params_preg'] as $iKey => $sParamPreg) {
                    if (preg_match($sParamPreg, $this->GetParam($iKey, ''), $aMatch)) {
                        $this->aParamsEventMatch['params'][$iKey] = $aMatch;
                    } else {
                        continue 2;
                    }
                }
                
                $this->sCurrentEventName = $aEvent['name'];
                $this->request->setAttribute('event_name', $this->sCurrentEventName);
                
                if ($aEvent['external']) {
                    if (!isset($this->aRegisterEventExternal[$aEvent['external']])) {
                        throw new Exception("External processing for event not found: " . $aEvent['external']);
                    }
                }
                $this->Hook_Run("action_event_" . strtolower($this->sCurrentAction) . "_before",
                    array('event' => $this->sCurrentEvent, 'params' => $this->GetParams()));
                /**
                 * Проверяем на наличие внешнего обработчика евента
                 */
                if ($aEvent['external']) {
                    $sEventClass = $this->Plugin_GetDelegate('event',
                        $this->aRegisterEventExternal[$aEvent['external']]);
                    $oEvent = new $sEventClass;
                    $oEvent->SetActionObject($this);
                    
                    $result = $oEvent->Init();
                    
                    if($result instanceof Psr\Http\Message\ResponseInterface){
                        return $result;
                    }
                
                    if (!$aEvent['method']) {
                        $result = $oEvent->Exec();
                    } else {
                        $result = call_user_func_array(array($oEvent, $aEvent['method']), array());
                    }
                } else {
                    $result = call_user_func_array(array($this, $aEvent['method']), array());
                }
                $this->Hook_Run("action_event_" . strtolower($this->sCurrentAction) . "_after",
                    array('event' => $this->sCurrentEvent, 'params' => $this->GetParams()));
                
                if($result instanceof Psr\Http\Message\ResponseInterface){
                    return $result;
                }
                
                $this->Hook_Run("action_shutdown_" . $this->sCurrentAction . "_before");
                $this->EventShutdown();
                $this->Hook_Run("action_shutdown_" . $this->sCurrentAction . "_after");
                               
                switch ($this->sResponseType) {
                    case self::RESPONSE_TYPE_HTML:
                            $result = $this->fetchHTML($result);
                        break;
                    case self::RESPONSE_TYPE_JSON:
                            $result = $this->fetchJSON($result);
                        break;
                    case self::RESPONSE_TYPE_JSONP:
                            $result = $this->fetchJSONP();
                        break;
                    case self::RESPONSE_TYPE_JSON_IFRAME:
                            $result = $this->fetchJSONIframe();
                        break;
                   
                }
                
                $this->response->getBody()->write( $result );

                return $this->response;                
            }
        }

        return $this->EventNotFound();
    }
    /**
     * Обрабатывает данные в шаблон
     * @param type $result
     * @return type
     */
    protected function fetchHTML($result = null) {
        $this->response = $this->response->withHeader('Content-type', 'text/html; charset=utf-8');
        
        if($result){
            return $result;
        }
        
        $this->Hook_Run("action_fetch_html_before",
                array('event' => $this->sCurrentEvent, 'params' => $this->GetParams()));
        
        foreach ($this->aVars as $key => $value) {
            $this->Viewer_Assign($key, $value);
        }
        
        $this->Viewer_Assign('aMsgError', $this->Message_GetError());
        $this->Viewer_Assign('aMsgNotice', $this->Message_GetNotice());
        
        $this->Viewer_Assign('sAction', Router::getInstance()->Standart(Router::GetAction()));
        $this->Viewer_Assign('sEvent', Router::GetActionEvent());
        $this->Viewer_Assign('aParams', Router::GetParams());
        $this->Viewer_Assign('PATH_WEB_CURRENT', func_urlspecialchars(Router::GetPathWebCurrent()));
        
        return $this->Viewer_Fetch($this->GetTemplate());
    }
    
    protected function fetchAjax() {
        
        $this->response = $this->response->withHeader('Content-type', 'application/json');
        
        /**
         * Загружаем статус ответа и сообщение
         */
        $bStateError = false;
        $sMsgTitle = '';
        $sMsg = '';
        $aMsgError = $this->Message_GetError();
        $aMsgNotice = $this->Message_GetNotice();
        if (count($aMsgError) > 0) {
            $bStateError = true;
            $sMsgTitle = $aMsgError[0]['title'];
            $sMsg = $aMsgError[0]['msg'];
        } elseif (count($aMsgNotice) > 0) {
            $sMsgTitle = $aMsgNotice[0]['title'];
            $sMsg = $aMsgNotice[0]['msg'];
        }
        $this->assign('sMsgTitle', $sMsgTitle);
        $this->assign('sMsg', $sMsg);
        $this->assign('bStateError', $bStateError);
    }
    
    protected function fetchJSON($result) {
        if($result){
            return json_encode($result);
        }
        
        $this->fetchAjax();
        
        return json_encode($this->aVars);
    }
    
    protected function fetchJSONP() {
        if($result){
            return json_encode($result);
        }
        
        $this->fetchAjax();
        
        $aParams = $this->request->getQueryParams();
        
        $sCallbackName = isset($aParams['jsonpCallbackName']) ? $aParams['jsonpCallbackName'] : 'jsonpCallback';
            $sCallback = $aParams[$sCallbackName];
            if (!preg_match('#^[a-z0-9\-\_]+$#i', $sCallback)) {
                $sCallback = 'callback';
            }
        return $sCallback . '(' . json_encode($this->aVars) . ');';
    }
    
    protected function fetchJSONIframe() {
        if($result){
            return json_encode($result);
        }
        
        $this->fetchAjax();
        
        return '<textarea>' . htmlspecialchars(json_encode($this->aVars)) . '</textarea>';
       
    }

    /**
     * Устанавливает евент по умолчанию
     *
     * @param string $sEvent Имя евента
     */
    public function SetDefaultEvent($sEvent)
    {
        $this->sDefaultEvent = $sEvent;
    }

    /**
     * Получает евент по умолчанию
     *
     * @return string
     */
    public function GetDefaultEvent()
    {
        return $this->sDefaultEvent;
    }

    /**
     * Возвращает элементы совпадения по регулярному выражению для евента
     *
     * @param int|null $iItem Номер совпадения
     * @return string|null
     */
    protected function GetEventMatch($iItem = null)
    {
        if ($iItem) {
            if (isset($this->aParamsEventMatch['event'][$iItem])) {
                return $this->aParamsEventMatch['event'][$iItem];
            } else {
                return null;
            }
        } else {
            return $this->aParamsEventMatch['event'];
        }
    }

    /**
     * Возвращает элементы совпадения по регулярному выражению для параметров евента
     *
     * @param int $iParamNum Номер параметра, начинается с нуля
     * @param int|null $iItem Номер совпадения, начинается с нуля
     * @return string|null
     */
    protected function GetParamEventMatch($iParamNum, $iItem = null)
    {
        if (!is_null($iItem)) {
            if (isset($this->aParamsEventMatch['params'][$iParamNum][$iItem])) {
                return $this->aParamsEventMatch['params'][$iParamNum][$iItem];
            } else {
                return null;
            }
        } else {
            if (isset($this->aParamsEventMatch['event'][$iParamNum])) {
                return $this->aParamsEventMatch['event'][$iParamNum];
            } else {
                return null;
            }
        }
    }

    /**
     * Получает параметр из URL по его номеру, если его нет то null
     *
     * @param int $iOffset Номер параметра, начинается с нуля
     * @return mixed
     */
    public function GetParam($iOffset, $default = null)
    {
        $iOffset = (int)$iOffset;
        return isset($this->aParams[$iOffset]) ? $this->aParams[$iOffset] : $default;
    }

    /**
     * Получает список параметров из УРЛ
     *
     * @return array
     */
    public function GetParams()
    {
        return $this->aParams;
    }

    protected function getRequest($key, $default = null) {
        return $this->request->getParam($key, $default);
    }

    /**
     * Установить значение параметра(эмуляция параметра в URL).
     * После установки занова считывает параметры из роутера - для корректной работы
     *
     * @param int $iOffset Номер параметра, но по идеи может быть не только числом
     * @param string $value
     */
    public function SetParam($iOffset, $value)
    {
        Router::SetParam($iOffset, $value);
        
        $this->aParams = Router::GetParams();
        
        $this->request = $this->request->withParams($this->aParams);
    }

    /**
     * Устанавливает какой шаблон выводить
     *
     * @param string $sTemplate Путь до шаблона относительно общего каталога шаблонов
     */
    protected function SetTemplate($sTemplate)
    {
        $this->sActionTemplate = $sTemplate;
    }

    /**
     * Устанавливает какой шаблон выводить
     *
     * @param string $sTemplate Путь до шаблона относительно каталога шаблонов экшена
     */
    protected function SetTemplateAction($sTemplate)
    {
        $aDelegates = $this->Plugin_GetDelegationChain('action', $this->GetActionClass());
        $sActionTemplatePath = $sTemplate . '.tpl';
        foreach ($aDelegates as $sAction) {
            
            if (preg_match('/^(Plugin([\w]+)_)?Action([\w]+)$/i', $sAction, $aMatches)) {
                $sTemplatePath = $this->Plugin_GetDelegate('template',
                    'actions/Action' . ucfirst($aMatches[3]) . '/' . $sTemplate . '.tpl');
                if (empty($aMatches[1])) {
                    $sActionTemplatePath = $sTemplatePath;
                } else {
                    $sTemplatePluginPath = Plugin::GetTemplatePath($sAction);
                    $sTemplatePath = $sTemplatePluginPath . $sTemplatePath;
                    /*
                     * Загружаем конфиг шаблона плагина
                     */
                    if (file_exists($sFile = $sTemplatePluginPath . '/settings/config/config.php')) {
                        Config::LoadFromFile($sFile, false);
                    }
                    
                    if (is_file($sTemplatePath)) {
                        $sActionTemplatePath = $sTemplatePath;
                        break;
                    }
                }
            }
        }
        $this->sActionTemplate = $sActionTemplatePath;
    }
    /**
     *  Тип ответа json/html/jsonp
     * @param string $sType
     */
    protected function setResponseType(string $sType = self::RESPONSE_TYPE_HTML) {
        $this->sResponseType = $sType;
    }
    
    public function GetResponseType() {
        return $this->sResponseType;
    }
    
    /**
     *  Добавить переменную в шаблон или ответ ajax
     * @param string $name
     * @param type $value
     */
    protected function assign(string $name, $value) {
        $this->aVars[$name] = $value;
    }

    /**
     * Получить шаблон
     * Если шаблон не определен то возвращаем дефолтный шаблон евента: actions/Action{Action}/{event}.tpl
     *
     * @return string
     */
    public function GetTemplate()
    {
        if (is_null($this->sActionTemplate)) {
            $this->SetTemplateAction(strtolower($this->sCurrentEvent));
        }
        return $this->sActionTemplate;
    }

    /**
     * Получить каталог с шаблонами экшена(совпадает с именем класса)
     * @see Router::GetActionClass
     *
     * @return string
     */
    public function GetActionClass()
    {
        return Router::GetActionClass();
    }

    /**
     * Возвращает имя евента
     *
     * @return null|string
     */
    public function GetCurrentEventName()
    {
        return $this->sCurrentEventName;
    }

    /**
     * Вызывается в том случаи если не найден евент который запросили через URL
     * По дефолту происходит перекидывание на страницу ошибки, это можно переопределить в наследнике
     * @see Router::Action
     *
     * @return string
     */
    protected function EventNotFound()
    {
        return Router::Action('error', '404');
    }

    /**
     * Перенаправляет на страницу ошибки "доступ запрещен"
     * @see Router::Action
     *
     * @return string
     */
    protected function EventForbiddenAccess()
    {
        return Router::Action('error', '403');
    }

    /**
     * Выполняется при завершение экшена, после вызова основного евента
     * todo:Если в основном Event вызвать шаблонизатор Viewer_Fetch 
     * то загрузка переменных в шаблон не произойдет в этом методе
     *
     */
    public function EventShutdown()
    {

    }

    /**
     * Выводит отладочную информацию в стандартном сообщении
     * Этим методом можно завершать выполнение евента в случае системной ошибки, например, не удалось найти топик по его ID при голосовании в ajax обработчике
     *
     */
    protected function EventErrorDebug()
    {
        if (Config::Get('sys.debug.action_error')) {
            $aTrace = debug_backtrace(false);
            $aCaller = array_shift($aTrace);
            $aCallerSource = array_shift($aTrace);
            $aPathinfo = pathinfo($aCaller['file']);

            $sMsg = $aPathinfo['basename'] . ' [' . $aCallerSource['class'] . $aCallerSource['type'] . $aCallerSource['function'] . ': ' . $aCaller['line'] . ']';
            $this->Message_AddErrorSingle($sMsg, 'System error');
            if ($this->GetResponseType() === self::RESPONSE_TYPE_HTML) {
                return Router::Action('error', '500');
            } else {
                return;
            }
        } else {
            if ($this->GetResponseType() === self::RESPONSE_TYPE_HTML) {
                return Router::Action('error', '500');
            } else {
                $this->Message_AddErrorSingle('System error');
                return;
            }
            
        }
    }

    /**
     * Абстрактный метод инициализации экшена
     *
     */
    abstract public function Init();

    /**
     * Абстрактный метод регистрации евентов.
     * В нём необходимо вызывать метод AddEvent($sEventName,$sEventFunction)
     *
     */
    abstract protected function RegisterEvent();

}