<?php
class XoadServiceAccessControlFilter extends CAccessControlFilter
{
    public function filter($filterChain)
	{
		if($this->preFilter($filterChain))
		{
			$responseObject = $filterChain->run();
			$this->postFilter($filterChain);
            return $responseObject;
		}
	}
}