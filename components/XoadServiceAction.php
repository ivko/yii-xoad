<?php

class XoadServiceAction extends CInlineAction
{
	/**
	 * Runs the action.
	 * The action method defined in the controller is invoked.
	 * This method is required by {@link CAction}.
	 */
	public function run()
	{
		$method='action'.$this->getId();
		return $this->getController()->$method();
	}

	/**
	 * Runs the action with the supplied request parameters.
	 * This method is internally called by {@link CController::runAction()}.
	 * @param array $params the request parameters (name=>value)
	 * @return boolean whether the request parameters are valid
	 * @since 1.1.7
	 */
	public function runWithParams($params)
	{
		$methodName = 'action'.$this->getId();
		$controller = $this->getController();
		$method = new ReflectionMethod($controller, $methodName);
		
        if($method->getNumberOfParameters()>0)
			return $this->runWithParamsInternal($controller, $method, $params);
        
		return $controller->$methodName();
	}

    protected function runWithParamsInternal($object, $method, $params)
	{
        
		$ps=array();
		foreach($method->getParameters() as $i=>$param)
		{
			$name=$param->getName();
			if(isset($params[$name]))
			{
				if($param->isArray())
					$ps[]=is_array($params[$name]) ? $params[$name] : array($params[$name]);
				elseif(!is_array($params[$name]))
					$ps[]=$params[$name];
				else {
                    throw new CHttpException(400, $method->class.'::'.$method->name
						.': Invalid argument passed for scalar parameter "'.$name.'"');
                }
			}
			elseif($param->isDefaultValueAvailable())
				$ps[]=$param->getDefaultValue();
			else {
                throw new CHttpException(400, $method->class.'::'.$method->name
					.': No argument passed for mandatory parameter "'.$name.'"');
            }
		}

		return $method->invokeArgs($object,$ps);
	}
}