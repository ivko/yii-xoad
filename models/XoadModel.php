<?php
class XoadModel {

    public function xoadGetMeta()
	{
		$filter = create_function('$name', 'return (preg_match("@^(xoad|_)@", $name) == false);');
        $methods = array_filter(get_class_methods($this), $filter);
		XOAD_Client::mapMethods($this, $methods);
		XOAD_Client::publicMethods($this, $methods);
	}
    
}