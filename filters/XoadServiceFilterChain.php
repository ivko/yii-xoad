<?php

class XoadServiceFilterChain extends CFilterChain
{
	public static function create($controller,$action,$filters)
	{
		$chain=new XoadServiceFilterChain($controller,$action);

		$actionID=$action->getId();
		foreach($filters as $filter)
		{
			if(is_string($filter))  // filterName [+|- action1 action2]
			{
				if(($pos=strpos($filter,'+'))!==false || ($pos=strpos($filter,'-'))!==false)
				{
					$matched=preg_match("/\b{$actionID}\b/i",substr($filter,$pos+1))>0;
					if(($filter[$pos]==='+')===$matched)
						$filter=XoadServiceInlineFilter::create($controller,trim(substr($filter,0,$pos)));
				}
				else
					$filter=XoadServiceInlineFilter::create($controller,$filter);
			}
			elseif(is_array($filter))  // array('path.to.class [+|- action1, action2]','param1'=>'value1',...)
			{
				if(!isset($filter[0]))
					throw new CException(Yii::t('yii','The first element in a filter configuration must be the filter class.'));
				$filterClass=$filter[0];
				unset($filter[0]);
				if(($pos=strpos($filterClass,'+'))!==false || ($pos=strpos($filterClass,'-'))!==false)
				{
					$matched=preg_match("/\b{$actionID}\b/i",substr($filterClass,$pos+1))>0;
					if(($filterClass[$pos]==='+')===$matched)
						$filterClass=trim(substr($filterClass,0,$pos));
					else
						continue;
				}
				$filter['class']=$filterClass;
				$filter=Yii::createComponent($filter);
			}

			if(is_object($filter))
			{
				$filter->init();
				$chain->add($filter);
			}
		}
		return $chain;
	}


	/**
	 * Executes the filter indexed at {@link filterIndex}.
	 * After this method is called, {@link filterIndex} will be automatically incremented by one.
	 * This method is usually invoked in filters so that the filtering process
	 * can continue and the action can be executed.
	 */
	public function run()
	{
		if($this->offsetExists($this->filterIndex))
		{
			$filter=$this->itemAt($this->filterIndex++);
			Yii::trace('Running filter '.($filter instanceof XoadServiceInlineFilter ? get_class($this->controller).'.filter'.$filter->name.'()':get_class($filter).'.filter()'),'system.web.filters.CFilterChain');
			return $filter->filter($this);
		}
		else {
            return $this->controller->runAction($this->action);
        }
	}
}