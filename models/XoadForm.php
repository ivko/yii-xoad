<?php

Yii::import('vendor.ivko.yii-xoad.models.XoadModel');

class XoadForm extends XoadModel {

    public function load($name, $id, $defaultData) {

        $model = $this->_getModel($name, $id, $defaultData);

        $this->_renderForm($name, $model);

        return $this->_responce(true, array('action' => 'render'));
    }
    
    public function submit($name, $formData) {
        
        $model = $this->_loadModel($name, $formData);
        
        if ($model->validate() && $model->save()) {
            return $this->_responce(true, array('action' => 'submit', 'model' => $model->attributes));
        }
        
        $this->_renderForm($name, $model);
        
        return $this->_responce(true, array('action' => 'render'));
    }
    
    public function remove($name, $id) {

        $model = $this->_getModel($name, $id);

        if ($model->delete()) {
            return $this->_responce(true, array('action' => 'remove', 'id' => $id));
        }
        return $this->_responce(false);
    }

    private function _renderForm($name, $model) {
        
        Yii::app()->clientScript->reset();
        Yii::app()->controller->layout = false;
        $view = Yii::app()->xoad->forms[$name]['view'];
        $output = Yii::app()->controller->render($view, array('model' => $model), true);
        $output = preg_replace('#<script type="text/javascript" src="([^"]+)"></script>#is', '', $output);
        print $output;
    }
    
    private function _loadModel($name, $formData = array()) {
        $modelName = Yii::app()->xoad->forms[$name]['model'];
        $primaryKey = $modelName::model()->tableSchema->primaryKey;
        $id = $data = null;
        if (isset($formData[$modelName])) {
            $data = $formData[$modelName];
            $id = $data[$primaryKey];
            unset($data[$primaryKey]);
        }
        return $this->_getModel($name, $id, $data);
    }
    
    private function _getModel($name, $id = null, $defaultData = null) {

        $modelName = Yii::app()->xoad->forms[$name]['model'];
        
        if (isset($id) && $id > 0) {
            
            $model = $modelName::model()->findByPk((int)$id);

            if ($model === null) {
                return 'Error';
            }
        } else {
            $model = new $modelName;
        }

        if (is_array($defaultData)) {
            $model->attributes = $defaultData;
        }

        return $model;
    }
    
    private function _responce($success = true, $data = null, $message = null) {
        return array('data' => $data, 'success' => $success, 'message' => $message);
    }
}