<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Damco_Controller_Plugin_Locale extends Zend_Controller_Plugin_Abstract
{
 
   /* public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
        $locale = new Zend_Locale();
        print_r($locale);
        Zend_Registry::set('Zend_Locale', $locale);
 
        $translate = Zend_Registry::get('Zend_Translate');
        $translate->setLocale($locale);
 
        $view->getHelper('translate')->setTranslator($translate);
        $view->navigation()->setTranslator($translate);
 
        Zend_Form::setDefaultTranslator($translate);
 
        Zend_Registry::set('Zend_Translate', $translate);
 
    }*/
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
 
        //$language = $request->getParam("langid");
        $langid = $request->getParam('langid');
        if(!$langid){
            $objAuth = Zend_Auth::getInstance();
            $objUser = $objAuth->getIdentity();
            if(isset($objUser) && isset($objUser->lang_id)){
                $langid = $objUser->lang_id;
            }else{
                $langid=1;
            }
        }
        $langObj = new Default_Model_Languages();        
        $dbLanguage =  $langObj->getLanguages('survey',$langid);
        //print_R($dbLanguage);die;
        if(!empty($dbLanguage[0]["lang_code"]))
        {
            $language = $dbLanguage[0]["lang_code"];
            if(file_exists(APPLICATION_PATH.'/languages/'.$language.'.mo'))
            {
                $locale   = new Zend_Locale($language);
                $translate = new Zend_Translate('Gettext', APPLICATION_PATH.'/languages/'.$language.'.mo', $locale);

                Zend_Registry::set('Zend_Locale', $locale);
                Zend_Registry::set('Zend_Translate', $translate);

                Zend_Controller_Router_Route::setDefaultTranslator($translate);
            }
        }
 
    }
 
}