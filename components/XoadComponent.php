<?php
Yii::import('vendor.crisu83.yii-extension.behaviors.*');

class XoadComponent extends CApplicationComponent
{
    private $scriptsLoaded = false;
    
    public $forms = array();
    public $services = array();
    public $xoad_base = 'vendor.ivko.xoad';
    
    public function init() 
    {
        parent::init();
        Yii::import($this->xoad_base . '.xoad', true);
        $this->attachBehavior('ext', new ComponentBehavior);
    }
    
    public function registerScripts() {
        if ($this->scriptsLoaded == true) {
            return;
        }
        $this->scriptsLoaded = true;
        $this->publishAssets(XOAD_BASE . '/js');
        $this->registerScriptFile('xoad.js');
    }
    
    public function register($class, $var_name = null, $path = null, $args = null) {
        if (is_string($class)) {
            
            if (!class_exists($class, false)) {
                $class = Yii::import($class, true);
            }
            
            if(empty($args) || count($args) == 0) {
                $class = new $class;
            } else {
                $r = new ReflectionClass($class);
                $class = $r->newInstanceArgs($args);
            }
        }
        if (empty($var_name)) {
            $var_name = get_class($class);
        }
        if (empty($path)) {
            $path = Yii::app()->request->url;
        }
        $this->registerClass($class, $var_name ,$path);
    }
    
    public function registerClass(XoadModel $instance, $var_name, $path) {

        $this->registerScripts();

        $script = "xoad.env.set('" . $var_name . "', " . XOAD_Client::register($instance, $path) . ");";

        Yii::app()->clientScript->registerScript( $var_name, $script, CclientScript::POS_HEAD );
    }

    public function registerServices() {
        $registry = [];
        foreach($this->services as $name => $class) {
            $serviceClass = Yii::import($this->services[$name], true);
            $reflector = new ReflectionClass($serviceClass);
            $rMethods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
            $registry[$name] = [];
            foreach($rMethods as $rMethod) {
                if ($serviceClass === $rMethod->class && strpos($rMethod->name, 'action') === 0) {
                    $method = lcfirst(str_replace('action', '', $rMethod->name));
                    //$registry[$name][] = $method;
                    $params = $reflector->getMethod($rMethod->name)->getParameters();
                    $registry[$name][$method] = [];
                    foreach ($params as $param) {
                        $registry[$name][$method][] = ['name' => $param->getName(), 'optional' => $param->isOptional()];
                    }
                }
            }
        }
        $this->registerScripts();
        $script = "xoad.env.set('XoadServiceRegistry', " . json_encode($registry) . ");";
        Yii::app()->clientScript->registerScript( 'XoadServiceRegistry', $script, CclientScript::POS_HEAD );
        $this->registerClass(new XoadService, 'XoadService');
    }

    public function initService($name, $params) {
        if (!isset($this->services[$name])) {
            throw new CHttpException(404, "Service '$name' not found!");
        }
        $serviceClass = Yii::import($this->services[$name], true);
        $service = new $serviceClass($serviceClass);
        $service->init();
        $service->actionParams = $params;
        return $service;
    }
    
    public function allowClasses($aliases = array()) {
        $classes = array();
        foreach ($aliases as $classFile) {
            if(!class_exists($classFile, false)) {
                $classFile = Yii::import($classFile, true);
            }
            $classes[] = $classFile;
        }
        XOAD_Server::allowClasses($classes);
    }
    
    public function runServer() {
        if (XOAD_Server::runServer()) {
            foreach (Yii::app()->log->routes as $route) {
                if ($route instanceof CWebLogRoute || $route instanceof YiiDebugToolbarRoute) {
                    $route->enabled = false;
                }
            }
            return true;
        }
        return false;
    }
}
