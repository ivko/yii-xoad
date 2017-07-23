<?php

class XoadService extends XoadModel {

    protected function xoadInitService($obj, $params) {
		// Install uncaught PHP error handler
		Yii::app()->attachEventHandler('onError', array($this, 'xoadOnError'));
		// Install uncaught exception handler
		Yii::app()->attachEventHandler('onException', array($this, 'xoadOnException'));
        // Load Service
        return Yii::app()->xoad->initService($obj, $params);
    }

    /**
	 * Handle uncaught exception.
	 *
	 * @param CExceptionEvent $event
	 */

    public function xoadOnException($event)
	{
		$e = $event->exception;
		// Don't bubble up
		$event->handled = true;
        // Directly return an exception
		return $this->_createErrorObject($e->statusCode, $e->getMessage(), $e->getTraceAsString(), get_class($e));
	}
	/**
	 * Handle uncaught PHP notice/warning/error.
	 *
	 * @param CErrorEvent $event
	 */
	public function xoadOnError($event)
	{
        print_r(['onError', $event]);
		//
		// Extract backtrace
		//
		$trace=debug_backtrace();
		// skip the first 4 stacks as they do not tell the error position
		if(count($trace)>4)
			$trace=array_slice($trace,4);
		$traceString = "#0 ".$event->file."(".$event->line."): ";
		foreach($trace as $i=>$t)
		{
			if ($i !== 0) {
				if(!isset($t['file']))
					$trace[$i]['file']='unknown';
				if(!isset($t['line']))
					$trace[$i]['line']=0;
				if(!isset($t['function']))
					$trace[$i]['function']='unknown';
				$traceString.="\n#$i {$trace[$i]['file']}({$trace[$i]['line']}): ";
			}
			if(isset($t['object']) && is_object($t['object']))
				$traceString.=get_class($t['object']).'->';
			$traceString.="{$trace[$i]['function']}()";
			unset($trace[$i]['object']);
		}
        // Don't bubble up
		$event->handled = true;
        
		//
		// Directly return an exception
		//
		return $this->_createErrorObject($event->code, $event->message, $traceString, 'PHP Error');
	}

    protected function _createResponseObject($data, $code = 200, $message = 'OK'){
        return array(
            "message" => $message,
            "code" => $code,
            "data" => $data
        );
    }

	protected function _createErrorObject($code, $message, $traceString, $type)
	{
		$errorObject = array(
			'message' => $message,
			'code' => $code,
			'type' => $type,
		);
		if ((defined('YII_DEBUG') && (constant('YII_DEBUG') === true))) {
			$errorObject['trace'] = explode("\n", $traceString);
		}
        $requestBody = $GLOBALS['_XOAD_SERVER_REQUEST_BODY'];

        print XOAD_Client::register(["returnValue" => $errorObject, "returnObject" => $requestBody['source']]);
	}

    public function authenticate($obj, $params) {
        return $this->_createResponseObject($this->xoadInitService($obj, compact('params'))->run('authenticate'));
    }

    public function validate($obj, $data) {
        return $this->_createResponseObject($this->xoadInitService($obj, compact('data'))->run('validate'));
    }

    public function create($obj, $data) {
        return $this->_createResponseObject($this->xoadInitService($obj, compact('data'))->run('create'));
    }
    
    public function update($obj, $pk, $data) {
        return $this->_createResponseObject($this->xoadInitService($obj, compact('pk', 'data'))->run('update'));
    }

    public function remove($obj, $pk) {
        return $this->_createResponseObject($this->xoadInitService($obj, compact('pk'))->run('remove'));
    }

    public function fetch($obj, $pk) {
        $responseObject = $this->xoadInitService($obj, compact('pk'))->run('fetch');
        return $this->_createResponseObject($responseObject);
    }

    public function filter($obj, $filter=array(), $page = 1) {
        return $this->_createResponseObject($this->xoadInitService($obj, compact('filter','page'))->run('filter'));
    }
}