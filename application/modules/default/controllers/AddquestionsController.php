<?php

class Default_AddquestionsController extends Damco_Core_CoreController
{

    public $objWorkSheet;
    public $objPHPExcel;
    
    public function init()
    {
        /* Initialize action controller here */
                parent::init();
		$this->_redirector = $this->_helper->getHelper('Redirector');
		$this->_helper->layout->setLayout('layout');
		$this->_auth = Zend_Auth::getInstance();
    }

     /**
     * @return Default_Form_Addlanguage
     */
    public function getForm(){
        return new Default_Form_Addlanguage();
    }
    
      /**
     * @return Default_Form_Addlanguage
     */
    public function getModel(){
        return new Default_Model_Languages();
    }
    
    public function indexAction()
    { 
          $langList = $this->getModel()->getAllLanguages();
          $form = $this->getForm()->setLanguage_options($langList,
                                                             true,
                                                             false);
           $request = $this->getRequest();
            if ($request->isPost()) {
                 if ($form->isValid( $request->getPost() )) {
                    $values = $form->getValues();
                    foreach($values as $k=>$v)
                    {
                        if(empty($v))
                        {
                            unset($values[$k]);
                        }
                    }
                    //print_R($values);die;
                    $this->getModel()->saveLanguages($values);
                    $form->reset();
                    $form->getElement('langid')->setValue($values['langid']);
          } 
       }
          $this->view->form = $form;
          $this->view ->messages = $this->_helper ->flashMessenger->getMessages();
    }
    
    public function createmastertemplateAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $get = $this->getRequest()->getParams();
        if ( !isset( $get['langid'] ) ) {
            die('Please provide language ID.');
        }
        
        $modelObj = new assessment_Model_Questions();
        
        $eventTypes = array(
            '1' => 'Sales',
            '2' => 'Product',
            '3' => 'Service',
        );
        
        $this->objPHPExcel = new Damco_Excelwriter_CreateExcel();

        foreach ( $eventTypes as $eventID => $eventName ) {
            $result = $modelObj->getQuestionsForTemplate($eventID, $get['langid']);
            if ( $get['langid'] != '1' ) {
                $resultEnglish = $modelObj->getQuestionsForTemplate($eventID, '1');
            }
    //        echo '<pre>'; print_r($result);
    //        echo '<pre>'; print_r($resultEnglish);die;
            
            $file_location = $result[0]['lang_name'].' Translations Template.xlsx';
            $this->objPHPExcel->createSheet($eventID-1);
            $this->objPHPExcel->getSheet($eventID-1)->setTitle($eventName.' Translations');                
            $this->objPHPExcel->setActiveSheetIndex($eventID-1);
            $this->objWorkSheet = $this->objPHPExcel->getActiveSheet($eventID-1);

            $this->objPHPExcel->getDefaultStyle()->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

            if ( isset( $resultEnglish ) ) {
                $this->objWorkSheet->setCellValueByColumnAndRow('0', '1', 'ENGLISH');
                $this->objPHPExcel->getActiveSheet()->getStyle(1)->getFont()->setBold(true);
                $this->_createTemplate($resultEnglish, '2', '0');
            }
            $this->objWorkSheet->setCellValueByColumnAndRow(($get['langid'] == '1')?'0':'1',
                    '1', 'TRANSLATION');
            $this->objPHPExcel->getActiveSheet()->getStyle(1)->getFont()->setBold(true);
            $this->_createTemplate($result, '2', ($get['langid'] == '1')?'0':'1' );
        }
        
        
        $this->objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $file_location . '"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007'); 
        $objWriter->save('php://output');

//       echo $get['event_typeid'] . ' ' . $get['langid'];
//        die;
    }
    
    private function _createTemplate($result,$i,$j) {
        foreach ( $result as $k => $val ) {
//           die($val['question']);
            $this->objWorkSheet->setCellValueByColumnAndRow($j, $i, 
                    str_replace( array("\r", "\n", "\r\n"), '', 
//                    $val['questionid'].' '.
                    $val['question']) );
            $this->objWorkSheet->setCellValueByColumnAndRow($j+1, $i++, 
                    $val['questionid']);
            
            if ( in_array($val['questionid'], array('91', '92', '93', '169', '171', '172') ) ) {
                continue;
            }
            
            $quesID = $val['questionid'];
            unset($val['event_typeid'], $val['question_type'], $val['question_number'],
                  $val['question_langid'], $val['langid'], $val['questionid'],
                  $val['question'], $val['lang_name']);
            
            foreach ( $val as $key => $value ) {

                if ( $key == 'grade_label_text' && !empty($value) ) {
                    $temp = explode(',', $value);
                    foreach( $temp as $v ) {
                        $this->objWorkSheet->setCellValueByColumnAndRow($j, $i++, 
                                $v);
                    }
                    continue;
                }

                if ( $quesID == '229' && $key == 'response11' ) {
                    $temp = explode('|', $value);
                    foreach( $temp as $v ) {
                        $this->objWorkSheet->setCellValueByColumnAndRow($j, $i++, 
                                $v);
                    }
                    continue;
                }

                if ( is_numeric($value)
                     || in_array($value, array( 
                             'NationsName',
                             'Nationname',
                             'Marketname',
                             'Make',
                         ) ) ) {
                    continue;
                }
               
                $value = str_replace( array("\r", "\n", "\r\n"), '', $value);
                if ( !empty($value) ) {
                    $this->objWorkSheet->setCellValueByColumnAndRow($j, $i++, 
                            $value);
                }
            }
        }
    }
    
    public function createemailtemplatesAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        $modelObj = new Event_Model_EmailTemplates();
        $resultEng = $modelObj->getLanguageEmailTemplates('1');
        
        $langs = array('1', '2', '3', '4', '6', '7', '8', '10', '11', '12', '13',
            '14', '15', '16', '17', '22', '23', '24', '25', '26');
        $langs = array('7');
        
        $this->objPHPExcel = new Damco_Excelwriter_CreateExcel();
        $file_location = 'Email Templates Translations.xlsx';
        
        $i=0;
        foreach ( $langs as $value ) {
            if ( $value != '1' ) {
                $result = $modelObj->getLanguageEmailTemplates($value);
            }
            
            $this->objPHPExcel->createSheet($i);
            $this->objPHPExcel->getSheet($i)->setTitle(
                    ($value != '1')?$result[0]['lang_name']:'English'.' Email Templates');                
            $this->objPHPExcel->setActiveSheetIndex($i);
            $this->objWorkSheet = $this->objPHPExcel->getActiveSheet($i++);

            $this->objPHPExcel->getDefaultStyle()->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

            $this->objWorkSheet->setCellValueByColumnAndRow('0', '1', 'ENGLISH');
            $this->objPHPExcel->getActiveSheet()->getStyle(1)->getFont()->setBold(true);
            $this->_createEmailTemplate($resultEng, '2', '0');
            
            if ( isset( $result ) ) {
                $this->objWorkSheet->setCellValueByColumnAndRow('1', '1', 'TRANSLATIONS');
                $this->objPHPExcel->getActiveSheet()->getStyle(1)->getFont()->setBold(true);
                $this->_createEmailTemplate($result, '2', '1');
            }
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $file_location . '"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007'); 
        $objWriter->save('php://output');
    }
    
    private function _createEmailTemplate($result, $i, $j) {
        
        foreach ( $result as $key => $value ) {
//            echo $value['title'] . '<br />';
//            echo $value['subject'] . '<br />';
            $content =  trim(
                    preg_replace('/(?:(?:\r\n|\r|\n)\s*){2}/s', "@@@@", 
                    str_replace(array('&nbsp;', '&copy;'), ' ', strip_tags($value['content']))
                    ) );
            
            $content = str_replace('@@@@:', ' : ', $content);
            $content = str_replace('@@@@{CUS_NAME}', ' {CUS_NAME}', $content);
            $content = str_replace('@@@@{MODEL}', ' {MODEL}', $content);
            $content = str_replace('@@@@{VIN}', ' {VIN}', $content);
            $content = str_replace('@@@@{DEALER}', ' {DEALER}', $content);
//            echo $content;
            $temp = explode( '@@@@', $content );
            unset($temp[0]);
            
//            foreach ( $temp as $val ) {
//                echo $val;
//            }
//            echo '<br />--------------------------------------------<br /><br /><br />';
            $this->objWorkSheet->getColumnDimension('A')->setWidth(65);
            $this->objWorkSheet->getStyle('A1:A220')->getAlignment()->setWrapText(true);
            $this->objWorkSheet->getColumnDimension('B')->setWidth(65);
            $this->objWorkSheet->getStyle('B1:B220')->getAlignment()->setWrapText(true);
            $this->objWorkSheet->setCellValueByColumnAndRow($j, $i, 
                    trim($value['title']) );
            $this->objWorkSheet->getStyleByColumnAndRow($j, $i++)->getFont()->setBold(true);
            $this->objWorkSheet->setCellValueByColumnAndRow($j, $i++, 
                    trim($value['subject']) );
            foreach ( $temp as $val ) {
                $this->objWorkSheet->setCellValueByColumnAndRow($j, $i++, 
                        trim($val) );
            }
        }
//        echo '<pre>';print_r($result);
//        die;
    }
    
    public function writeremailtemplatesAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        $modelObj = new Event_Model_EmailTemplates();
        
        $langs = array('1', '2', '3', '4', '6', '7', '8', '10', '11', '12', '13',
            '14', '15', '16', '17', '22', '23', '24', '25', '26');
        $langs = array('4');
        
        $dir = 'languages/';
        foreach ( $langs as $value ) {
            $result = $modelObj->getLanguageEmailTemplates($value);
            
            foreach ( $result as $key => $value ) {
                if ( !file_exists($dir . $value['lang_code']) ) {
                    mkdir($dir . $value['lang_code'], '0777');
                }
                $fp = fopen($dir . $value['lang_code'] . '/' 
                        . $value['email_token'] . '.html', 'w');
                fwrite($fp, trim( $value['content'] ));

                
                if ( !file_exists($dir . $value['lang_code'] . '/subjects') ) {
                    mkdir($dir . $value['lang_code'] . '/subjects', '0777');
                }
                $fp = fopen($dir . $value['lang_code'] . '/subjects/' 
                        . $value['email_token'] . '.html', 'w');
                fwrite($fp, trim( $value['subject'] ));
            }            
        }        
    }
}




