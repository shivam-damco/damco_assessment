<?php
class Report_QuestionanalysisController extends Damco_Core_CoreController {

    protected $_auth = null;
    protected $_redirector = null;
    

    public function init() {
        /* Initialize action controller here */
        parent::init();  
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->_helper->layout->setLayout('layout');
        $this->_auth = Zend_Auth::getInstance();
        $this->highchart_mod = new Report_Model_Highchart();
        $this->_questionModelObject = new Survey_Model_Questions();
        $this->_surveyModelObject = new Survey_Model_Survey();
        $this->_surveyEvenrtModelObject = new Survey_Model_SurveyEvents();
        $this->_answersModelObject = new Survey_Model_Answers();
        $this->_objCconfig = new Survey_Model_Config();
        $this->_eventTypeObj = new Survey_Model_EventTypes();
        $this->langid = $this->_request->getParam("langid");
        if (empty($this->langid)) {
            $this->langid = $this->_user->lang_id;
        }
        
    }

    public function indexAction() {
        if ($this->getRequest()->isXmlHttpRequest())  {
            $this->_getByAjax();
        }
        
        $rptObj = new Damco_Core_ReportingFunctions();
        $request = $this->getRequest();
        $formData = $request->getParams();
        $get = $this->getRequest()->getParams();
        $highchartArray = array();
        $dateRangeField = '';
        $drilldownQueryString = $qus_id_string = "";
        $highchart_mod = new Report_Model_Highchart();
        $allquestiontext = $dateArray = array();
        $start_date = '';
        $end_date = '';
        $period = '';
        
        $survey_category = (isset($get['survey_category']) && !empty($get['survey_category'])) ? $get['survey_category'] : '';
        $event_type = (isset($get['event_type']) && !empty($get['event_type'])) ? $get['event_type'] : '';
        $questionid = (isset($get['questionid']) && !empty($get['questionid'])) ? $get['questionid'] : '';     
        $survey_id = (isset($get['survey_id']) && !empty($get['survey_id'])) ? $get['survey_id'] : '';     
        
        $dateInfo = $this->getdate($get);
        $start_date = $dateInfo["start_date"];
        $end_date = $dateInfo["end_date"];
        $this->view->arrmonthName = $arrmonths = $this->createMonthArray($get);      
        $questioninfo = $this->getQuestioninfo($event_type);
        
        $splQids = $arrSplQids = $questioninfo["arrSplQids"];
        
        $this->view->arrSplQids = $splQids;
        $npsqid = $questioninfo["npsqid"];
        $allQuestions =  $questioninfo["allQuestions"];
        $where = $questioninfo["where"];
        
        $where["input_type"] = array('checkbox','drop down','radio');
        if($questionid > 0 || $questionid == 'All') {
            $questionIDArray = array();
            if($questionid == 'All') {
                $allQuestionID = $this->_questionModelObject->getAllQuestionIDByEventTypeNoTextArea($event_type);
                foreach($allQuestionID as $questionId) {
                    $questionIDArray[] = $questionId['questionid'];
                }
                
                $questionid = $questionIDArray;
                $where["seq.questionid"]= implode(',',$questionid);
                $questionWhere = implode(',',$questionid);
            }
            else {
                $where["seq.questionid"]= $questionid;
                $questionWhere = '';
            }
            $old_cond = isset($where["seq.questionid"])? $where["seq.questionid"] : "" ;
            $where["seq.questionid"]= $questionid;
            if($survey_id != '') {
                if(count($questionid) == 1 && $questionWhere != '') {
                    $resultOverall = $this->_questionModelObject->getAllquestionsforQA(			
                                                $event_type,//$dealers,
                                                $start_date,$end_date,
                                                $questionWhere,
                                                //$get["model"],
                                                $this->langid,$arrSplQids,$survey_id);
                }
                else {
                    $resultOverall = $this->_questionModelObject->getAllquestionsforQA(			
                                                $event_type,//$dealers,
                                                $start_date,$end_date,
                                                $questionid,
                                                //$get["model"],
                                                $this->langid,$arrSplQids,$survey_id);
                }  
            }
            else {
                $resultOverall = array();
            }
            $allquestiontext = $this->_questionModelObject->getQuestionDetailsForReports($where);
            
            if(empty($resultOverall)) {
                $this->view->msg = $this->view->translate("No data found");
            }
            else {
                //$info = $this->createResponseforQuestion($allquestiontext,$resultOverall);
                $data = array();
                for( $i = 0; $i < count($allquestiontext[0]); $i++ ) {
                    if( $resultOverall[$i]['questionid'] == $allquestiontext[0][$i]['questionid']) {
                        $question_text = $allquestiontext[0][$i]['question'];
                        $response_options = array_splice($allquestiontext[0][$i] , -21);
                        $chart = $this->getBarHighCharts($resultOverall[$i], $start_date, $end_date, 
                                $period, $response_options, $get, $question_text,"container_".$resultOverall[$i]['questionid']);
                        //echo "<pre>chart";print_r($chart);exit;
                        $data[$resultOverall[$i]['questionid']]['question'] = $question_text;
                        $data[$resultOverall[$i]['questionid']]['chart'] = $chart;
                    }
                }
            }
            
            if(!empty($data)) {
                $this->view->questionResponse = $data;
                unset($where["seq.questionid"]);
                $drilldownQueryString = '&surveyid='.$get['survey_id'].'&questionid='.$get['questionid'];
            }
            else {
                $this->view->msg = $this->view->translate("No data found");
            }
        }
         
        $get['startDate'] = $start_date;
        $get['endDate'] = $end_date;
        
        $this->view->questiontext = isset($allquestiontext[0]) ? $allquestiontext[0][0]["question"] : "";
        $this->view->npsqid = $npsqid;
        $this->view->drilldownQueryString = $drilldownQueryString;//.$selp;
        $this->view->get = $get;
        $this->view->flashMessages = $this->_flashMessenger->getMessages();
    }
    
    private function _getByAjax($returnArray = FALSE) {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $get = $this->getRequest()->getParams();
        $aColumns = array('0'=>'EmployeeId','1'=> 'EmployeeName','3'=>'Qusetion','4'=>'Answer');
        
    	if(!isset($get['ordercolumn'])) {
            if(isset($get['order'][0]['column'])) {
                if(is_numeric($get['order'][0]['column'])) {
                    $field = $aColumns[$get['order'][0]['column']];
                    $sortby = ($get['order'][0]['dir']==='asc')? 'ASC' : 'DESC';
                    $sOrder = $field. ' ' .$sortby;
                } 
                else {
                    $sOrder = '';
                }
            }
            else {
                $sOrder = '';
            }   
        } 
        else {
            $field = $aColumns[$get['ordercolumn']];
            $sortby = ($get['orderdir']==='asc')? 'ASC' : 'DESC';
            $sOrder = $field. ' ' .$sortby;
        }
        
        
        if(isset($get['surveyID']) && $get['surveyID'] != '' ) {
            $params = array(
                'survey_id'=>$get['surveyID'],
                'start_date'=> isset($get['start_date'])?$get['start_date']:'',
                'end_date' => isset($get['end_date'])?$get['end_date']:'',
                'start'=>isset($get['start'])?$get['start']:'',
                'length'=>isset($get['length'])?$get['length']:'',
                'orderBy'=>$sOrder //Added By Amit kumar 16/09/14 3:46 PM for Sorting
            );
        
            $result = $this->_answersModelObject->getEventTypesData($params); 
            $rowCount = $this->_answersModelObject->getCountData($params); 
        }
        else {
            $result = array();
        }
        
        if(count($result)>0) {
            $data = array(
                'draw' => isset($get['draw'])?$get['draw']:'',
                'recordsTotal' => $rowCount[0]['COUNT'], //$result[1][0]['tot'],
                'recordsFiltered' => $rowCount[0]['COUNT'] //$result[1][0]['tot'],
            );
            $data['data'] = array();
            
            foreach ($result as $value) {
                $temp = array(
                        $value['EmployeeId'],
                        $value['EmployeeName'],
                        $value['Employeeemail'],
                        $value['Qusetion'],
                        rtrim($value['Answer'], ",")
                );
                $data['data'][]=$temp;
            } 
        }
        else {
            $data = array(
                'draw' => isset($get['draw'])?$get['draw']:'',
                'recordsTotal' => '0', //$result[1][0]['tot'],
                'recordsFiltered' => '0' //$result[1][0]['tot'],
            );
            $data['data'] = array();
        }
        
        echo json_encode( $data );
        exit;
    }

    

    //This is for NPS chart
    function getLineHighCharts($params = array(), $start_date, $end_date, $dateFileterType = '', $arrmonths,$get,$questiontext='') {
        
        $chart = new Damco_Highchart();
        $chart->chart->renderTo = "container1";
        $chart->chart->type = "line";
        //$chart->title->text = $this->view->translate('Score');
        $chart->legend->enabled = false;
        $chart->credits->enabled = false;
        $chart->title->text = $questiontext;
        $chart->title->margin = 15;
        $chart->title->align = "left";
      //  $chart->title->style->opacity = '0.2';
        $chart->title->style->fontSize = '18px';
       // $chart->title->style->fontWeight = 'bold';     
        $chart->title->style->color = '#0B0719'; 
        $chart->legend->enabled = true;
        
        //$chart->plotOptions->bar->dataLabels->color = 'black';
       // $chart->credits->enabled = false;
        $chart->yAxis->min = 0; 
        $chart->yAxis->max = 100; //7/31/14 2:39 PM
        $chart->yAxis->minTickInterval = 10; //7/31/14 2:41 PM
        $chart->yAxis->title->text = " ";
        $chart->chart->height = '400';
        $chart->chart->marginTop = 50;
        // $chart->chart->marginLeft=80;
        $chart->chart->plotBorderWidth = '1';
        $chartArr = $category = array();

        $i = 0;
        $minYaxisStrtVal = 100;
        //print_R($params);//die;
        foreach ($params[0] as $val) {
            //$total = $val['red'] + $val['green'] + $val['amber'];
            $mnnm = $this->view->translate($val['chart_description']) . "-" . substr($val["yearnum"], 2, 2);
            $category[$i] = $mnnm;
            ////
            $mmnm = strlen($val["monthnum"]) < 2 ? "0" . $val["monthnum"] : $val["monthnum"];
            $chartArr[$val["yearnum"] . $mmnm][$mnnm] = array("red" => $val['red'], "green" => $val['green'], "amber" => $val['amber']);
            $i++;
        }
       // print_R($chartArr);
        $retdata = $this->createResponseArray($dateFileterType,$start_date,$end_date,$chartArr,$category,$arrmonths);
        //print_R($retdata);die;
        // print_R($xAsisArry);  
        $xAsisArry = ($retdata["xAsisArry"]);
        $red = array_reverse($retdata["red"]);
        $green = array_reverse($retdata["green"]);
        $amber = array_reverse($retdata["amber"]);
         //  print_R($xAsisArry);     
        $chart->xAxis->categories = array_reverse($xAsisArry); 
        $chart->series[] = array('name' => $this->view->translate('Promoters'), 'data' => $green,'color'=>'#088A08');
        $chart->series[] = array('name' => $this->view->translate('Passive'), 'data' => $amber,'color'=>'#D79428');
        $chart->series[] = array('name' => $this->view->translate('Detractors'), 'data' => $red,'color'=>'#FE2E2E');
       $chart->tooltip->formatter = new Damco_HighchartJsExpr(
                "function() {
        return  this.series.name+':'+ this.x + '<br>' +  this.y+' %' ;}");
       if(!empty($get["chkme"]))
        {
            $chart->plotOptions->series->enableMouseTracking = false;
            $chart->plotOptions->series->animation = false;
        }
        $chart->tooltip->enabled = true; //disable tooltip & it's formatter as per naren sir 8/7/14 2:03 PM 
        //to remove export option
        $chart->exporting->buttons->contextButton->enabled = false;
        $this->view->highcharts = $chart;
    }

    // Generate bar chart script for Question Analysis report TT-1580
    function getBarHighCharts($params = array(), $start_date, $end_date, $period,
                                $questionres, $get, $questiontext='', $renderTo) {
        $chart = new Damco_Highchart();
        $chart->chart->renderTo = $renderTo;
        $chart->chart->series->type = "bar";
        $chart->title->text = $questiontext = '';
        $chart->chart->height = '320';
        $chart->title->align = "left";
        $chart->title->margin = 5;
        $chart->title->style->fontSize = '12px';
        $chart->title->style->color = '#EC1C24';
        $chart->legend->enabled = false;
        $chart->credits->enabled = false;
        $chart->yAxis->max= 100;
        $chart->yAxis->title->text = '';
        $chart->plotOptions->bar->size = '25';

        $chart->plotOptions->bar->dataLabels->enabled = 1;
        $chart->plotOptions->bar->dataLabels->formatter =  new Damco_HighchartJsExpr(
         "function() {return  this.y+'%';}");
        $chart->tooltip->enabled = true;
        $chart->tooltip->valueSuffix = '%';
        $data = $categories = array();
        
        $chart->plotOptions->series->cursor = 'pointer';
        $drilldownQueryString = $this->view->serverUrl(). "/event/index/?event_status=Closed"
                . "&date_range_field=survey_submission_date&period=by_month&esr_type=qans"
                . "&month=&year=&qresp=&period=by_month&surveyid=" . $get['survey_id'] 
                . "&questionid=" . $params['questionid'] . "&startDate=" . $start_date 
                . "&endDate=".$end_date;
        //echo "<pre>";print_r($params);exit;
        for ($qcnt=1; $qcnt <= count($questionres); $qcnt++) {
            if (isset($params['response' . $qcnt]) && strlen($questionres['response' . $qcnt]) > 0) {
                $categories[] = $questionres['response' . $qcnt]." (".$params['response' . $qcnt . "_cnt"].")";
                $data[] = array("name" => $questionres['response' . $qcnt]." (".$params['response' . $qcnt . "_cnt"].")",
                    "y" => (float) ($params['response' . $qcnt]), 
                    "total" => $params['response' . $qcnt . "_cnt"],
                    "response" => $qcnt);
            }
        }
        $chart->xAxis->categories = $categories;
        $chart->series[] = array( 'type'=> 'bar', 'name' => "Score", 'color' => '#0f7dc1',
            'data' => $data,
            'format'=>'%',
            'url'=>$drilldownQueryString,
            'events' => array(
                'click' => new Damco_HighchartJsExpr('function(e){ 
                    location.href = this.options.url+"&response="+e.point.response; 
                }')),
            'pointWidth'=>25,
            );
        $chart->exporting->sourceWidth = 1200;
        //to remove print option
        $chart->exporting->buttons->contextButton->menuItems = new Damco_HighchartJsExpr("
                 Highcharts.getOptions().exporting.buttons.contextButton.menuItems.splice(2)");
        return $chart;
    }

    function array_insert(&$array, $element, $position = null) {
        if (count($array) == 0) {
            $array[] = $element;
        } elseif (is_numeric($position) && $position < 0) {
            if ((count($array) + position) < 0) {
                $array = array_insert($array, $element, 0);
            } else {
                $array[count($array) + $position] = $element;
            }
        } elseif (is_numeric($position) && isset($array[$position])) {
            $part1 = array_slice($array, 0, $position, true);
            $part2 = array_slice($array, $position, null, true);
            $array = array_merge($part1, array($position => $element), $part2);
            foreach ($array as $key => $item) {
                if (is_null($item)) {
                    unset($array[$key]);
                }
            }
        } elseif (is_null($position)) {
            $array[] = $element;
        } elseif (!isset($array[$position])) {
            $array[$position] = $element;
        }
        $array = array_merge($array);
        return $array;
    }
    
    public function getdate($get) { 
        if (isset($get['period']) && $get['period'] == 'rolling_12_months') {
            $period = $get['period'];
            //commented by sachin 1/5/16 10:08 AM 
//            $start_date = $this->_helper->GetReportdate(date('Y-m-1'), "currentyear");
//            $end_date = $this->_helper->GetReportdate(date('Y-m-1'), "enddate");
           $start_date = date('Y-m-1');
           $end_date = date('Y-m-d');
           
            //
        } else if (isset($get['period']) && $get['period'] == 'by_month') {
            $period = $get['period'];
            $month = $get['month'];
            $year = $get['year'];
            if ($month && $year) {
                $end_date = $year . '-' . $month . '-31';
                $start_date = $year . '-' . $month . '-01';
            }
        } else if (isset($get['period']) && $get['period'] == 'by_period') {
            $period = $get['period'];
            $start_date = isset($get['fromDate']) ? $get['fromDate'] : '';
            $end_date = isset($get['toDate']) ? $get['toDate'] : '';
        } else {
              //$start_date = date("Y-m-1");
            //$end_date = date("Y-m-d");
			$start_date = '';
            $end_date = '';
        }

        return array("start_date" => $start_date, "end_date" => $end_date);
    }
    
    /*
        Controller for export to excel
     *  */
    public function exporttoexcelAction()
    {
        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $request = $this->getRequest();
        $formData = $request->getParams();
        $get = $this->getRequest()->getParams(); 
        
        // Get Dealers
        $session = new Zend_Session_Namespace('access_heirarchy');
        $questionres = $dealers = array();
        
       /* if (isset($get['dealer']) && in_array($get['dealer'], $session->accessHierarchy['dealers'])) {
            $dealers[] = "'" . $get['dealer'] . "'";
        } else {
            $accHierarchy = new Damco_Core_AccessHierarchy();
            $result = $accHierarchy->get(
                    (isset($get['branch']) ? $get['branch'] : ''), (isset($get['market']) ? $get['market'] : ''), (isset($get['sales_region']) ? $get['sales_region'] : ''), (isset($get['dealer']) ? $get['dealer'] : '')
            );

            foreach ($result['dealers'] as $value) {
                $dealers[] = "'" . $value['id'] . "'";
            }

            //patch from Harpreet By Dipa 8/13/14 11:53 AM
            if ((!empty($get['branch']) || !empty($get['market']) || !empty($get['sales_region'])
                    ) && empty($dealers)) {
                $dealers = array('-1');
            }
        } */
        
        $survey_category = (isset($get['survey_category']) && !empty($get['survey_category'])) ? $get['survey_category'] : '1';
        
        $event_type = (isset($get['event_type']) && !empty($get['event_type'])) ? $get['event_type'] : '1';
        
        $questionid = (isset($get['questionid']) && !empty($get['questionid'])) ? $get['questionid'] : '';        
        
        $surveyID = (isset($get['survey_id']) && !empty($get['survey_id'])) ? $get['survey_id'] : '';        
        
        $dateInfo = $this->getdate($get);
        
        
        $start_date = $dateInfo["start_date"];
        $end_date = $dateInfo["end_date"];
        //echo $start_date."|".$end_date;die;
        //BOC to show monthname 9/30/14 2:09 PM
        $this->view->arrmonthName = $arrmonths = $this->createMonthArray($get);
        
        //EOC to show monthname 9/30/14 2:09 PM
         //get all questions
        $npsqid = "";        
        $questioninfo = $this->getQuestioninfo($event_type);
        
        $this->view->arrSplQids = $arrSplQids = $questioninfo["arrSplQids"];
        
        //print_r($arrSplQids);die;
        $npsqid = $questioninfo["npsqid"];
        
        $allQuestions =  $questioninfo["allQuestions"];
        
        $where = $questioninfo["where"];
        
        //$arrSplQids = unserialize($config_queid["Question_analysis_dropdown_questionid"]); ;
        $where["input_type"] = array('checkbox','drop down','radio');
        
        if(isset($get['period']) && $get['period'] == 'by_period')
            $getParamUrl = '&period=by_period&fromDate='.$start_date.'&toDate='.$end_date.'&month=&year=&sel_year=&startDate=&endDate=';
        else 
            $getParamUrl = '';
        
        
                
        if($questionid > 0 || $questionid == 'All')
        {
            $old_cond = isset($where["seq.questionid"])? $where["seq.questionid"] : "" ;
            if($questionid == 'All')
            {
                
                $allQuestionID = $this->_questionModelObject->getAllQuestionIDByEventTypeNoTextArea($event_type);
                foreach($allQuestionID as $questionId)
                {
                    $questionIDArray[] = $questionId['questionid'];
                }
                
                $questionid = $questionIDArray;
                $where["seq.questionid"]= implode(',',$questionid);
                $questionWhere = implode(',',$questionid);
            }
            else
            {
                $where["seq.questionid"]= $questionid;
                $questionWhere = '';
            }
            
            //print_R($arrSplQids);
            $where["seq.questionid"]= $questionid;
            
            //print_R($dealers);die;
            //echo "$event_type,$dealers, $start_date,$end_date,  $questionid";die;
            if($surveyID != '')
            {
                if(count($questionid) == 1 && $questionWhere != '')
                {
                    $resultOverall = $this->_questionModelObject->getAllquestionsforQA(			
                                                $event_type,
                                                $start_date,$end_date,
                                                $questionWhere,
                                                $this->langid,$arrSplQids,$surveyID);
                }
                else
                {
                    $resultOverall = $this->_questionModelObject->getAllquestionsforQA(			
                                                $event_type,
                                                $start_date,$end_date,
                                                $questionid,
                                                $this->langid,$arrSplQids,$surveyID);
                }
                
            }
            else 
            {
                $resultOverall = array();
            }
            
            
            //     print_R($resultOverall);die;
            $allquestiontext = $this->_questionModelObject->getQuestionDetailsForReports($where);
            
           /* if($allquestiontext[0]["response1"] == "Make")
            {
                $arrMakeQids = unserialize($config_queid["survey_make_name"]); 
                foreach($arrMakeQids as $k=>$mkName)
                {
                    $allquestiontext[0]["response".$k] = $mkName;
                }
            } */
            //print_R($resultOverall);die;
            
            if(empty($resultOverall))
            {
                $this->_flashMessenger->addMessage(array('message' => 'No data found'));
                if(is_array($questionid) && count($questionid)>0)
                    $this->_redirect('/report/questionanalysis/?event_type='.$event_type.'&survey_id='.$surveyID.'&questionid=All'.'&survey_category='.$survey_category.$getParamUrl);
                else
                    $this->_redirect('/report/questionanalysis/?event_type='.$event_type.'&survey_id='.$surveyID.'&questionid='.$questionid.'&survey_category='.$survey_category.$getParamUrl);
            }
            else
            {
                if(in_array($questionid,$arrSplQids))
                { 
                    $info = $this->createResponseforSplQuestion($resultOverall,"-","n",$resultOverall[2][0]["question"]);
                }
                else
                {
                    $info = $this->createResponseforQuestion($allquestiontext,$resultOverall,"-","n");             
                    
                }
                $getResponse = $info["getResponse"];
                $questionres = $info["questionres"];
                $resp_cnt=$info["resp_cnt"];
            }
           
            
            
            if(!empty($getResponse))
            {
                   /* foreach($arrmonths as $mnth_k=>$mnth_v)
                    {
                       for($i=1;$i<=$resp_cnt;$i++)
                       {
                           unset($getResponse["response".$i]["actual_response_val"]); //10/29/14 11:36 AM
                            if(isset($getResponse["response".$i]) && !array_key_exists($mnth_k, $getResponse["response".$i]))
                            {
                                
                                $month_no = substr($mnth_k,-2);
                                $year_no = substr($mnth_k,0,-4);
                                $getResponse["response".$i][$mnth_k] = "0.00-(0)";
                            }
                       }
                    } */
                    $response = array();
                    $b= 0;
                    
                    
                    
                    foreach($getResponse as $resp=>$aresp)
                    {
                        for($a=1;$a<11;$a++)
                        {
                            
                            if(isset($aresp['response_val'.$a]) && $aresp['response_val'.$a]!= '')
                            {
                                $response[$b][] = $aresp['question'];
                                $response[$b][] = $aresp['response_val'.$a];
                                $response[$b][] = $aresp['response'.$a];
                            }
                            $b++;
                        }
                        $response[$b][] = '';
                        $b++;       
                    }
                    
                    $userResp = $response;
                    
                    
                    
                    
                    
                    //for heading
                    
                    $header = array();
                    $header[0][] = $this->view->translate("Questions");
                    $header[0][] = $this->view->translate("Answers");
                    $header[0][] = $this->view->translate("Responses");
                    
                    $header = array_merge($header,$userResp);
                    
                    
                    
                    
                    /*foreach($arrmonths as $k=>$v)
                    {           
                       $header[0][] = $v;
                    }*/
                    
                    
                    
                    //print_r($userResp);die;
                    
                    /*foreach($userResp as $k=>$arrv)
                    {
                        if(strlen($arrv["response_val1"])>0)
                        {
                            $header[]=$arrv;
                        }
                    } */
                    
                    
                    $filterOptions = array();
                    //$this->dealerObj = new Dealer_Model_Dealers();
                    //$this->_eventModelCompanyObject = new Event_Model_CompanyStructure();
                    $this->_eventtypesModelObject = new Event_Model_EventTypes();
                    
                    
                    foreach($get as $k=>$v)
                    {
                        
                        if(!empty($v))
                        {
                            switch($k)
                            {
                               case "event_type" : 
                                   $eventtype= $this->_eventtypesModelObject->getWhere('event_type',array('event_typeid' => $v));
                                   $filterOptions["Survey:"] = $this->view->translate($eventtype[0]['event_type']); 
                                   break;
                               case "survey_id" : 
                                   $surveyData= $this->_surveyModelObject->getSurveyByID($v);
                                   $filterOptions["Survey Instance:"] = $this->view->translate($surveyData[0]['survey_name']); 
                                   break;
                               //case "questionid" :$filterOptions[$this->view->translate("Question:")] =  $allquestiontext[0]["question"]; break;
                               case "startDate" :$filterOptions[$this->view->translate("From:")] = $v ; break;
                               case "endDate" :$filterOptions[$this->view->translate("To:")] =  $v; break;
                               default: //do nothing 
                                   break;
                            }
                        }
                    }
                    $worksheet_name = $exp_name = 'Question-Analysis-Report';       
                    $file_location = $exp_name . date("Y-m-d") . ".xlsx";    
                    $array = array();
                    
                    $this->_helper->CreateExcelfile($file_location,$header,$array,$worksheet_name,'4',$filterOptions);
            }
            else
            {
                $this->view->msg = $this->view->translate("No data found");
            }
        }
        else 
        {
            $this->_redirect('/report/questionanalysis/?event_type='.$event_type.'&survey_id='.$surveyID.'&questionid='.$questionid.'&survey_category='.$survey_category.$getParamUrl);
        }
        exit;
    }
    
    public function getQuestioninfo($event_type)
    {
        $arrConfigVariables = array('Question_analysis_questionid','survey_make_name','service_NPS_question_id',
            'sales_NPS_question_id','Question_analysis_dropdown_questionid');
        $config_queid = $this->_objCconfig->getConfigQueIds($arrConfigVariables);        
        $serArray = unserialize($config_queid['Question_analysis_questionid']);
        $arrSplQids = array();
        $npsqid = '';
        
        /* switch($event_type)
        {
            case "1":
                $npsqid = $config_queid["sales_NPS_question_id"];
                $allQuestions = $serArray['sales']['questionId'];
                $where = array("event_typeid" => 1, 
                        "langid" => !empty($this->langid) ? $this->langid : 1, 
                        'question_type' =>array('Q'));
                break;
            case "2": 
                $npsqid = "";
                $allQuestions = $serArray['product']['questionId'];
                $where = array("event_typeid" => 2, 
                        "langid" => !empty($this->langid) ? $this->langid : 1, 
                        'question_type' =>array('Q'));
                break;
            case "3": 
                $npsqid = $config_queid["service_NPS_question_id"];
                $allQuestions = $serArray['service']['questionId'];
                $where = array("event_typeid" => 3, 
                        "langid" => !empty($this->langid) ? $this->langid : 1, 
                        'question_type' =>array('Q'));
                break; 
           
        } 12/30/15 5:39 PM */
        $allQuestions = $serArray['service']['questionId'];
                $where = array("event_typeid" => $event_type, 
                        "langid" => !empty($this->langid) ? $this->langid : 1, 
                        'question_type' =>array('Q'));
        //$arrSplQids = unserialize($config_queid["Question_analysis_dropdown_questionid"]); 
        return array("npsqid"=>$npsqid,"allQuestions"=>$allQuestions,"where"=>$where,"arrSplQids"=>$arrSplQids);
    }
    
     public function createMonthArray($get)
    {
        
        $dateInfo = $this->getdate($get);
        $start_date = $dateInfo["start_date"];
        $end_date = $dateInfo["end_date"];
        
        //BOC to show monthname 9/30/14 2:09 PM
        $arrStrtmonth = explode("-",$start_date); // this is dateformat 2013-08-31
        $arrEndmonth = explode("-",$end_date); 
        

         if (!empty($arrEndmonth[0]) && !empty($arrEndmonth[1])  && $arrEndmonth[0] == $arrStrtmonth[0] && $arrEndmonth[1] >= $arrStrtmonth[1]) {
			 if ($arrEndmonth[0] == $arrStrtmonth[0] && $arrEndmonth[1] >= $arrStrtmonth[1]) {
				for($i = $arrStrtmonth[1]; $i<=$arrEndmonth[1];$i++) {
					$year = str_replace("20","",$arrStrtmonth[0]);                                   
					$months_name = date('M', mktime(0, 0, 0, $i, 1));
					$mmnum = strlen($i)<2 ? "0".$i : $i;
					$arrmonths[$arrStrtmonth[0].$mmnum] = $this->view->translate($months_name)."-".$year;//."-".$arrStrtmonth[0];                     
				}
        }
			else {
				for ($i = $arrStrtmonth[1]; $i <= 12; $i++) {
					$year = str_replace("20","",$arrStrtmonth[0]);
					$months_name = date('M', mktime(0, 0, 0, $i, 1));
					$mmnum = strlen($i)<2 ? "0".$i : $i;
					$arrmonths[$arrStrtmonth[0].$mmnum] = $this->view->translate($months_name)."-".$year;
				}
				$lastloop_cnt = $i;
				for($i = 1; $i<=$arrEndmonth[1];$i++)
				{                   
					$year =  str_replace("20","",$arrEndmonth[0]); 
				   // $year = ($lastloop_cnt > 12) ? ($year +1) : $year;
					$months_name = date('M', mktime(0, 0, 0, $i, 1));
					$mmnum = strlen($i)<2 ? "0".$i : $i;
					if(!isset($arrmonths[$arrEndmonth[0].$mmnum]))
					{
						$arrmonths[$arrEndmonth[0].$mmnum] = $this->view->translate($months_name)."-".$year;//."-".$arrEndmonth[0];
					}
            }
        }
		 return $arrmonths;
		 }
       
       
    }
    
    public function createResponseforSplQuestion($resultOverall,$seperator = "<br>",$showlinkinfo="y",$noOtherBrandTranslation='')
    {
        $resp_cnt=0;
        foreach($resultOverall[1] as $userresp)
        { 
            $resp_cnt++;
            $questionres[$resp_cnt] =   $getResponse["response".$resp_cnt]["response_val"] = (($userresp["user_responses"] == "Nootherbrand") && !empty($noOtherBrandTranslation)) ? $noOtherBrandTranslation : $userresp["user_responses"] ;
            $getResponse["response".$resp_cnt]["actual_response_val"] = $userresp["user_responses"] ;
            //$questionres[$resp_cnt] =  $userresp["user_responses"];                   
        }
        foreach($resultOverall[0] as $arrData)
        {
           // print_R($arrData);//die;
           for($i=1;$i<=$resp_cnt;$i++)
           {
               $mnKey = $arrData["yearno"].(strlen($arrData["monthno"])<2 ? "0".$arrData["monthno"] : $arrData["monthno"]);
               if(isset($getResponse["response".$i]["response_val"]))
               {
                   $linkinfo = "";
                   if($showlinkinfo == "y")
                   {
                       $linkinfo = " [".$arrData["monthno"]."-".$arrData["yearno"] ."]";
                   }                  
                   $getResponse["response".$i][$mnKey] =  
                            $arrData[$getResponse["response".$i]["actual_response_val"]."_percent"].
                            $seperator."(".$arrData[$getResponse["response".$i]["actual_response_val"]] .")".$linkinfo;
                 // unset($getResponse["response".$i]["actual_response_val"]); 
                  
               }
               
           }
        }
       // print_r($getResponse);
        return array("getResponse"=>$getResponse,"questionres"=>$questionres,"resp_cnt"=>$resp_cnt);
    }
    
    public function createResponseforQuestion($allquestiontext,$resultOverall,$seperator = "<br>",$showlinkinfo="y")
    {
        
        $resp_cnt =0;
        $totalResponses = 11;
       // print_r($allquestiontext);die;
        
        foreach($allquestiontext[0] as $arrQtxt)
        {
            foreach($resultOverall as $k=>$arrData)
            {
               if($arrQtxt["questionid"] == $arrData["questionid"])
               {
                   $resultOverall[$k]["question_text"]=  $arrQtxt["question"];
                   for ($i=1; $i < $totalResponses; $i++) 
                   {
                        if(isset($arrQtxt["response".$i]) && !empty($arrQtxt["response".$i]))
                        {
                            $resultOverall[$k]["response_val".$i] =  $arrQtxt["response".$i];
                            //$questionres[$i] = $allquestiontext[0]["response".$i];
                        }
                    }
               }
            }
            
        }
        
      /*  for ($i=1; $i < $totalResponses; $i++) {
           if(isset($allquestiontext[0]["response".$i]))
           {
               $getResponse["response".$i]["response_val"] =  $allquestiontext[0]["response".$i];
               $questionres[$i] = $allquestiontext[0]["response".$i];
           }
        }
        $resp_cnt = $i;*/
        //echo "before";  print_R($resultOverall);die;
       // print_R($resultOverall);die;
        foreach($resultOverall as $arrData)
        {
            
            //print_R($allquestiontext[0]);
             for ($i=1,$arrCnt =0; $i < $totalResponses; $i++,$arrCnt++) {
                 $getqid = $arrData["questionid"];
               //echo $allquestiontext[0][$arrCnt]["response".$i]."<br>";
                //print_R($allquestiontext[0][$arrCnt]);die; 
               /* if(isset($allquestiontext[0][$arrCnt])  )
                {                    
                    if( isset($allquestiontext[0][$arrCnt]["response".$i]))
                    {
                        $getResponse[$getqid]["response".$i]["response_val"] =  $allquestiontext[0][$arrCnt]["response".$i];
                        $questionres[$getqid][$i] = ($allquestiontext[0][$arrCnt]["questionid"] == $getqid ) ? $allquestiontext[0][$arrCnt]["response".$i] : '';
                    }
                }*/
                /* elseif(isset($allquestiontext[0][$arrCnt])  && $getqid != $allquestiontext[0][$arrCnt]["questionid"])
                {
                    $getResponse[$getqid]=array();
                }*/
                
             }
             
             $resp_cnt = $i;
             
              $getResponse[$arrData["questionid"]]["question"]=$arrData["question_text"];
              $getResponse[$arrData["questionid"]]["questionID"]=$arrData["questionid"];
              $getResponse[$arrData["questionid"]]["surveyID"]=$arrData["survey_id"];
            //
           for($i=1;$i<$resp_cnt;$i++)
           {
               if(isset($arrData["response".$i]))
               {
                   $linkinfo = "";
                   if($showlinkinfo == "y")
                   {
                       $linkinfo = " [".$arrData["monthno"]."-".$arrData["yearno"] ."]";
                   }
                   $mnKey = $arrData["yearno"].(strlen($arrData["monthno"])<2 ? "0".$arrData["monthno"] : $arrData["monthno"]);
                   $getResponse[$arrData["questionid"]]["response".$i] = $arrData["response".$i].$seperator."(".$arrData["response".$i."_cnt"] .")";
                   $getResponse[$arrData["questionid"]]["question"]=$arrData["question_text"];
                   
                   
                   if(!empty($arrData["response_val".$i]))
                   {
                        $getResponse[$arrData["questionid"]]["response_val".$i]=$arrData["response_val".$i];
                   }
                    //[".$arrData["monthno"]."-".$arrData["yearno"] ."]";

               }
           }
        }
      //echo "zdfdg"; print_r($getResponse);die;
        $questionres = '';
        
        return array("getResponse"=>$getResponse,"questionres"=>$questionres,"resp_cnt"=>$resp_cnt);
    }
    
    public function createResponseArray($dateFileterType,$start_date,$end_date,$chartArr,$category, $arrmonths)
    {
        
        $arrStrtmonth = explode("-", $start_date); // this is dateformat 2013-08-31
        $arrEndmonth = explode("-", $end_date);
       
        $xAsisArry = array();
        $red = array();
        $green = array();
        $amber = array();
        $j = 0;
            foreach ($arrmonths as $ak => $av) {
                $monthno = substr($ak, -2);
                $yearno = substr($ak, 0, 4);
                $red[$j] = array("y" => ((isset($chartArr[$ak][$av]["red"]) && $chartArr[$ak][$av]["red"] > 0) ? (float) $chartArr[$ak][$av]["red"] : (float) "0"), "yearno" => $yearno, "monthno" => $monthno);
                $green[$j] = array("y" => ((isset($chartArr[$ak][$av]["green"]) && $chartArr[$ak][$av]["green"] > 0) ? (float) $chartArr[$ak][$av]["green"]:0), "yearno" => $yearno, "monthno" => $monthno);
                $amber[$j] = array("y" => ((isset($chartArr[$ak][$av]["amber"]) && $chartArr[$ak][$av]["amber"] > 0) ?(float) $chartArr[$ak][$av]["amber"]:0), "yearno" => $yearno, "monthno" => $monthno); 
                $xAsisArry[$j] = $av;
                $j++;
           }
        return array("xAsisArry"=>  array_reverse($xAsisArry),"red"=>array_reverse($red),"green"=>array_reverse($green),"amber"=>array_reverse($amber));
    }
    
    /* public function generatepdfAction() {
             
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $arrConfigVariables = array('ESR_PDF_INFO');
        $config_queid = $this->_objCconfig->getConfigQueIds($arrConfigVariables);
        $pdfInfo = unserialize($config_queid["ESR_PDF_INFO"]);
        
        ///////////////////BOC BY Dipa For PDF creation/////////////////////////////
        // this session value req for creating PDF       
        $session = new Zend_Session_Namespace('access_heirarchy');
        $arrUserdtls = (array)$this->_user;
        $reqFields = array("role_id","id","dealer_id","branch_id","sales_region_id");
        
        foreach($arrUserdtls as $k=>$udtls)
        {
            if(in_array($k,$reqFields) && !empty($udtls))
            {
                $userinfo["userinfo"][$k]=$udtls;
            }
        }
        $userinfo["userinfo"]["langid"] = $this->langid;
        //print_R($session->accessHierarchy);//die;
        if($userinfo["userinfo"]["role_id"] > 1)
        {
            foreach($session->accessHierarchy as $k=>$arrV)
            {           
                if(!empty($arrV))
                {
                    $userinfo["accessHierarchy"][$k]=$arrV;
                }
            }
        }        
       //print_r($userinfo);die;
        if(!empty($userinfo))
        {
           $uinfo = serialize($userinfo);
        }
        ///////////////////EOC BY Dipa For PDF creation////////////$pdfDetails->sid = md5(time()."|".$this->_user->role_id);//////////////
        
        $url = HTTP_PATH.$_SERVER["REQUEST_URI"];
       
        $query = parse_url($url, PHP_URL_QUERY);         
        $url .= ($query ? '&' : '?') ."langid=".$this->langid;
        $url .= ($query ? '&' : '?') . 'chkme='.urlencode($uinfo);// Returns a string if the URL has parameters or NULL if not
        $url = str_ireplace("generatepdf", "index", $url);
        //
        //no need to show any extra text for this report
        /* $get = $this->getRequest()->getParams();        
        $footertext = $this->view->CreatePdfInfo($get,"pdf_footer"); 
        $footertext = urlencode($this->view->translate("Question Analysis Report"));
              
        //
        $arrConfigVariables = array('PDF_BIN_PATH');
        $config_queid = $this->_objCconfig->getConfigQueIds($arrConfigVariables);
        try { 
           // print_R($_SERVER["REQUEST_URI"]);die;
            $wkhtmltopdf = new Damco_Wkhtmltopdf(array('path' => APPLICATION_PATH . '/../public/chartSVGFiles/',
                                                       "redirect-delay"=>"5000", 
                                                       "title"=>$pdfInfo["Title"],
                                                       "binpath"=>$pdfInfo["bin_path"],                                                        
                                                       "user-style-sheet"=> HTTP_PATH."/css/pdf.css"
                                                    ));
            $wkhtmltopdf->setTitle("Title");
            $wkhtmltopdf->setUrl($url);
            $wkhtmltopdf->setOptions(array("footer-html"=>"\"".HTTP_PATH.'/survey/index/pdffooter/?customText='.$footertext."\""));
            $wkhtmltopdf->output(Damco_Wkhtmltopdf::MODE_DOWNLOAD, "QAS_".time().".pdf");
        } catch (Exception $e) {
            echo $e->getMessage();
        }  
                
    } */
    
    
    /*
        Controller for export to excel
     *  */
    public function exportexcelquestionAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $request = $this->getRequest();
        $formData = $request->getParams();
        $get = $this->getRequest()->getParams(); 
            
        $survey_category = (isset($get['survey_category']) && !empty($get['survey_category'])) ? $get['survey_category'] : '1';
        
        if (isset($get['eventID']) && $get['eventID']!= '') 
            $eventID = $get['eventID'];
        else 
            $eventID = 0;
        
        if (isset($get['SurveyID']) && $get['SurveyID']!= '') 
            $SurveyID = $get['SurveyID'];
        else 
            $SurveyID = 0;
        
        $startDate = isset($get['start_date'])?$get['start_date']:'';
        $endDate   = isset($get['end_date'])?$get['end_date']:'';
        
        if(isset($get['period']) && $get['period'] == 'by_period')
            $getParamUrl = '&period=by_period&fromDate='.$startDate.'&toDate='.$endDate.'&month=&year=&sel_year=&startDate=&endDate=';
        else 
            $getParamUrl = '';
        
        if($eventID > 0 && $SurveyID>0)
        {
            $excelData = array();
            //$headerOne = array('EventTYpe'=>'EventTYpe','SurveyName'=>'SurveyName');
            //$headerOne[] = array('EventTYpe'=>'Event Type','SurveyName'=>'Survey Name');
            $headerTwo = array('Employee Id'=>'Employee Id','EmployeeName'=>'Employee Name','Employee Email'=>'Employee Email','Qusetion'=>'Questions','Answer'=>'Answers');
            $excelData = $this->_answersModelObject->getAllExcelData($SurveyID,$startDate,$endDate);
            $headerData = $this->_surveyModelObject->getSurveyAndEventTypeName($eventID);
          
            //_surveyModelObject
            
           
            
            if(is_array($excelData) && count($excelData)>0 && count($headerData)>0)
            {
                array_unshift($excelData,$headerTwo);
                $headerOne[] = array('0'=>'Survey:','1'=>$headerData[0]['event_type']);
                $headerOne[] = array('0'=>'Survey Instance Name:','1'=>$headerData[0]['survey_name']);
                
                //$header = array('EventTYpe','SurveyName','EmployeeName','Qusetion','Answer');
                $worksheet_name = $exp_name = 'Consolidate-Analysis-Report';       
                $file_location = $exp_name . date("Y-m-d") . ".xlsx";     
                
                
                $this->_helper->CreateExcelfile($file_location,$excelData,$headerOne,$worksheet_name,'1','0');
                
            }
            else
            {
                $noRecordsFound[] = array('EmployeeName'=>'No Records Found','Qusetion'=>'','Answer'=>'');
                array_unshift($noRecordsFound,$headerTwo);
                
                $headerOne[] = array('0'=>'Event Type:','1'=>$headerData[0]['event_type']);
                $headerOne[] = array('0'=>'Survey Name:','1'=>$headerData[0]['survey_name']);
                
                //$header = array('EventTYpe','SurveyName','EmployeeName','Qusetion','Answer');
                $worksheet_name = $exp_name = 'Consolidate-Analysis-Report';       
                $file_location = $exp_name . date("Y-m-d") . ".xlsx";     
                
                
                $this->_helper->CreateExcelfile($file_location,$noRecordsFound,$headerOne,$worksheet_name,'1','0');
            }
        }
        else 
        {
            $this->_redirect('/report/questionanalysis/consolidate?event_type='.$event_type.'&survey_id='.$surveyID.'&survey_category='.$survey_category.$getParamUrl);    
        }
    }
    
    public function displayingridAction()
    {
        $get = $this->getRequest()->getParams(); 
        $result = $this->_answersModelObject->getAllExcelData($get['surveyID']); 
        
        $this->_helper->json->sendJson($result);
        json_encode($result);
        echo $result;
        exit;
        
        
    }
    
    public function consolidateAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_getByAjax();
        }
        
        $rptObj = new Damco_Core_ReportingFunctions();
        $request = $this->getRequest();
        $formData = $request->getParams();
        $get = $this->getRequest()->getParams(); //print_r($get);die;
        $highchartArray = array();
        $dateRangeField = '';
        $drilldownQueryString = $qus_id_string = "";
        $highchart_mod = new Report_Model_Highchart();
        $allquestiontext = $dateArray = array();
        $start_date = '';
        $end_date = '';
        $period = '';
        // Get Dealers
       /*  $session = new Zend_Session_Namespace('access_heirarchy');
        $questionres = $dealers = array();
        
        if (isset($get['dealer']) && in_array($get['dealer'], $session->accessHierarchy['dealers'])) {
            $dealers[] = "'" . $get['dealer'] . "'";
        } else {
            $accHierarchy = new Damco_Core_AccessHierarchy();
            $result = $accHierarchy->get(
                    (isset($get['branch']) ? $get['branch'] : ''), (isset($get['market']) ? $get['market'] : ''), (isset($get['sales_region']) ? $get['sales_region'] : ''), (isset($get['dealer']) ? $get['dealer'] : '')
            );

            foreach ($result['dealers'] as $value) {
                $dealers[] = "'" . $value['id'] . "'";
            }

            //patch from Harpreet By Dipa 8/13/14 11:53 AM
            if ((!empty($get['branch']) || !empty($get['market']) || !empty($get['sales_region'])
                    ) && empty($dealers)) {
                $dealers = array('-1');
            }
        } 12/30/15 5:13 PM */ 
        
        $survey_category = (isset($get['survey_category']) && !empty($get['survey_category'])) ? $get['survey_category'] : '1';
        $event_type = (isset($get['event_type']) && !empty($get['event_type'])) ? $get['event_type'] : '1';
        $questionid = (isset($get['questionid']) && !empty($get['questionid'])) ? $get['questionid'] : '';      
        
        $dateInfo = $this->getdate($get);
        
        
        $start_date = $dateInfo["start_date"];
        $end_date = $dateInfo["end_date"];
        //BOC to show monthname 9/30/14 2:09 PM
        $this->view->arrmonthName = $arrmonths = $this->createMonthArray($get);
        //EOC to show monthname 9/30/14 2:09 PM
        //print_R($arrmonths);
       /*  if (isset($get['dealer']) || ($this->_user->role_id == 4)) {
            if ($this->_user->role_id == 4) {
                $this->view->showdealer = $showdealer = $this->_user->dealer_id;
            } else {
                if (in_array($get['dealer'], $session->accessHierarchy['dealers'])) {
                    $this->view->showdealer = $showdealer = $get['dealer'];
                }
            }
            
        } 12/30/15 5:13 PM */ 
         //get all questions
        
        $questioninfo = $this->getQuestioninfo($event_type);
        
        $splQids = $arrSplQids = $questioninfo["arrSplQids"];
        
//        array_push($splQids, "35");
        $this->view->arrSplQids = $splQids;
        $npsqid = $questioninfo["npsqid"];
        $allQuestions =  $questioninfo["allQuestions"];
        $where = $questioninfo["where"];
        
        $where["input_type"] = array('checkbox','drop down','radio');
        
        if($questionid > 0)
        {
            
            
            $old_cond = isset($where["seq.questionid"])? $where["seq.questionid"] : "" ;
            $where["seq.questionid"]= $questionid;
            
            $resultOverall = $this->_questionModelObject->getAllquestionsforQA(			
                                                $event_type,//$dealers,
                                                $start_date,$end_date,
                                                $questionid,
                                                //$get["model"],
                                                $this->langid,$arrSplQids);
            
            $allquestiontext = $this->_questionModelObject->getQuestionDetails($where);
            

            /* if($allquestiontext[0]["response1"] == "Make" && $questionid != 35)
            {
                $config_queid = $this->_objCconfig->getConfigQueIds(array('survey_make_name'));
                $arrMakeQids = unserialize($config_queid["survey_make_name"]); 
                foreach($arrMakeQids as $k=>$mkName)
                {
                    $allquestiontext[0]["response".$k] = $mkName;
                }                
            }
            elseif($allquestiontext[0]["response1"] == "Make" && $questionid == 35)
            {
                foreach($this->makeOptions as $k=>$mkName)
                {
                    $allquestiontext[0]["response".$k] = $mkName;
                }                 
             * 
            }12/30/15 5:34 PM */
            
            if(empty($resultOverall))
            {
                $this->view->msg = $this->view->translate("No data found");
                
            }
            else
            {
                
                if(in_array($questionid,$arrSplQids))
                { 
                    
                    $info = $this->createResponseforSplQuestion($resultOverall,"","y",$resultOverall[2][0]["question"]);
                    
                    $getResponse = $info["getResponse"];
                    $questionres = $this->makeOptions;//$info["questionres"]; //10/29/14 5:42 PM
                    $questionres['17']="None, this is my first bike";
                    $resp_cnt=$info["resp_cnt"]+1;
                }
                else
                {
                    
                    $info = $this->createResponseforQuestion($allquestiontext,$resultOverall);
  
                    $getResponse = $info["getResponse"];
                    if($questionid == 35)
                    {
                        $questionres = $this->makeOptions;//$info["questionres"]; //10/29/14 5:42 PM
                    }
                    else
                    {
                         $questionres =  $info["questionres"];
                    }
                        
                   
                    $resp_cnt=$info["resp_cnt"];
                }
                
            }
            if(!empty($getResponse))
            {
                
                    foreach ($arrmonths as $mnth_k=>$mnth_v) 
                    {
                       for($i=1;$i<=$resp_cnt;$i++) {
                           unset($getResponse["response".$i]["actual_response_val"]);
                            if(isset($getResponse["response".$i])
                                && !array_key_exists($mnth_k, $getResponse["response".$i])) {
                                $month_no = substr($mnth_k,-2);
                                $year_no = substr($mnth_k,0,-4);
                                $getResponse["response".$i][$mnth_k] = "0.00<br>(0) [".$month_no."-".$year_no ."]";
                            }
                       }
                    }
                    //print_r($getResponse);die;
                    foreach ($getResponse as $resp=>$aresp) {
                        ksort($aresp);
                        $userResp[$resp] = $aresp;
                    }
                    $this->view->questionResponse = $userResp;

                    unset($where["seq.questionid"]);
                    $drilldownQueryString = '&surveyid='.$get['survey_id'].'&questionid='.$get['questionid'];
                    
                    
            }
            else
            {
                $this->view->msg = $this->view->translate("No data found");
            }
        }
        
        //  print_r($finalArr);die;
        $get['startDate'] = $start_date;
        $get['endDate'] = $end_date;
        /* $this->view->dealer_cnt = count($dealers);
        $this->view->role_name = $this->_user->role_name; 12/30/15 5:14 PM */ 
        $this->view->questiontext = isset($allquestiontext[0]) ? $allquestiontext[0]["question"] : "";
        $this->view->npsqid = $npsqid;
        $this->view->drilldownQueryString = $drilldownQueryString;//.$selp;
        $this->view->get = $get;
    }
    
    
    public function surveystatusAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_getByAjaxSurveyStatus();
        }
        
        //$rptObj = new Damco_Core_ReportingFunctions();
        $request = $this->getRequest();
        $formData = $request->getParams();
        $get = $this->getRequest()->getParams(); //print_r($get);die;
        
        $highchartArray = array();
        $dateRangeField = '';
        $drilldownQueryString = $qus_id_string = "";
        $highchart_mod = new Report_Model_Highchart();
        $allquestiontext = $dateArray = array();
        $start_date = '';
        $end_date = '';
        $period = '';
        // Get Dealers
       /*  $session = new Zend_Session_Namespace('access_heirarchy');
        $questionres = $dealers = array();
        
        if (isset($get['dealer']) && in_array($get['dealer'], $session->accessHierarchy['dealers'])) {
            $dealers[] = "'" . $get['dealer'] . "'";
        } else {
            $accHierarchy = new Damco_Core_AccessHierarchy();
            $result = $accHierarchy->get(
                    (isset($get['branch']) ? $get['branch'] : ''), (isset($get['market']) ? $get['market'] : ''), (isset($get['sales_region']) ? $get['sales_region'] : ''), (isset($get['dealer']) ? $get['dealer'] : '')
            );

            foreach ($result['dealers'] as $value) {
                $dealers[] = "'" . $value['id'] . "'";
            }

            //patch from Harpreet By Dipa 8/13/14 11:53 AM
            if ((!empty($get['branch']) || !empty($get['market']) || !empty($get['sales_region'])
                    ) && empty($dealers)) {
                $dealers = array('-1');
            }
        } 12/30/15 5:13 PM */ 

        $event_type = (isset($get['event_type']) && !empty($get['event_type'])) ? $get['event_type'] : '1';
        //$questionid = (isset($get['questionid']) && !empty($get['questionid'])) ? $get['questionid'] : '';      
        
        
        $dateInfo = $this->getdate($get);
        
        $start_date = $dateInfo["start_date"];
        $end_date = $dateInfo["end_date"];
        //BOC to show monthname 9/30/14 2:09 PM
        $this->view->arrmonthName = $arrmonths = $this->createMonthArray($get);
        //EOC to show monthname 9/30/14 2:09 PM
        //print_R($arrmonths);
       /*  if (isset($get['dealer']) || ($this->_user->role_id == 4)) {
            if ($this->_user->role_id == 4) {
                $this->view->showdealer = $showdealer = $this->_user->dealer_id;
            } else {
                if (in_array($get['dealer'], $session->accessHierarchy['dealers'])) {
                    $this->view->showdealer = $showdealer = $get['dealer'];
                }
            }
            
        } 12/30/15 5:13 PM */ 
         //get all questions
        
        $questioninfo = $this->getQuestioninfo($event_type);
        
        
        $splQids = $arrSplQids = $questioninfo["arrSplQids"];
        
//        array_push($splQids, "35");
        $this->view->arrSplQids = $splQids;
        
        
        $get['startDate'] = $start_date;
        $get['endDate'] = $end_date;
        /* $this->view->dealer_cnt = count($dealers);
        $this->view->role_name = $this->_user->role_name; 12/30/15 5:14 PM */ 
        $this->view->questiontext = isset($allquestiontext[0]) ? $allquestiontext[0]["question"] : "";
        $this->view->npsqid = '';
        $this->view->drilldownQueryString = '';//.$selp;
        $this->view->get = $get;
    }
    
     private function _getByAjaxSurveyStatus($returnArray = FALSE) {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $get = $this->getRequest()->getParams();
        $aColumns = array( 'SurveyName','Total','Closed','Open','InProgress','Status','end_date');
        
    	if(!isset($get['ordercolumn'])) {
            if(isset($get['order'][0]['column'])) {
                if(is_numeric($get['order'][0]['column'])) {
                    $field = $aColumns[$get['order'][0]['column']];
                    $sortby = ($get['order'][0]['dir']==='asc')? 'ASC' : 'DESC';
                    $sOrder = $field. ' ' .$sortby;
                } 
                else {
                    $sOrder = '';
                }
            }
            else {
                $sOrder = '';
            }
	        
        } 
        else {
            $field = $aColumns[$get['ordercolumn']];
            $sortby = ($get['orderdir']==='asc')? 'ASC' : 'DESC';
            $sOrder = $field. ' ' .$sortby;
        }
        
        $params = array(
                'event_type_id'=>$get['EventTypeID'],
                'start_date'=> isset($get['start_date'])?$get['start_date']:'',
                'end_date' => isset($get['end_date'])?$get['end_date']:'',
                'start'=>isset($get['start'])?$get['start']:'',
                'length'=>isset($get['length'])?$get['length']:'',
                'orderBy'=>$sOrder //Added By Amit kumar 16/09/14 3:46 PM for Sorting
            );
        
        if(isset($get['EventTypeID']) && $get['EventTypeID']!='') {
            $result = $this->_surveyEvenrtModelObject->getSurveyStatusData($params); 
            $rowCount = $this->_surveyEvenrtModelObject->getCountData($params); 
        }
        else {			
            $result = $this->_surveyEvenrtModelObject->getSurveyStatusData($params); 
            $rowCount = $this->_surveyEvenrtModelObject->getCountData($params);
        }
        
        if(count($result)>0) {
            $data = array(
                'draw' => isset($get['draw'])?$get['draw']:'',
                'recordsTotal' => $rowCount[0]['COUNT'], //$result[1][0]['tot'],
                'recordsFiltered' => $rowCount[0]['COUNT'] //$result[1][0]['tot'],
            );
            $data['data'] = array();
            
            $show_drlldown = $export_to_excel = 0;
            if ( $this->view->hasAccess( $this->_user->role_name, 'event', 'index', 'surveystatus' ) ) {
                $show_drlldown = 1;
            }
            
            if ( $this->view->hasAccess( $this->_user->role_name, 'report', 'questionanalysis', 'exportexcelconsolidate' ) ) {
                $export_to_excel = 1;
            }
            foreach ($result as $value) {
                $temp = array(
                        //$value['SurveyName'],
                        $show_drlldown > 0 ? '<a href = "'. $this->view->serverUrl().'/event/index/surveystatus/surveyid/'.$value['survey_id'] .'">'.$value['SurveyName'].'</a>' : $value['SurveyName'],
                        $show_drlldown > 0 ? '<a href = "'. $this->view->serverUrl().'/event/index/surveystatus/surveyid/'.$value['survey_id'] .'">'.$value['Total'].'</a>' : $value['Total'],
                        $show_drlldown > 0 ? '<a href = "'. $this->view->serverUrl().'/event/index/surveystatus/surveyid/'.$value['survey_id'] .'/?event_status=Closed">'.$value['Closed'].'</a>' : $value['Closed'],
                        $show_drlldown > 0 ? '<a href = "'. $this->view->serverUrl().'/event/index/surveystatus/surveyid/'.$value['survey_id'] .'/?event_status=Open">'.$value['Open'].'</a>' : $value['Open'],
                        $show_drlldown > 0 ? '<a href = "'. $this->view->serverUrl().'/event/index/surveystatus/surveyid/'.$value['survey_id'] .'/?event_status=In progress">'.$value['InProgress'].'</a>' : $value['InProgress'],
                        $value['STATUS'],
                        ( $value['Closed'] > 0 && $export_to_excel > 0 ) ? '<a href = "javascript:void(0);" onclick="exportTOConsolidateReport('.$value['survey_id'].')">Export To Excel</a>':''
                );
                $data['data'][]=$temp;
            } 
        }
        else {
            $data = array(
                'draw' => isset($get['draw'])?$get['draw']:'',
                'recordsTotal' => '0', //$result[1][0]['tot'],
                'recordsFiltered' => '0' //$result[1][0]['tot'],
            );
            $data['data'] = array();
        }
        
        echo json_encode( $data );
        exit;
    }
    
     /*
        Controller for export to excel
     *  */
    public function exportexcelsurveystatusAction()
    {
        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $request = $this->getRequest();
        $formData = $request->getParams();
        $get = $this->getRequest()->getParams(); 
        
        $survey_category = (isset($get['survey_category']) && !empty($get['survey_category'])) ? $get['survey_category'] : '1';    
        
        if (isset($get['eventTypeID']) && $get['eventTypeID']!= '') 
            $eventTypeID = $get['eventTypeID'];
        else 
            $eventTypeID = 0;
        
        if($eventTypeID > 0)
        {
            $excelData = array();
            //$headerOne = array('EventTYpe'=>'EventTYpe','SurveyName'=>'SurveyName');
            //$headerOne[] = array('EventTYpe'=>'Event Type','SurveyName'=>'Survey Name');
            $headerTwo = array('SurveyName'=>'Survey Instance Name','Total'=>'Target Count','Closed'=>'Closed','Open'=>'Open','InProgress'=>'In Progress','Status'=>'Status');
            $excelData = $this->_surveyEvenrtModelObject->getSurveyStatusExcelData($eventTypeID);
            
            $headerData = $this->_eventTypeObj->getEventTypesNameByID($eventTypeID);
            
            //_surveyModelObject
            
           if(is_array($excelData) && count($excelData)>0 && count($headerData)>0)
            {
                array_unshift($excelData,$headerTwo);
                $headerOne[] = array('0'=>'Survey :','1'=>$headerData[0]['event_type']);
                //$headerOne[] = array('0'=>'SurveyName:','1'=>$headerData[0]['survey_name']);
                
                //$header = array('EventTYpe','SurveyName','EmployeeName','Qusetion','Answer');
                $worksheet_name = $exp_name = 'Survey-Status-Analysis-Report';       
                $file_location = $exp_name . date("Y-m-d") . ".xlsx";     
                
                
                $this->_helper->CreateExcelfile($file_location,$excelData,$headerOne,$worksheet_name,'1','0');
                
            }
            else
            {
                $noRecordsFound[] = array('EmployeeName'=>'No Records Found','Qusetion'=>'','Answer'=>'');
                array_unshift($noRecordsFound,$headerTwo);
                
                $headerOne[] = array('0'=>'Event Type:','1'=>$headerData[0]['event_type']);
                $headerOne[] = array('0'=>'Survey Name:','1'=>$headerData[0]['survey_name']);
                
                //$header = array('EventTYpe','SurveyName','EmployeeName','Qusetion','Answer');
                $worksheet_name = $exp_name = 'Consolidate-Analysis-Report';       
                $file_location = $exp_name . date("Y-m-d") . ".xlsx";     
                
                
                $this->_helper->CreateExcelfile($file_location,$noRecordsFound,$headerOne,$worksheet_name,'1','0');
            }
        }
        else 
        {
            $this->_redirect('/report/questionanalysis/surveystatus?event_type='.$event_type.'&survey_category='.$survey_category);    
        }
    }
	
	/*
        Controller for export to consolidate excel
     *  */
    public function exportexcelconsolidateAction()
    { 
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $request = $this->getRequest();
        $formData = $request->getParams();
        $get = $this->getRequest()->getParams(); 
          
        $survey_category = (isset($get['survey_category']) && !empty($get['survey_category'])) ? $get['survey_category'] : '1';
        
        if (isset($get['SurveyID']) && $get['SurveyID']!= '') 
            $SurveyID = $get['SurveyID'];
        else 
            $SurveyID = 0;
        if (isset($get['eventID']) && $get['eventID']!= '') 
            $eventID = $get['eventID'];
        else {
            if($SurveyID>0) {
                $data = $this->_surveyModelObject->getSurveyEventID($SurveyID);
                $eventID = $data[0]['event_typeid'];
            }
            else {
                $eventID = 0;
            }
        }
            
        $startDate = isset($get['start_date'])?$get['start_date']:'';
        $endDate   = isset($get['end_date'])?$get['end_date']:'';
        
        if(isset($get['period']) && $get['period'] == 'by_period')
            $getParamUrl = '&period=by_period&fromDate='.$startDate.'&toDate='.$endDate.'&month=&year=&sel_year=&startDate=&endDate=';
        else 
            $getParamUrl = '';
        if($eventID > 0 && $SurveyID > 0)
        {
			//echo $eventID;
			
			$getquestiondata = $this->_questionModelObject->getAllQuestionDataByEventTypeId($eventID);
			foreach($getquestiondata as $getquestions){
				$questions[$getquestions['questionid']] = strip_tags($getquestions['question']);
			}
			$excelData = array();
           
            $header_static = array('Employee Id'=>'Employee Id','EmployeeName'=>'Employee Name','Employee Email'=>'Employee Email','survey_date' => 'Survey Date');
			$headerTwo = array_merge($header_static, $questions, array('Percentage' => 'Percentage'));
			
            //  $excelData = $this->_answersModelObject->getAllExcelData($SurveyID,$startDate,$endDate);
            $headerData = $this->_surveyModelObject->getSurveyAndEventTypeNameforConsolidate($SurveyID);
            $excelData = $this->_surveyEvenrtModelObject->exceldataconsolidate($SurveyID,$startDate,$endDate);
		    //echo "<pre>";print_r($questions);die;
			$questionIdsArr = array_keys($questions);
            
            // taken just for percent column to show if max_Score>0 so should be visible
            $percentStatus = false; 

			foreach($excelData as $keys =>$geteventid){
				$answer_data = $this->_answersModelObject->getAnswersByEventId($geteventid['eventid']);
                foreach($questionIdsArr as $key => $value) {
					$excelData[$keys]['ans'.$key] = '-';
				}
                $totalScore = 0;
                $ansScores = 0;
				foreach($answer_data as $key => $answers){
					if (strpos($answers['response_options'], ',') !== false) {
						$answer_arr = explode(',',$answers['response_options']);
					
						$ans='';
			
						for($i = 0; $i < count($answer_arr); $i++){
							
							$ans = $ans.$answers['answer'.$answer_arr[$i]].',';
							
						}
						 $excelData[$keys]['ans'.$key] =  $ans;
					}
					else{
						$key = array_search($answers['questionid'], $questionIdsArr);
                        if($answers['max_score'] > 0){
                            $totalScore += $answers['max_score'];
                            $ansScores += $answers['score'];   
                            $excelData[$keys]['ans'.$key] = ($answers['score'] > 0) ? $answers['score'] : 0;
                            $percentStatus = true;
                        } 
						else{
                            $excelData[$keys]['ans'.$key] = $answers['answer'.$answers['response_options']];
                        }
					}
				    unset($excelData[$keys]['eventid']);
					
				}
                if($totalScore > 0){
                    // $excelData[$keys]['Total Score'] = $totalScore;
                    // $excelData[$keys]['Ans Score'] = $ansScores;
                    $excelData[$keys]['Percentage'] = ($ansScores / $totalScore)*100;
                }
	        //echo "<pre>";print_r($excelData);die;
			}
            // echo "<pre>";print_r($excelData);
            // die;

            // adding codnition if (max_score > 0) then will show percentage column otherwise no
            if($percentStatus){
                $headerTwo = array_merge($header_static, $questions, array('Percentage' => 'Percentage'));
            } else{
                $headerTwo = array_merge($header_static, $questions);
            }

            if(is_array($excelData) && count($excelData) > 0 && count($headerData) > 0)
            {
                array_unshift($excelData, $headerTwo);
                //echo "<pre>";print_r($excelData);exit;
                $headerOne[] = array('0'=>'Survey:','1'=>$headerData[0]['event_type']);
                $headerOne[] = array('0'=>'Survey Instance Name:','1'=>$headerData[0]['survey_name']);
                
                //$header = array('EventTYpe','SurveyName','EmployeeName','Qusetion','Answer');
                $worksheet_name = $exp_name = 'Consolidate-Analysis-Report';       
                $file_location = $exp_name . date("Y-m-d") . ".xlsx";     
                $this->_helper->CreateExcelfile($file_location,$excelData,$headerOne,$worksheet_name,'1','0');
                
            }
            else
            {
                $noRecordsFound[] = array('EmployeeName'=>'No Records Found','Qusetion'=>'','Answer'=>'');
                array_unshift($noRecordsFound,$headerTwo);
                
                $headerOne[] = array('0'=>'Event Type:','1'=>$headerData[0]['event_type']);
                $headerOne[] = array('0'=>'Survey Name:','1'=>$headerData[0]['survey_name']);
                
                //$header = array('EventTYpe','SurveyName','EmployeeName','Qusetion','Answer');
                $worksheet_name = $exp_name = 'Consolidate-Analysis-Report';       
                $file_location = $exp_name . date("Y-m-d") . ".xlsx";     
                
                
                $this->_helper->CreateExcelfile($file_location,$noRecordsFound,$headerOne,$worksheet_name,'1','0');
            }
        }
        else 
        {
            $this->_redirect('/report/questionanalysis/consolidate?event_type='.$event_type.'&survey_id='.$surveyID.'&survey_category='.$survey_category.$getParamUrl);    
        }
 
    }
    
}
