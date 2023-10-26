<?php

class Damco_Action_Helper_CreateExcelfile extends Zend_Controller_Action_Helper_Abstract{

    
    function CreateExcelfile($file_location,$arrheader,$headerOne='',$worksheet_name,$strtrowno=1,$filterOptions='') {
        
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time','600');
		
	$this->_objConfig = Zend_Registry::get('config');
        $this->_config = new Survey_Model_Config();
        $objPHPExcel = new Damco_Excelwriter_CreateExcel();
        // Set active sheet index
         
        $objPHPExcel->setActiveSheetIndex(0);
        $objWorkSheet = $objPHPExcel->getActiveSheet();
        // Rename sheet
        $objWorkSheet->setTitle($worksheet_name);

        //$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(55);
        // $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setWrapText(true);
        $objPHPExcel->getDefaultStyle()->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheetno = 0;
        $headercnt = 1;
        // print_r($filterOptions);die;
        // BOC show filter options
       
        if(!empty($filterOptions)) {
            foreach($filterOptions as $k=>$v) {
                $objPHPExcel->getActiveSheet()->getStyle(''.$headercnt)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$headercnt, $k);
                    //$headercnt++; 
                $objPHPExcel->getActiveSheet()->getStyle(''.$headercnt)->getFont()->setBold(false);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$headercnt , $v);
                $headercnt++; 
            }
        }
       
        if(isset($headerOne) && is_array($headerOne) && count($headerOne)>0) {
            $i = 1;   //     
            foreach ($headerOne as $a)  {
                $j = 0; 
                $k = 0;
                if($i==$strtrowno) {
                    foreach($headerOne[0] as $p)  {
                        if($k == 0) {
                            $objPHPExcel->getActiveSheet()->getStyle(''.$i)->getFont()->setBold(true);
                        } 
                        else {
                            $objPHPExcel->getActiveSheet()->getStyle(''.$i)->getFont()->setBold(false);
                        }
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $i, $p);
                        $j++;
                        $k++;
                    } 
                }
                else  {
                    foreach ($a as $p) {
                        /*if(stristr($p,"|bold"))
                        {   
                            $p= stristr($p,"|bold",true);
                            $objPHPExcel->getActiveSheet()->getStyle(''.$i)->getFont()->setBold(true);
                        } */
                        if($k == 0) {
                            $objPHPExcel->getActiveSheet()->getStyle(''.$i)->getFont()->setBold(true);
                        } 
                        else {
                            $objPHPExcel->getActiveSheet()->getStyle(''.$i)->getFont()->setBold(false);
                        }
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $i, $p);
                        $j++;
                        $k++;
                    }
                }
                $k++;
                $i++;
            }
            $strtrowno = 4;
        }
       
        //EOC show filter options
        $i = $strtrowno;   //     
        foreach ($arrheader as $a) {
            $j = 0; 
            ///////
            if($i==$strtrowno) {
                foreach($arrheader[0] as $p) {
                    if($j == 0) {
                        $objPHPExcel->getActiveSheet()->getStyle(''.$i)->getFont()->setBold(true);
                    } 
                    
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $i, $p);
                    $j++;
                } 
            }
            else  {
                if(is_array($a) && count($a)>0) {    
                    foreach ($a as $p)  {
                        if(stristr($p,"|bold")) {   
                            $p= stristr($p,"|bold",true);
                            $objPHPExcel->getActiveSheet()->getStyle(''.$i)->getFont()->setBold(true);
                        }

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $i, rtrim($p, ","));
                        $j++;
                    } 
                }
                else {
                    $i++;
                }
            } 

            $i++;
            $arrConfigVar = array('excel_max_rowno');
            $config_rowslimit = $this->_config->getConfigQueIds($arrConfigVar);
            if(($i%$config_rowslimit['excel_max_rowno']) == 0) { 
                $sheetno++;
             //  echo "<pre>"; print_r($arrheader[0]);die;
                $objPHPExcel->createSheet($sheetno);
                $objPHPExcel->getSheet($sheetno)->setTitle($worksheet_name.$sheetno);                
                $objPHPExcel->setActiveSheetIndex($sheetno);
                $j=0;
                foreach($arrheader[0] as $p) { 
                    $ni=1;
                    if($j == 0) {
                        $objPHPExcel->getActiveSheet()->getStyle(1)->getFont()->setBold(true);
                    } 
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $ni, $p);
                    $j++;
                }
                $i=$strtrowno+1;//
            }
        } 
        
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $file_location . '"');
        header('Cache-Control: max-age=0');
        ob_end_clean();
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007'); 
        $objWriter->save('php://output');  
        exit;
    }

    function CreateEcxelForAllQuestionEveryAnswer( $file_location,$arrheader,$worksheet_name,
            $comparisonDealers,$brandArr,$otherTypeQuestions,$AllQuestionsArr,
            $resultOverall,$resultComparitive,$strtrowno=1,$filterOptions='',
            $exportHeads = array(), $exportData = array(), $overall = 'Overall',
            $comparison = 'Comparison', $specialCases = array()) {
        
        $this->translate = Zend_Registry::get('Zend_Translate');
        
//        print_r($brandArr);
//        print_r($AllQuestionsArr);
////        print_r($exportData);
////        print_r($exportData);
//        echo $this->translate->_('Count').'asdf';die;
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time','600');
        $this->_objConfig = Zend_Registry::get('config');
        $this->_config = new Survey_Model_Config();
        $objPHPExcel = new Damco_Excelwriter_CreateExcel();
        // Set active sheet index
         
        $objPHPExcel->setActiveSheetIndex(0);
        $objWorkSheet = $objPHPExcel->getActiveSheet();
        // Rename sheet
        $objWorkSheet->setTitle($worksheet_name);
    
        //$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(55);
        // $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setWrapText(true);
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheetno = 0;
        $headercnt = 1;
 
        $r = $strtrowno;
        foreach ($arrheader as $a) {
            $j = 0;
            foreach($a as $p) {
                if($j == 0)
                {
                    $objPHPExcel->getActiveSheet()->getStyle(''.$r)->getFont()->setBold(true);
    
                }
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $r, $p);
                $j++;
            } 
            $r++;    
        }
        $r++;
        if (count($resultOverall) > 0) {
            foreach($AllQuestionsArr as $questions) {
                $j=0;
                if($questions['question_type']=='T') { 
                    $objPHPExcel->getActiveSheet()->getStyle($r)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $r, $questions['question']);
                    $r++;
                }
                
                if($questions['question_type']=='Q' || $questions['question_type']=='V') {
                    
                    if($questions['question_type']=='V') {
                        $objPHPExcel->getActiveSheet()->getStyle($r)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $r++, 
                                $questions['question_number'].' '. $questions['question']);
                    }
                    
                    if($questions['question_type']=='Q') {
                        $objPHPExcel->getActiveSheet()->getStyle($r)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $r++, 
                                $questions['question_number'].' '. $questions['question']);
                        if(in_array($questions['questionid'],$otherTypeQuestions)) {
                            $r += 2;
                            $overAllText = $overall;
                            $specialCase = in_array($questions['questionid'], $specialCases);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r,  null);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r,
                                $overAllText);
                            if (isset($resultOverall[$questions['questionid']]['totalCount'])) {
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $r-1,
                                    $this->translate->_('Count'));
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r,
                                    number_format($resultOverall[$questions['questionid']]['totalCount']));
                            }
                            
                            if($comparisonDealers !='') {
                                $comparisionText = $comparison;
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r, 
                                        $comparisionText );
                                if (isset($resultComparitive[$questions['questionid']]['totalCount'])) {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $r-1, 
                                            $this->translate->_('Count') );
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r, 
                                            number_format($resultComparitive[$questions['questionid']]['totalCount']) );
                                }
                            }
                            
                            if ( !empty( $exportHeads ) && !empty( $exportData ) ) {
                                foreach ( $exportHeads as $key => $value ) {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                            $j++, $r, $value);
                                    if ( isset( $exportData[$key][$questions['questionid']]['totalCount'] )) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                            $j, $r-1, $this->translate->_('Count'));
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                            $j++, $r, number_format($exportData[$key][$questions['questionid']]['totalCount']));
                                    }
                                }
                            }
/*
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r,null);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r, $overAllText);
                            
                            if($comparisonDealers !='') {
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r,
                                        $comparisionText);
                            }
                            
                            if ( !empty( $exportHeads ) && !empty( $exportData ) ) {
                                foreach ( $exportHeads as $key => $value ) {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                            $j++, $r, $value);
                                    if ( isset( $exportData[$key][$questions['questionid']]['totalCount'] )) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                            $j++, $r, $exportData[$key][$questions['questionid']]['totalCount']);
                                    }
                                }
                            } */

                            $j=0;
                            $r++;
                            $totalResponses = 24;
                            if (!empty($questions['response11']) &&
                                (strpos($questions['response11'], '|') !== false)) {
                                $totalResponses = 11;
                                $remainingResponses =  explode('|', $questions['response11']);
                                unset($questions['response11']);
                                foreach ($remainingResponses as $response) {
                                    $questions['response'. $totalResponses] = $response;
                                    $totalResponses++;
                                }
                            }
                            for ($i = 1; $i < $totalResponses; $i++) {
                                if ($specialCase && empty($questions['response' . $i])) {
                                    break;
                                }
                                
                                if ( empty($questions['response' . $i])
                                     && empty($brandArr[$i-1]) ) {
                                    continue;
                                }
                                
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r, 
                                        ($specialCase) ? $questions['response' . $i] : $brandArr[$i-1]);
                                $setResponseValue = '-';
                                if (isset($resultOverall[$questions['questionid']]['response' . $i])) {
                                    $setResponseValue = $resultOverall[$questions['questionid']]['response' . $i].'%';
                                }
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r, 
                                     $setResponseValue );
                                if (isset($resultOverall[$questions['questionid']]['response' . $i . '_cnt'])) {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r, 
                                         number_format($resultOverall[$questions['questionid']]['response' . $i . '_cnt']));
                                }
                                
                                if ($comparisonDealers != '') {
                                    $setResponseValue = '-';
                                    if (isset($resultComparitive[$questions['questionid']]['response' . $i])) {
                                        $setResponseValue = $resultComparitive[$questions['questionid']]['response' . $i].'%';
                                    }
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++,
                                        $r, $setResponseValue);

                                    if (isset($resultComparitive[$questions['questionid']]['response' . $i . '_cnt'])) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++,
                                            $r, number_format($resultComparitive[$questions['questionid']]['response' . $i . '_cnt']));
                                    }
                                }

                                if ( !empty( $exportHeads ) && !empty( $exportData ) ) {
                                    foreach ( $exportHeads as $key => $value ) {
                                        if ( isset( $exportData[$key][$questions['questionid']]['response'.$i] ) ) {
                                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                                $j++, $r, $exportData[$key][$questions['questionid']]['response'.$i].'%');
                                            if ( isset( $exportData[$key][$questions['questionid']]['response' . $i . '_cnt'] )) {
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                                    $j++, $r, number_format($exportData[$key][$questions['questionid']]['response' . $i . '_cnt']));
                                            }
                                        }
                                        else {
                                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                                $j++, $r, '');
                                        }
                                    }
                                }
                                
                                /*if($i < $totalResponses){
                                    if($questions['questionid']==35 && $brandArr[$i-1]=='Triumph'){
                                        $i++;
                                    }
                                }
                                $i++;
                                
                                if($i < $totalResponses) {
                                    $isResponseSet = true;
                                    if ($specialCase && empty($questions['response' . $i])) {
                                        $isResponseSet = false;
                                    }

                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, 
                                            $r,  ($specialCase) ? $questions['response' . $i] : $brandArr[$i-1]);
                                if($isResponseSet) {
                                    $setResponseValue = '-';
                                    if (isset($resultOverall[$questions['questionid']]['response' . $i])) {
                                        $setResponseValue = $resultOverall[$questions['questionid']]['response' . $i];
                                    }
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r,
                                        $setResponseValue);
                                    
                                    if (isset($resultOverall[$questions['questionid']]['response' . $i . '_cnt'])) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r,
                                            number_format($resultOverall[$questions['questionid']]['response' . $i . '_cnt']));
                                    }
                                    
                                    if ($comparisonDealers != '') {
                                        $setResponseValue = '-';
                                        if (isset($resultComparitive[$questions['questionid']]['response' . $i])) {
                                            $setResponseValue = $resultComparitive[$questions['questionid']]['response' . $i];
                                        }
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                            $j++, $r, $setResponseValue);
                                        
                                        if (isset($resultComparitive[$questions['questionid']]['response' . $i])) {
                                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                                $j++, $r, number_format($resultComparitive[$questions['questionid']]['response' . $i . '_cnt']));
                                        }
                                    }
                                } else {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r, '');
                                    if ($comparisonDealers != '') {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                            $j++, $r, ''
                                        );
                                    }
                                }
                                    
                                    if ( !empty( $exportHeads ) && !empty( $exportData ) ) {
                                        foreach ( $exportHeads as $key => $value ) {
                                            if ( isset( $exportData[$key][$questions['questionid']]['response'.$i] ) ) {
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                                    $j++, $r, $exportData[$key][$questions['questionid']]['response'.$i]);
                                            }
                                            else {
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                                    $j++, $r, '');
                                            }
                                        }
                                    }
                                } else {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r,'');
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r, '');
                                    if($comparisonDealers !='') {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r,'');
                                    } 
                                    
                                    if ( !empty( $exportHeads ) && !empty( $exportData ) ) {
                                        foreach ( $exportHeads as $key => $value ) {
                                            if ( isset( $exportData[$key][$questions['questionid']]['response'.$i] ) ) {
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                                    $j++, $r, $exportData[$key][$questions['questionid']]['response'.$i]);
                                            }
                                            else {
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                                    $j++, $r, '');
                                            }
                                        }
                                    }
                                } */
                                
                                /*if ( !empty( $exportHeads ) && !empty( $exportData ) ) {
                                    foreach ( $exportHeads as $key => $value ) {
                                        for($h=1;$h<=11;$h++) {
                                            if ( isset( $exportData[$key][$questions['questionid']]['response'.$h] )
                                                 && $exportData[$key][$questions['questionid']]['response'.$h]!='') {
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                                    $j++, $r, $exportData[$key][$questions['questionid']]['response'.$h]);
                                            }
                                        }
                                    }
                                }*/
                                
                                $j=0;
                                $r++;
                            }

                        }  else {
                            $j=0;

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r, 
                                    $this->translate->_('Score'));
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r, 
                                    $this->translate->_('Count'));
                            for($i=1;$i<=11;$i++) {
                                if($questions['response'.$i]!='') {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                            $j++, $r, $questions['response'.$i]);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                            $j++, $r, $this->translate->_('Count'));
                                } 
                            }
                            
                            $r++;
                            $j=0;
                            $overAllText = $overall;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r, 
                                    $overAllText);
                            if (isset($resultOverall[$questions['questionid']]['totalCount'])) {
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r, 
                                        number_format($resultOverall[$questions['questionid']]['totalCount']));
                            }
                            
                            for ($i=1; $i <= 11; $i++) {
                                if ($questions['response'.$i] != '') {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, 
                                        $r, $resultOverall[$questions['questionid']]['response' . $i].'%'
                                    );
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, 
                                        $r, number_format($resultOverall[$questions['questionid']]['response' . $i . '_cnt'])
                                    );
                                }
                            }
                            
                            if($comparisonDealers !='') {
                                $r++;
                                $j=0;
                                $comparisionText = $comparison;
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r, 
                                        $comparisionText );
                                if (isset($resultComparitive[$questions['questionid']]['totalCount'])) {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $r, 
                                            number_format($resultComparitive[$questions['questionid']]['totalCount']) );
                                }
                                for($i=1;$i<=11;$i++) {
                                    if($questions['response'.$i]!='') {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                            $j++, $r, $resultComparitive[$questions['questionid']]['response'.$i].'%'
                                        );
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                            $j++, $r, number_format($resultComparitive[$questions['questionid']]['response' . $i . '_cnt'])
                                        );
                                    }
                                }
                            }
                            
                            if ( !empty( $exportHeads ) && !empty( $exportData ) ) {
                                foreach ( $exportHeads as $key => $value ) {
                                    $r++;
                                    $j=0;
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                            $j++, $r, $value);
                                    if ( isset( $exportData[$key][$questions['questionid']]['totalCount'] )) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                            $j++, $r, $exportData[$key][$questions['questionid']]['totalCount']);
                                    }
                                    for($i=1;$i<=11;$i++) {
                                        if ( isset( $exportData[$key][$questions['questionid']]['response'.$i] )
                                             && $questions['response'.$i]!='') {
                                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                                $j++, $r, $exportData[$key][$questions['questionid']]['response'.$i].'%');
                                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                                                $j++, $r, $exportData[$key][$questions['questionid']]['response'.$i.'_cnt']);
                                        }
                                    }
                                }
                            }
                            
                            $r++;
                        }
                    }
                    $r++;
    		}
            }
        }

        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $file_location . '"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
   }
    
    function direct($file_location,$arrheader,$header='',$worksheet_name,$strtrowno=1,$filterOptions='') {
        
        return $this->CreateExcelfile($file_location,$arrheader,$header,$worksheet_name,$strtrowno,$filterOptions);
    }

}


