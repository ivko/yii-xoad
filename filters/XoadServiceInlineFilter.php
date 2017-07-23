<?php

class XoadServiceInlineFilter extends CInlineFilter
{
	public static function create($controller,$filterName)
	{
		if(method_exists($controller,'filter'.$filterName))
		{
			$filter=new XoadServiceInlineFilter;
			$filter->name=$filterName;
			return $filter;
		}
		else
			throw new CException(Yii::t('yii','Filter "{filter}" is invalid. Controller "{class}" does not have the filter method "filter{filter}".',
				array('{filter}'=>$filterName, '{class}'=>get_class($controller))));
	}

	/**
	 * Performs the filtering.
	 * This method calls the filter method defined in the controller class.
	 * @param CFilterChain $filterChain the filter chain that the filter is on.
	 */
	public function filter($filterChain)
	{
		$method='filter'.$this->name;
		return $filterChain->controller->$method($filterChain);
	}
}