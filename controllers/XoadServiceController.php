<?php

class XoadServiceController extends CController
{
	/**
	 * Active action.
	 * @var CAction
	 */
	private $_action;
	/**
	 * Action params.
	 *
	 * Stored in case debugging is on.
	 * @var array
	 */
	private $_actionParams;

    public function setActionParams($params)
	{
		$this->_actionParams = $params;
	}


    public function getActionParams()
	{
		return $this->_actionParams;
	}

    /**
	 * Runs the named action.
	 * Filters specified via {@link filters()} will be applied.
	 * @param string $actionID action ID
	 * @throws CHttpException if the action does not exist or the action name is not proper.
	 * @see filters
	 * @see createAction
	 * @see runAction
	 */
	public function run($actionID)
	{
		if(($action=$this->createAction($actionID))!==null)
		{
			if(($parent=$this->getModule())===null)
				$parent=Yii::app();
			if($parent->beforeControllerAction($this,$action))
			{
				$responseObject = $this->runActionWithFilters($action,$this->filters());
				$parent->afterControllerAction($this,$action);
			}
		}
		else {
            return $this->missingAction($actionID);
        }
        return $responseObject;
	}

	/**
	 * Runs an action with the specified filters.
	 * A filter chain will be created based on the specified filters
	 * and the action will be executed then.
	 * @param CAction $action the action to be executed.
	 * @param array $filters list of filters to be applied to the action.
	 * @see filters
	 * @see createAction
	 * @see runAction
	 */
	public function runActionWithFilters($action,$filters)
	{
		if(empty($filters))
			$responseObject = $this->runAction($action);
		else
		{
			$priorAction=$this->_action;
			$this->_action=$action;
			$responseObject = XoadServiceFilterChain::create($this,$action,$filters)->run();
			$this->_action=$priorAction;
		}
        return $responseObject;
	}


	public function filterAccessControl($filterChain)
	{
		$filter=new XoadServiceAccessControlFilter;
		$filter->setRules($this->accessRules());
		return $filter->filter($filterChain);
	}

    /**
	 * Runs the action after passing through all filters.
	 *
	 * This method is invoked by {@link runActionWithFilters} after all possible filters have been
	 * executed and the action starts to run.
	 *
	 * The major difference from the parent method is that it does the rendering
	 * instead of the actions themselves which just return objects.
	 *
	 * Also catches exceptions and prints them accordingly.
	 *
	 * @param CAction $action action to run
	 */
	public function runAction($action)
	{
        $priorAction = $this->_action;
		// Store action for debugging
		$this->_action = $action;

		if (!$this->beforeAction($action)) {
			// Validate request
			throw new CHttpException(403, 'Restful action execution forbidden.');
		}

		// Run action and get response
		$responseObject = $action->runWithParams($this->getActionParams());
		// Run post-action code
		$this->afterAction($action);
		// Render action response object
        $this->_action = $priorAction;
        
		return $responseObject;
	}


    /**
	 * Creates the action instance based on the action name.
	 *
	 * The method differs from the parent in that it uses CBJsonInlineAction for inline actions.
	 *
	 * @param string $actionId ID of the action. If empty, the {@link defaultAction default action} will be used.
	 * @return CAction the action instance, null if the action does not exist.
	 * @see actions
	 * @todo Implement External Actions as well.
	 */
	public function createAction($actionId)
	{
        
		if ($actionId === '') {
			$actionId = $this->defaultAction;
		}
		if (method_exists($this, 'action'.$actionId) && strcasecmp($actionId, 's')) { // we have actions method
			return new XoadServiceAction($this, $actionId);
		} else {
			$action = $this->createActionFromMap($this->actions(), $actionId, $actionId);
			if ($action !== null && !method_exists($action, 'run'))
				throw new CException(Yii::t('yii',
						'Action class {class} must implement the "run" method.',
						array('{class}' => get_class($action))));
			return $action;
		}
	}
}