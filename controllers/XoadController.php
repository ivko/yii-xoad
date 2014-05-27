<?php
class XoadController extends CController
{
    protected function beforeAction($event){
        if (XOAD_Server::runServer()) {
            exit;
        }
        return true;
    }
}