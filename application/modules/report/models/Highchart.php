<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class Report_Model_Highchart extends Default_Model_Core {
    
    public function __construct() {
        parent::__construct();
    }
  
    //for 1st & 2nd chart
    public function getHighchartData($event_type,$dealerID,$start_date,$end_date,$questionid,$type) {
        if(!empty($dealerID) ){
            $dealer_str=implode(',', $dealerID);
            
        }else $dealer_str='';
        $this->_objConfig = Zend_Registry::get('config');
        $db_name=$this->_objConfig['resources']['db']['params']['dbname'];
        $in_param=array($db_name, $event_type, $start_date, $end_date, $type,$dealer_str,$questionid);
        $result = $this->_spObj->getSpData('usp_reportOverAllYTD', $in_param, false);
        return $result;
      
    }
    //3rd & 4th chart
    public function getHighchartMonthData($event_type,$dealerID,$start_date,$end_date,$period,$dateArray=array(),$questionid,$type) {
        $this->_objConfig = Zend_Registry::get('config');
        $db_name=$this->_objConfig['resources']['db']['params']['dbname'];
        if(!empty($dealerID)){
            $dealer_str=implode(',', $dealerID);
            
        }else $dealer_str='';

        $in_params   = array($db_name,$event_type,$start_date,$end_date,$type,$dealer_str,$questionid);
        $result = $this->_spObj->getSpData('usp_reportOverAllMonth', $in_params, false);
        return $result;
    }
    
    public function getYTD(){
        $select ="select GetYtd(1,'start') ytd_start, GetYtd(1,'end') ytd_end ";
         $result=$this->db->fetchAll($select);
        return $result[0];
    }
    
    //for 1st chart
    function highChartDataOvetAllYtd($event_type,$dealerID,$start_date,$end_date){
        if(!empty($dealerID)){
            $dealer_str=implode(',', $dealerID);
        }else $dealer_str='';
        $this->_objConfig = Zend_Registry::get('config');
        $db_name=$this->_objConfig['resources']['db']['params']['dbname'];
        $in_params   = array($db_name,$event_type,$start_date,$end_date,'satisfaction',$dealer_str);
        $result = $this->_spObj->getSpData('usp_reportOverAllYTD', $in_params, FALSE);
        return $result;
    } 
    
    
     function showDrilldown($params=array(),$dealers){
        if(!empty($dealers)){
            $dealer_str=implode(',', $dealers);
            
        }else $dealer_str=''; 
        $this->_objConfig = Zend_Registry::get('config');
        $db_name=$this->_objConfig['resources']['db']['params']['dbname'];
        if(isset($params['startDate']) && isset($params['endDate'])){
            $start_date=$params['startDate'];
            $end_date=$params['endDate'];
            
        }
        $seriesVal=" ";
        if(isset($params['series_name'])){
            $series_arry=explode(",",$params['series_name']);
            
            foreach($series_arry as $series){
                 if($seriesVal==" ")$seriesVal ="'".$series."'";
                 else $seriesVal =$seriesVal.",'".$series."'";
            }
        }
        if(isset($params['event_type']))$event_type=$params['event_type'];else $event_type="";
        if(isset($params['search_key']))$search_keyword=$params['search_key'];else $search_keyword="";
        if(isset($params['model']))$model=$params['model'];else $model="";
       //echo $dealer_str;
        $in_params= array($db_name,$dealer_str,'',$params['series_name'],$event_type,'',$start_date
        ,$end_date,'','','', '',$params['start'],$search_keyword, $params['length'], '', '' ,''
        ,'', true, '', '','','', '',$model);
        $result= $this->_spObj->getSpData("usp_RPTGetDrilldown", $in_params,
        false);
        return $result;
        
      }
      
      public function getTotalMarketDealer ($dealer_id,$start_date,$end_date,$event_type){
        $cond="";
        $cond=" and survey_date>='".$start_date."' and survey_date<='".$end_date."'";
       if($event_type!='' && $event_type!=0){
           $cond.=" and event_typeid='".$event_type."'";
       }else {
            $cond.=" and event_typeid not in ('2') ";
       }
       if($dealer_id!=''){
            $sql="SELECT marketid FROM dealers WHERE id in ('".$dealer_id."')";
            $result=$this->db->fetchAll($sql); 
            $marketid= $result[0]['marketid'];
            if($marketid!=''){
                 $sql_nation="SELECT nation_id FROM nation_markets WHERE market_structid='".$marketid."'";
                 $result_nation=$this->db->fetchAll($sql_nation); 
                 if($result_nation){
                     $nation_id=$result_nation[0]['nation_id'];
                     if($nation_id!=''){
                         $sql="SELECT market_structid FROM nation_markets WHERE nation_id='".$nation_id."'";
                         $result=$this->db->fetchAll($sql);
                         $market_struct_id='';
                         foreach($result as $market_structidVal){
                             if($market_struct_id=='')$market_struct_id ="'".$market_structidVal['market_structid']."'";
                             else $market_struct_id =$market_struct_id.','."'".$market_structidVal['market_structid']."'";
                         }
                         if($market_struct_id){
                                 $sql=" SELECT COUNT(distinct(deal.id)) AS tot_dealer FROM
                                    survey_events event INNER JOIN dealers deal ON 
                                    event.dealer_id=deal.id WHERE deal.marketid IN 
                                   (".$market_struct_id.") $cond  and event_status='Closed' and is_deleted='0'";//echo $sql.'<br>';
                                 $result=$this->db->fetchAll($sql); //echo $sql;
                                 return  $result[0]['tot_dealer'];
                         }
                     }
                }
            
           }
       }
             
    }
    
    public function getDealerRank ($dealer_id,$start_date,$end_date,$nation="",$event_type="",$top_count="",$scoringQids=''){
       $cond="";
       $nationCond="";
       $limit="";
       $cond=" and survey_date between '".$start_date."' and '".$end_date."'";
       if($event_type!='' && $event_type!=0){
           $cond.=" and event_typeid='".$event_type."'";
       }else {
            $cond.=" and event_typeid not in ('2') ";
       }
       if($top_count!=''){
           $limit ="limit ".$top_count;
       }
       if($dealer_id!=''){
       $sql="SELECT marketid FROM dealers WHERE id in ('".$dealer_id."')"; 
       $result=$this->db->fetchAll($sql); 
       
       $market="";
       foreach($result as $resulData){
           //Dipa 8/4/14 5:10 PM
           $market = !empty($market) ? ",'".trim($resulData['marketid'])."'" : trim($resulData['marketid']) ;
       }
      
       $marketid= $result[0]['marketid'];
       if($market!=''){
            $sql_nation="SELECT nation_id FROM nation_markets WHERE market_structid in ('".$market."')";
            $result_nation=$this->db->fetchAll($sql_nation); 
            if($result_nation){
                $nation_id=$result_nation[0]['nation_id'];
                if($nation_id!=''){
                    $sql="SELECT market_structid FROM nation_markets WHERE nation_id='".$nation_id."'";
                    $result=$this->db->fetchAll($sql);
                    $market_struct_id='';
                    foreach($result as $market_structidVal){
                        if($market_struct_id=='')$market_struct_id ="'".$market_structidVal['market_structid']."'";
                        else $market_struct_id =$market_struct_id.','."'".$market_structidVal['market_structid']."'";
                    }
                    //if($market_struct_id){
                    if(!empty($scoringQids))
                    {
                        $cond .= " and a.`questionid` IN (".$scoringQids.") ";
                    }
                        if($nation!=''){
                            $nationCond=" left join nation_markets nation 
                            on deal.marketid=nation.market_structid WHERE  nation_id=$nation_id and event_status='Closed' and is_deleted='0' $cond";
                        }else{
                           $nationCond=" WHERE deal.marketid IN (".$market_struct_id.") and   is_deleted='0' and   event_status='Closed' $cond 
                                         GROUP BY dealer_id having event_count>5"; 
                        }
                         $sql="SELECT @rn:=@rn+1 AS rank, dealer_id,  dealer_rank
                            FROM (SELECT dealer_id, round((SUM(CASE WHEN response_options between 10 and 11
					     THEN 1 ELSE 0 END)/COUNT(e.eventid))*100 - 
                                             (SUM(CASE WHEN response_options between 1 and 7
					     THEN 1 ELSE 0 END)/COUNT(e.eventid))*100,2) 
                            AS dealer_rank,count(e.eventid) as event_count FROM survey_events e
                            INNER JOIN `survey_event_answers` a ON e.eventid=a.eventid
                            LEFT JOIN dealers deal
                            ON e.dealer_id=deal.id $nationCond  "
                                 . "ORDER BY dealer_rank DESC $limit) t1, 
                            (SELECT @rn:=0) t2"; //echo $sql.'<br>';die;
                            $result=$this->db->fetchAll($sql); 
                            return  $result;
                    //}
                }
            }
        }
       }     
 }
 
  public function getDealerData($dealer_id) {
        $rs = $this->db->select()
                ->from(array('dealers'),
                array('country_name','dealer_name'))
                ->where("id in (" .$dealer_id.")")
                ->where("is_deleted = '0' ") ;
        $result= $rs->query()->fetchAll();
        return $result[0];
    }
    
    
    public function getDealerDataMonthwise($month,$year,$dealer_id,$event_type){
        $cond="";
        if($event_type!='' && $event_type!=0){
            $cond="and event_typeid='".$event_type."'";
        }
        if($dealer_id!=""){
          $cond.=" and dealer_id in (".$dealer_id.")";  
        }
        $start_date=$year.'-'.$month.'-01';
        $end_date=$year.'-'.$month.'-31';
        $rs = $this->db->select()
                ->from(array('event'=>'survey_events'),
                array('event_count' => 'count(eventid)'))
                ->joinleft(array('deal' => 'dealers'),
                    'event.dealer_id = deal.id',array())
                ->where('event.event_status = "Closed" and event.event_typeid!=2 
                         '.$cond.' and survey_date>="'.$start_date.'"
                         and survey_date<="'.$end_date.'" and  is_deleted="0"');
       
        $result= $rs->query()->fetchAll();
        return $result[0];
    }
    
    
    public function getDealerNPSMonthwise($month,$year,$dealer_id,$event_type){
        $start_date=$year.'-'.$month.'-01';
        $end_date=$year.'-'.$month.'-31';
        $cond="";
        if($event_type!='' && $event_type!=0){
            $cond=" event_typeid='".$event_type."' and ";
        }else{
             $cond=" event.event_typeid!='2' and";
        }
        if($dealer_id!=""){
          $cond.="  dealer_id in (".$dealer_id.") and";  
        }
        $rs = $this->db->select()
                ->from(array('event'=>'survey_events'),
                array('score' => "round((SUM(CASE WHEN satisfaction_percent between 90  and 100
			          THEN 1 ELSE 0 END)/COUNT(eventid))*100 - 
                                  (SUM(CASE WHEN satisfaction_percent between 10 and 60
				  THEN 1 ELSE 0 END)/COUNT(eventid))*100,2)"))
               ->joinleft(array('deal' => 'dealers'),'event.dealer_id = deal.id',
                          array())
               ->where('event.event_status = "Closed"  and 
                        '.$cond.'  survey_date between "'.$start_date.'" and 
                        "'.$end_date.'" and  is_deleted="0"'); //echo $rs.'<br>';// exit;
        $result= $rs->query()->fetchAll();
        return $result[0];
    }
    
    public function getLowScoreQus($dealers=array(),$event_type,$start_date,$end_date,$qus_ids){
        if(!empty($dealers)){
         //print_r($dealers);exit; 
            $dealer_str=implode(',', $dealers);
            $cond= " event.dealer_id IN (".$dealer_str.") and event_status='Closed' ";
        } 
        else{$cond=" 1 ";}
        
        if($event_type!=''&& $event_type!=0){
            $cond.= " and event.event_typeid='".$event_type."'";
        }else{
          $cond.="  and event.event_typeid!='2' and question_type='Q'";
        }
        if($qus_ids!='')
            $cond.= " and ans.questionid in (".$qus_ids.")";
        if($start_date!=''&& $end_date!=''){
            $cond.= " and event.survey_date between '".$start_date."' and '".$end_date."' ";
        }
        //query chnages by Dipa
        $rs = $this->db->select()
               ->from(array('ans'=>'survey_event_answers'),
                array('ans.questionid', 'location_score'=>'SUM(response_options-1)/(10*COUNT(response_options))*100',
                    'event_count'=>'COUNT(event.eventid)','parent_id'=>'(CASE WHEN parent_id>0 THEN parent_id ELSE 0 END)' ))
               ->joinleft(array('ques' => 'survey_event_questions'),
                     'ans.questionid=ques.questionid',
                          array('parent_id'))
               ->joinleft(array('ques_lang' => 'survey_event_question_langs'),
                     'ans.questionid=ques_lang.questionid and ques_lang.langid=1',
                          array('question'))
               ->joinleft(array('ques_lang1' => 'survey_event_question_langs'),
                     'ques.parent_id=ques_lang1.questionid and ques_lang1.langid=1',
                          array("parent_question"=>'question'))
               ->joinleft(array('event' => 'survey_events'),
                     'ans.eventid=event.eventid',
                          array())
               ->where($cond)
               ->group('ans.questionid')
               ->order('location_score ASC'); 
        //echo $rs->__toString();die;
        $result= $rs->query()->fetchAll(); 
        
        //echo "<pre>";print_R($result);die;
        return $result;
    }
    
    public function getMonth($monthNo){
        $monthArray = array("01"=>"January","02"=>"February", "03"=>"March", 
              "04"=>"April","05"=>"May", "06"=>"June","07"=>"July",
              "08"=>"August","09"=>"September","10"=>"October","11"=>"November","12"=>"December");
        foreach($monthArray as $key=>$month){
            if($key==$monthNo){
                return $month;
            }
        }
        
    }
    //8/25/14 1:48 PM need to remove
    public function getQuestionDealerRating($dealer_id,$start_date,$end_date,$event_type,$qus_id){
        if(trim($dealer_id,"'")!=''){ 
            $cond="dealer_id in (" .$dealer_id.")";
        }else {
            $cond="1";
        }
       $rs = $this->db->select()
                ->from(array('ans'=>'survey_event_answers'),
                array('ans.questionid','ques.question','round((SUM(response_options-1)/(10*COUNT(answerid)))*100,2) AS score' ))
               ->joinleft(array('ques' => 'survey_event_question_langs'),'ans.questionid=ques.questionid',
                          array())
               ->joinInner(array('event' => 'survey_events'),'ans.eventid=event.eventid',
                          array())
                ->where($cond)
                ->where("date(answer_date)>= ?", $start_date)
                ->where("date(answer_date) <= ?", $end_date)
                ->where("ans.questionid = ?", $qus_id)
                ->where("event_status = ?", "Closed")
               ->where("ques.langid = ?", "1")
               ->group('ans.questionid'); //echo $rs;
        $result= $rs->query()->fetchAll();
        return $result;
        
    }
    
     public function getCustomerAlert($event_type,$dealers,$model,$start_date,$end_date, $roleID = '0'){
         $dealer_str="";
         if(!empty($dealers))
            $dealer_str=implode(',', $dealers);
         //changes made as per Naren sir 8/16/14 8:33 PM
         if(empty($event_type))
         {
             $cond=" 1 ";
         }
         elseif( $roleID != '4' )
         {
             $cond=" 1 and event_typeid not in ('2') ";
         }
         else {
             $cond=" 1 ";
         }
         
         if ( $roleID == '4' && $event_type == '3' ) {
             $event_type .= ', 2';
         }
         
         if($event_type!='' && $event_type!=0){ $cond.=" and event_typeid IN (".$event_type.")"; }
         if($dealer_str!=''){ $cond.=" and dealer_id in (".$dealer_str.")"; }
        
        if($start_date!='' && $end_date!=''){ $cond.=" and survey_date between '".$start_date." 00:00:00"."' and '".$end_date." 23:59:59'" ; }
        $rs = $this->db->select()
                ->from(array('survey_events'),
                array('customer_cnt'=>'count(eventid)'))
                ->where($cond)
                ->where(" ( code_red_status = 'Open' OR code_red_status = 'Reopened' ) ")
                ->where("code_status = ?", "Red")
                ->where("event_status = ?", "Closed") ; //echo $rs;
               
//              echo  $rs->__toString();die;
        $result= $rs->query()->fetchAll();
        return $result[0];
       
     }
    
    public function getPerformanceAlert($event_type,$dealers,$model,$start_date,$end_date){
        
        $dealer_str="";
         if(!empty($dealers))
            $dealer_str=implode(',', $dealers);
         $cond=" 1 and event_typeid not in ('2') ";
         if($event_type!='' && $event_type!=0){ $cond.=" and event_typeid=".$event_type; }
         if($dealer_str!=''){ $cond.=" and dealer_id in (".$dealer_str.")"; }
        
        if($start_date!='' && $end_date!=''){ $cond.=" and survey_date between '".$start_date." 00:00:00"."' and '".$end_date." 23:59:59'" ; }
        $rs = $this->db->select()
                ->from(array('survey_events'),
                array('performanc_cnt'=>'count(eventid)'))
                 ->where($cond)
                ->where("is_performance_alert = ?", "1")
                ->where("event_status = ?", "Closed")
                ->where("event_typeid != ?", "2"); //echo $rs; exit;
        $result= $rs->query()->fetchAll();
        return $result[0];
        
    }
    
    //query chnages by dipa
    public function getNationComparisindata ($dealer_id,$event_type,$start_date,$end_date,$qus_ids){
        if($dealer_id!=''){
        $sql="SELECT marketid FROM dealers WHERE id in (".$dealer_id.")";
        $result=$this->db->fetchAll($sql); 
        $market="";
        foreach($result as $resulData){
            /**/ if($market==''){
            $market="'".$resulData['marketid']."'";
            }
            else{
                $market.=",'".$resulData['marketid']."'";
            }
            $arrmarket[]=$resulData['marketid'];
        }
        if($market!=''){
            
                $dealer_id="";
                $myresult=$this->db->select()
                         ->from(array("nm"=>"nation_markets"),array())
                         ->joinInner(array("nm1"=>"nation_markets"),'nm.nation_id=nm1.nation_id',array())
                         ->joinLeft(array("d"=>"dealers"),'d.`marketid`=nm1.market_structid',array('dealerid'=>new Zend_Db_Expr("group_concat(d.id)")))
                         ->where("nm.market_structid in (?) ",$arrmarket)//->__toString();die;
                         ->query()
                         ->fetch();
                 //print_R($myresult);die;
                 $dealer_id = $myresult["dealerid"];
                 if($dealer_id!=''){
                     $cond= " event.dealer_id IN (".$dealer_id.") and event_status='Closed' ";
                 } else{$cond=" event_status='Closed' ";}

                 if($event_type!='' && $event_type!=0){
                     $cond.= " and event.event_typeid=".$event_type;
                 }else{
                   $cond.="  and event.event_typeid!='2' ";
                 }
                 if($qus_ids!='')
                 $cond.= " and ans.questionid in (".$qus_ids.")";
                 if($start_date!=''&& $end_date!=''){
                     $cond.= " and event.survey_date between '".$start_date."' and '".$end_date."' ";
                 }
                 $rs = $this->db->select()
                                 ->from(array('ans'=>'survey_event_answers'),
                                 array('ans.questionid', 'location_score'=>'SUM(response_options-1)/(10*COUNT(response_options))*100','event_count'=>'COUNT(event.eventid)' ))
                                ->joinleft(array('ques' => 'survey_event_question_langs'),
                                      'ans.questionid=ques.questionid',
                                           array())
                                ->joinleft(array('event' => 'survey_events'),
                                      'ans.eventid=event.eventid',
                                           array())
                                ->where($cond)
                                ->group('questionid')
                                ->order('location_score ASC'); //echo $rs; exit;
                               // ->limit(3); echo $rs;
                 $result= $rs->query()->fetchAll(); 
                 return $result;
               }
                  
         }
       }
    public function getNationComparisindataOld ($dealer_id,$event_type,$start_date,$end_date,$qus_ids){
        if($dealer_id!=''){
        $sql="SELECT marketid FROM dealers WHERE id in (".$dealer_id.")";
        $result=$this->db->fetchAll($sql); 
        $market="";
        foreach($result as $resulData){
            if($market==''){
            $market="'".$resulData['marketid']."'";
            }
            else{
                $market.=",'".$resulData['marketid']."'";
            }
        }
        if($market!=''){
            $sql_nation="SELECT nation_id FROM nation_markets WHERE market_structid in (".$market.")"; //echo $sql_nation; exit;
            $result_nation=$this->db->fetchAll($sql_nation); 
            if($result_nation){
                $nation_id=$result_nation[0]['nation_id'];
                if($nation_id!=''){
                    $sql="SELECT market_structid FROM nation_markets WHERE nation_id='".$nation_id."'";
                    $result=$this->db->fetchAll($sql);
                    $market_struct_id='';
                    foreach($result as $market_structidVal){
                        if($market_struct_id=='')$market_struct_id ="'".$market_structidVal['market_structid']."'";
                        else $market_struct_id =$market_struct_id.','."'".$market_structidVal['market_structid']."'";
                    }
                    $sql="select id from dealers where marketid in (".$market_struct_id.")";
                    $result=$this->db->fetchAll($sql);
                    $dealer_id="";
                    foreach($result as $rsltData){
                        if($dealer_id==''){
                            $dealer_id="'".$rsltData['id']."'";
                        }else{
                            $dealer_id .=",'".$rsltData['id']."'";
                        }
                    }
                    if($dealer_id!=''){
                        $cond= " event.dealer_id IN (".$dealer_id.") and event_status='Closed' ";
                    } else{$cond=" 1 ";}

                    if($event_type!='' && $event_type!=0){
                        $cond.= " and event.event_typeid=".$event_type;
                    }else{
                      $cond.="  and event.event_typeid!='2' ";
                    }
                    if($qus_ids!='')
                    $cond.= " and ans.questionid in (".$qus_ids.")";
                    if($start_date!=''&& $end_date!=''){
                        $cond.= " and event.survey_date between '".$start_date."' and '".$end_date."' ";
                    }
                    $rs = $this->db->select()
                                    ->from(array('ans'=>'survey_event_answers'),
                                    array('ans.questionid', 'location_score'=>'SUM(response_options-1)/(10*COUNT(response_options))*100','event_count'=>'COUNT(event.eventid)' ))
                                   ->joinleft(array('ques' => 'survey_event_question_langs'),
                                         'ans.questionid=ques.questionid',
                                              array())
                                   ->joinleft(array('event' => 'survey_events'),
                                         'ans.eventid=event.eventid',
                                              array())
                                   ->where($cond)
                                   ->group('questionid')
                                   ->order('location_score ASC'); //echo $rs; exit;
                                  // ->limit(3); echo $rs;
                    $result= $rs->query()->fetchAll(); 
                    return $result;
                  }
                }
            }
         }
       }
       function getDealerComparisondata($dealer_id){
            if($dealer_id!=''){    
                 $rs = $this->db->select()
                          ->from(array('dealers'),
                          array('subsidiaryid'))
                          ->where("id in (".$dealer_id.")");
                  $result= $rs->query()->fetchAll();
                 $subsidiaryid=$result[0]['subsidiaryid'];
                
            }
            $rs = $this->db->select()
                       ->from(array('dealers'),
                         array('id'))
                       ->where("subsidiaryid = ? ",$subsidiaryid);
           $result= $rs->query()->fetchAll(); 
           $dealer_id="";
           foreach($result as $resulData){
                if($dealer_id==''){
                $dealer_id="'".$resulData['id']."'";
                }
                else{
                    $dealer_id.=",'".$resulData['id']."'";
                }
            }
            $cond= " event.dealer_id IN (".$dealer_id.") and event.event_typeid!=2";
            $rs = $this->db->select()
                   ->from(array('ans'=>'survey_event_answers'),
                    array('ans.questionid','ques.question', 'location_score'=>'SUM(response_options-1)/(10*COUNT(response_options))*100' ))
                   ->joinleft(array('ques' => 'survey_event_question_langs'),
                         'ans.questionid=ques.questionid',
                              array())
                   ->joinleft(array('event' => 'survey_events'),
                         'ans.eventid=event.eventid',
                              array())
                   ->where($cond)
                   ->group('questionid')
                   ->order('score ASC')
                   ->limit(3); 
            $result= $rs->query()->fetchAll(); 
            return $result;
       }
       public function getTopdealerRank ($dealer_id,$month,$year,$event_type=""){ 
            $cond="";
             if($dealer_id!=''){
                $cond .=" and dealer_id in (".$dealer_id.")";
            }
            if($month!='' && $year!=''){
                 $start_date= $year.'-'.$month.'-01';
                 $end_date=$year.'-'.$month.'-31';
                 $cond.=" and survey_date between '".$start_date."' and '".$end_date."'";
             }
            if($event_type!='' && $event_type!=0){
                $cond.=" and event_typeid='".$event_type."'";
            }
            if($dealer_id!=''){
                $sql="SELECT @rn:=@rn+1 AS rank, dealer_id,  dealer_rank
                   FROM (SELECT dealer_id, round((SUM(CASE WHEN satisfaction_percent between 90 and 100
                                    THEN 1 ELSE 0 END)/COUNT(eventid))*100 - 
                                    (SUM(CASE WHEN satisfaction_percent between 10 and 60
                                    THEN 1 ELSE 0 END)/COUNT(eventid))*100,2) 
                   AS dealer_rank FROM survey_events event LEFT JOIN dealers deal
                   ON event.dealer_id=deal.id where event_status='Closed' and is_deleted='0' and event.event_typeid!=2 $cond ORDER BY dealer_rank DESC ) t1, 
                   (SELECT @rn:=0) t2";  //echo  $sql.'<br>'; //exit;
                   $result=$this->db->fetchAll($sql); 
                   return  $result;
           }
                
       }   
       
       public function getQuestionId(){
           $rs = $this->db->select()
                ->from(array('config'),
                array('config_val'))
                ->where("config_var = ?", "question_ID"); 
        $result= $rs->query()->fetchAll();
        return $result[0]['config_val'];
       }
       
       
       public function getQuestionNationRating($dealer_id,$start_date,$end_date,$event_type,$qus_id,$limit="" ){
         $limit_cond="";
         $marketid="";
         $market_structid="";
         $dealer_ids="";
        // if($limit!=''){ $limit_cond=" limit ".$limit;}
         if($limit!=''){ $limit=$limit;}else{$limit=3;}
         
         $rs = $this->db->select()
                ->from(array('dealers'),
                array('marketid'))
                ->where("id in (" .$dealer_id.")");
        $result= $rs->query()->fetchAll();
        if($result){
            $marketid= $result[0]['marketid']; 
        }
        if($marketid!=''){
                $rs = $this->db->select()
                    ->from(array('nation_markets'),
                    array('nation_id'))
                    ->where("market_structid =?" ,$marketid); 
                $result_nation= $rs->query()->fetchAll();
                if($result_nation){
                    $nation_id=$result_nation[0]['nation_id'];
                    if($nation_id!=''){
                       $rs = $this->db->select()
                        ->from(array('nation_markets'),
                        array('market_structid'))
                        ->where("nation_id = ?", $nation_id);
                        $result= $rs->query()->fetchAll();

                    }
                }
                $market_struct_id='';
                
                foreach($result as $market_structidVal){
                    if(isset($market_structidVal['market_structid'])){
                    if($market_struct_id=='')$market_struct_id ="'".$market_structidVal['market_structid']."'";
                    else $market_struct_id =$market_struct_id.','."'".$market_structidVal['market_structid']."'";
                    }
                
                }
                if($market_struct_id){
                    $rs = $this->db->select()
                        ->from(array('dealers'),
                        array('id'))
                        ->where("marketid in(" .$market_struct_id.")");
                    $result_dealer= $rs->query()->fetchAll();
                   
                    foreach($result_dealer as $dealer){
                        if($dealer_ids=='')$dealer_ids ="'".$dealer['id']."'";
                        else $dealer_ids =$dealer_ids.','."'".$dealer['id']."'";
                    }
                }

            }
            
            if($dealer_ids!=''){
                $cond="dealer_id in (" .$dealer_ids.")";
            }else{$cond="1";}
            $rs = $this->db->select()
                ->from(array('ans'=>'survey_event_answers'),
                array('event.dealer_id','ans.questionid','ques.question','round((SUM(response_options-1)/(10*COUNT(answerid)))*100,2) AS score' ))
               ->joinleft(array('ques' => 'survey_event_question_langs'),'ans.questionid=ques.questionid',
                          array())
               ->joinleft(array('event' => 'survey_events'),'ans.eventid=event.eventid',
                          array())
                ->where($cond)
                ->where("date(answer_date)>= ?", $start_date)
                ->where("date(answer_date) <= ?", $end_date)
                ->where("ans.questionid = ?", $qus_id)
                ->where("event_status = ?", "Closed")
                
               ->group('ans.questionid')
               ->limit($limit); //echo $rs;
            $result= $rs->query()->fetchAll();
            return $result;
        //}  
    }
    
    
    public function monthWiseCustomerAlert($month,$year,$event_type,$dealer){
        isset($event_type)?$cond="  event_typeid='$event_type'":"";
        isset($dealer)?$cond.=" and dealer_id='$dealer'":"";
            $start_date=$year.'-'.$month.'-01';
            $end_date=$year.'-'.$month.'-31';
            $rs = $this->db->select()
                ->from(array('survey_events'),
                array('customer_cnt'=>'count(eventid)'))
                //->where("code_red_status = ?", "Open")
                ->where("code_status = ?", "Red")
                ->where("event_status = ?", "Closed")
                ->where("event_typeid != ?", "2")
                ->where("date(survey_date) >= ?", $start_date)
                ->where("date(survey_date) <= ?", $end_date)
                ->where($cond); 
            $result= $rs->query()->fetchAll();
            return $result;

     }
    
     public function monthWisePerformanceAlert($month,$year,$event_type,$dealer){
         isset($event_type)?$cond="  event_typeid='$event_type'":"";
         isset($dealer)?$cond .=" and dealer_id='$dealer'":"";
         $start_date=$year.'-'.$month.'-01';
         $end_date=$year.'-'.$month.'-31';
         $rs = $this->db->select()
                  ->from(array('survey_events'),
                  array('performanc_cnt'=>'count(eventid)'))
                  ->where("is_performance_alert = ?", "1")
                  ->where("event_status = ?", "Closed")
                  ->where("event_typeid != ?", "2") 
                  ->where("date(survey_date) >= ?", $start_date)
                  ->where("date(survey_date) <= ?", $end_date)
                  ->where($cond);
          $result= $rs->query()->fetchAll();
          return $result;
      
     }
     
      public function getMonthNumber($monthName){
        $monthArray = array("01"=>"January","02"=>"February", "03"=>"March", 
              "04"=>"April","05"=>"May", "06"=>"Jun","07"=>"July",
              "08"=>"August","09"=>"September","10"=>"October","11"=>"November","12"=>"December");
        foreach($monthArray as $key=>$month){
            if($month==$monthName){
                return $key;
            }
        }
        
    }
    
    public function getQuestionLang($qus_id){
        $rs = $this->db->select()
                ->from(array('lang'=>'survey_event_question_langs'),
                array('question','parent_id'=>'(CASE WHEN parent_id>0 THEN parent_id ELSE 0 END)'))
                ->joinleft(array('ques' => 'survey_event_questions'),
                     'lang.questionid=ques.questionid',
                          array())
                ->where("lang.questionid = ?", $qus_id)
                ->where("langid = ?","1")
                ->where("question_type = ?","Q"); 
        $result= $rs->query()->fetchAll();
        if(isset($result[0]['parent_id']) && $result[0]['parent_id']>0 ){
                    $rs = $this->db->select()
                       ->from(array('survey_event_question_langs'),
                       array('question'))
                       ->where("questionid = ?", $result[0]['parent_id'])
                       ->where("langid = ?","1");
                    $resultQL= $rs->query()->fetchAll();
                    $result[0]['parent_question'] =$resultQL[0]['question'];
             }
             if(!empty($result[0])){
                 return $result[0];
            }
    }
    
     public function getConfigQues($val,$event_type){
        $rs = $this->db->select()
                ->from(array('config'),
                array('config_val'))
                ->where("config_var = ?", $val);
        $result= $rs->query()->fetchAll();
        if(isset($result[0]['config_val'])){
            return $result[0]['config_val'];
        }
    }
    
    public function getRollingMonth($selected_date){
        $sql="SELECT concat(YEAR('".$selected_date."'-INTERVAL 12 MONTH),'-',
                  MONTH('".$selected_date."' - INTERVAL 11 MONTH),'-01')AS start_date";// echo $sql;exit;
        $result=$this->db->fetchAll($sql);
        $start_date= $result[0]['start_date'];
        return $start_date; 
        
    }
    
//    public function getDealerName(){
//        $rs = $this->db->select()
//                ->from(array('dealers'),
//                array('question'))
//                ->where("questionid = ?", $qus_id);
//        $result= $rs->query()->fetchAll();
//        return $result[0];
//    }
//    
    public function getUserResponsebasedQuestions($eventid,$langid=1)
    {   $rs = $this->db->select()
                ->from(array('seq' => 'survey_event_questions'), '*')
               ->joinLeft(array('seql'=>'survey_event_question_langs'),'seq.questionid = seql.questionid and seql.langid="'.$langid.'"')
               ->join(array('sea' => 'survey_event_answers'), 'sea.questionid = seq.questionid')
                ->where("eventid = ?", $eventid)  ;
        $result= $rs->query()->fetchAll();
        return $result;
    }
    
    function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
            $sort_col = array();
            if(!empty($arr)){
                foreach ($arr as $key=> $row) {
                    $sort_col[$key] = $row[$col];
                }
                array_multisort($sort_col, $dir, $arr);
            }
            
    }
    
    public function getQuesLang($qus_id){
        $rs = $this->db->select()
                ->from(array('survey_event_question_langs'),
                array('question'))
                ->where("questionid = ?", $qus_id); 
        $result= $rs->query()->fetchAll();
        return  $result[0]['question'];
    }
    
    public function getQusCate($config_qus_arry,$qus_id){
        foreach($config_qus_arry as $key=>$value){ 
            foreach($value as $key1=>$val){ 
                if(in_array($qus_id,$val)){ 
                    $category= $key1;
                }
            }
            
       } return $category;
    }
    
    Public function getImprovementAreas($dealers,$params,$allquestionids,$NonsliderQuestionid,
            $MultipleYesOptionQuestionid,$langid,$start_date,$end_date,$model='',$multiplier=1)
    {
       
        if(!empty($dealers) && is_array($dealers)){
            $dealer_str=implode(',', $dealers);
            
        }else $dealer_str=''; 
        $this->_objConfig = Zend_Registry::get('config');
        $db_name=$this->_objConfig['resources']['db']['params']['dbname'];
                     
        if(isset($params['event_type']))$event_typeid=$params['event_type'];else $event_typeid="";      
        // call usp_improvementareas('triumphstage','1','2013-08-31', '2014-07-31','665','48,49,50,51,52,53,54,55,56,57,58,59,60,12,13,14,15,16','12,13,14,15,16','12')
      
        $in_params= array($db_name,$event_typeid,$start_date,$end_date,$dealer_str,$allquestionids,$NonsliderQuestionid,$MultipleYesOptionQuestionid,$langid,$model,$multiplier);
      // print_R($in_params);//die;
        $result= $this->_spObj->getSpData("usp_improvementareas", $in_params,False);
        //print_r($result);die;
        return $result;
        
    }
    
    public function getImprovementAreasForExport( $dealers, $params, $allquestionids,
        $NonsliderQuestionid, $MultipleYesOptionQuestionid, $langid, $start_date,
        $end_date, $model = '', $multiplier = 1, $groupBy = '') {

        if ( !empty( $dealers ) 
             && is_array( $dealers ) ) {
            $dealer_str = implode( ',', $dealers );            
        }
        else {
            $dealer_str = ''; 
        }
        
        $this->_objConfig = Zend_Registry::get('config');
        $db_name = $this->_objConfig['resources']['db']['params']['dbname'];
                     
        if( isset( $params['event_type'] ) ) {
            $event_typeid = $params['event_type'];
        }
        else {
            $event_typeid = '';
        }

        $in_params = array(
            $db_name,
            $event_typeid,
            $start_date,
            $end_date,
            $dealer_str,
            $allquestionids,
            $NonsliderQuestionid,
            $MultipleYesOptionQuestionid,
            $langid,
            $model,
            $multiplier,
            $groupBy
        );
//        print_r($in_params);die;
        $result = $this->_spObj->getSpData("usp_improvementareas_export", $in_params, FALSE);
        return $result;
    }

    Public function getTopImprovementAreas($dealers,$params,$allquestionids,$NonsliderQuestionid,$MultipleYesOptionQuestionid,$langid,$start_date,$end_date,$multiplier=1)
    {
       
        if(!empty($dealers) && is_array($dealers)){
            $dealer_str=implode(',', $dealers);
            
        }else $dealer_str=''; 
        $this->_objConfig = Zend_Registry::get('config');
        $db_name=$this->_objConfig['resources']['db']['params']['dbname'];
                     
        if(isset($params['event_type']))$event_typeid=$params['event_type'];else $event_typeid="";      
        // call usp_improvementareas('triumphstage','1','2013-08-31', '2014-07-31','665','48,49,50,51,52,53,54,55,56,57,58,59,60,12,13,14,15,16','12,13,14,15,16','12')
      
        $in_params= array($db_name,$event_typeid,$start_date,$end_date,$dealer_str,$allquestionids,$NonsliderQuestionid,$MultipleYesOptionQuestionid,$langid,$multiplier);
       //print_R($in_params);die;
        $result= $this->_spObj->getSpData("usp_topimprovementareas", $in_params,false);
        //print_r($result);die;
        return $result;
        
    }

    public function mdrcount($event_typeid,$start_date,$end_date,$arrdealerids,$OverallOpinionQuestionid,$arrMonths)
    {
       
        if(!empty($arrdealerids) && is_array($arrdealerids)){
            $dealer_str=implode(',', $arrdealerids);            
        }
        else $dealer_str=$arrdealerids;
        $this->_objConfig = Zend_Registry::get('config');
        $db_name=$this->_objConfig['resources']['db']['params']['dbname'];
        $in_params= array($db_name,$event_typeid,$start_date,$end_date,$dealer_str,$OverallOpinionQuestionid);
        $result= $this->_spObj->getSpData("usp_mdrreportcount", $in_params,False);
        //print_r($result);die;
        return $result;
    }
    
    public function gettopdealerscore($event_typeid,$start_date,$end_date,$arrdealerids,$OverallOpinionQuestionid,$type="")
    {
       
        if(!empty($arrdealerids) && is_array($arrdealerids)){
            $dealer_str=implode(',', $arrdealerids);            
        }
        else $dealer_str=$arrdealerids;
        $this->_objConfig = Zend_Registry::get('config');
        $db_name=$this->_objConfig['resources']['db']['params']['dbname'];
        switch($type)
        {
          case "topQuestion":
              $in_params= array($db_name,$event_typeid,$start_date,$end_date,$dealer_str,$OverallOpinionQuestionid);
                $result= $this->_spObj->getSpData("usp_topdealersquestionscore", $in_params,false);
                break;
          default:
                $in_params= array($db_name,$event_typeid,$start_date,$end_date,$dealer_str);
                $result= $this->_spObj->getSpData("usp_topdealerscore", $in_params,false);
                break;
        }
            //print_r($result);die;
        return $result;
    }
    
    public function getImprovementAreasInfo($objUserinfo,$event_type,$dealers,$get,
                    $qus_id_string,$NonsliderQuestionid,$MultipleYesOptionQuestionid,
                    $langid,$start_date,$end_date,$retType='',$retlimit=3,$ReqType="",$multiplier=1)
    {
        $rptObj = new Damco_Core_ReportingFunctions();
           
        $objConfig = new Survey_Model_Config();
            if ($event_type>0){
                  $retarr = $storeCompData = $final_arr=array();
                  $all_location_score = $this->getImprovementAreas($dealers,$get,$qus_id_string,$NonsliderQuestionid,$MultipleYesOptionQuestionid,$langid,$start_date,$end_date,'',$multiplier);//
                 //echo "LIne: 950";print_r($all_location_score);//die;
                   foreach($all_location_score as $ar)
                   {
                       $final_arr[$ar["questionid"]]=array("question"=>$ar["question"],
                                                    "local_avg_score"=>$ar["avg_score"],
                                                    "local_event_count"=>$ar["tot_event"],
                                                    "rpt_specific_avg_score"=>$ar["rpt_specific_avg_score"],
                                                    );
                   }
                   ///////////////////////////////
                   if($ReqType == "onlyDealerData")
                   {
                       return $final_arr;
                   }
                   
                  //to get comparison dealers ids
                   if($objUserinfo->role_id == 4)
                   {
                     // $comparisonDealers = $objUserinfo->dealer_id;
                      $comparisonDealers = $rptObj->getComparisionDealers($objUserinfo->dealer_id,$marketid,$sales_regionid,$branchid); 
                      //print_R($comparisonDealers);die;
                      
                   }
                   else
                   {
                       //print_r($get);//die;
                       $comparisonDealers = $dealerid =  $marketid = $sales_regionid = $branchid = "";
                       if (isset($get['dealer']) && !empty( $get['dealer'] ) ) 
                       {
                           $dealerid = $get['dealer'];
                           $marketid = $sales_regionid = $branchid = "";
                           $comparisonDealers = $rptObj->getComparisionDealers($dealerid,$marketid,$sales_regionid,$branchid); 
                       }            
                       elseif(isset($get['sales_region']) && !empty( $get['sales_region']))
                       {
                           $dealerid = "";
                           $marketid = $branchid = "";
                           $sales_regionid = $get['sales_region'];
                           $comparisonDealers = $rptObj->getComparisionDealers($dealerid,$marketid,$sales_regionid,$branchid); 
                       }
                       elseif(isset($get['market']) && !empty( $get['market'] ))
                       {
                           $dealerid = "";
                           $marketid =  $get['market'] ;// 8/19/14 2:04 PM
                           $sales_regionid = "";
                           $branchid = $get['branch'];
                           
                           $comparisonDealers = $rptObj->getComparisionDealers($dealerid,$marketid,$sales_regionid,$branchid); 
                       }
                       elseif(isset($get['branch']) && !empty( $get['branch'] ))
                       {
                           $marketid = $dealerid = "";
                           $sales_regionid = "";
                           $branchid = $get['branch'];
                           $comparisonDealers='';
                       }  
                   }
                   $comparisonDealers = !is_array($comparisonDealers) ? explode(",",$comparisonDealers) : $comparisonDealers ; 
                  
                 
                  $globalLocation_score = $this->getImprovementAreas($comparisonDealers,$get,$qus_id_string,
                          $NonsliderQuestionid,$MultipleYesOptionQuestionid,$langid,$start_date,$end_date,"",$multiplier);
                 /* echo "Final arr";print_R($final_arr); */
                 
                 // echo "Global  arr";print_R($globalLocation_score);die;
                 
                  foreach($globalLocation_score as $ar)
                  {
                      if(count($final_arr)>0 && array_key_exists($ar["questionid"], $final_arr))
                      {
                          $final_arr[$ar["questionid"]]["global_avg_score"]=$ar["avg_score"];
                          $final_arr[$ar["questionid"]]["global_event_count"]=$ar["tot_event"];
                          $score_diff = $final_arr[$ar["questionid"]]["local_avg_score"] - $ar["avg_score"];                         
                      }
                      else
                      {
                          $final_arr[$ar["questionid"]]=array("question"=>$ar["question"],"local_avg_score"=>0,
                                                            "global_avg_score"=>$ar["avg_score"], "local_event_count"=>0,
                                                            "global_event_count"=>$ar["tot_event"],
                                                             );
                          $score_diff =(0-$ar["avg_score"]);
                      }
                        $final_arr[$ar["questionid"]]["score_diff"]= $score_diff;
                        $storeCompData[$ar["questionid"]]= ($score_diff); //chnaged as per QA 9/24/14 4:34 PM
                      
                  }
                 // echo "Store arr";print_R($storeCompData);//die;
                  if(count($storeCompData)>0)
                  {
                        
                        asort($storeCompData);
                     //   print_R($storeCompData);die;
      //                  print_r($all_location_score);
      //                  print_r($globalLocation_score);
                        $cnt=0;
                        foreach($storeCompData as $k=>$q)
                        {    
                           if($retlimit == 3)
                           {
                                if($cnt < 3 && $final_arr[$k]["local_event_count"] > 0 )
                                {
                                  if($retType == "Question")
                                  {
                                      $retarr[]=$final_arr[$k]["question"];
                                  }
                                  else {
                                      $retarr[]=$final_arr[$k];
                                  }

                                  $cnt++;
                                }
                           }
                           else
                           {
                                  if($retType == "Question")
                                  {
                                      $retarr[]=$final_arr[$k]["question"];
                                  }
                                  else {
                                      $retarr[]=$final_arr[$k];
                                  }

                           }
                        }
                  }
                  
             }
            // print_R($retarr);die;
             return $retarr;
    }
    
    public function getTopImprovementAreasInfo($event_type,$dealers,$get,
                    $qus_id_string,$NonsliderQuestionid,$MultipleYesOptionQuestionid,
                    $langid,$start_date,$end_date,$retType='',$retlimit=3,$ReqType="",$multiplier=1)
    {
        $rptObj = new Damco_Core_ReportingFunctions();
           
        $objConfig = new Survey_Model_Config();
            if ($event_type>0){
               
                  $final_arr=array();
                  $all_location_score = $this->getTopImprovementAreas($dealers,$get,$qus_id_string,$NonsliderQuestionid,$MultipleYesOptionQuestionid,$langid,$start_date,$end_date,$multiplier);//
                  //print_r($all_location_score);die;
                   foreach($all_location_score as $ar)
                   {
                       //$k =  strlen($ar["monthnum"])<2 ? "0".$ar["monthnum"] : $ar["monthnum"];
                        $final_arr[$ar["questionid"]]=array("question"=>$ar["question"],
                                                            "local_avg_score"=>$ar["avg_score"],
                                                            "rpt_specific_avg_score"=>$ar["rpt_specific_avg_score"] );
                       //$final_arr[$ar["questionid"]][$ar["yearnum"].$k]=array("question"=>$ar["question"],"dealerid"=>$ar["dealer_id"],"avg_score"=>$ar["avg_score"]);
                   }
                   ///////////////////////////////
                   if($ReqType == "onlyDealerData")
                   {
                       return $final_arr;
                   }
                   
            }   
            // print_R($retarr);die;
             return $retarr;
        }
        
        public function getEsrData($event_type,$dealers,$start_date,$end_date,$period,$groupby='')
        {
             if(!empty($dealers) && is_array($dealers)){
                $dealer_str=implode(',', $dealers);
            }else $dealer_str=$dealers;
            $this->_objConfig = Zend_Registry::get('config');
            $db_name=$this->_objConfig['resources']['db']['params']['dbname'];
            $in_param=array($db_name, $event_type, $start_date, $end_date, $dealer_str,$groupby);
           
            $result = $this->_spObj->getSpData('usp_esrreport', $in_param, false);
            return $result;
        }
        
        public function createdataforcusrsor($arrMonths)
        {
            //for cursor BOC 10/14/14 9:57 AM
            //to get date range option
            $strDate = "";
            //print_r($arrMonths);die;
            foreach($arrMonths as $mk=>$month_nm)
            {
                $monthno = (int) substr($mk,4,2);
                if($monthno == 12)
                {                
                  $yearMonth = (substr($mk,0,4)+1).'-01-01';
                }
                else
                {
                   $yearMonth = substr($mk,0,4).'-'.($monthno+1).'-1';  
                }
                $mykey = $rolling_start_date =  Zend_Controller_Action_HelperBroker::getStaticHelper('GetReportdate')->GetReportdate($yearMonth, "rolling12_first_date")." 00:00:00";

                $rolling_end_date =  Zend_Controller_Action_HelperBroker::getStaticHelper('GetReportdate')->GetReportdate($yearMonth, "enddate")." 23:59:59";
                $strDate .= $rolling_start_date . "|" . $rolling_end_date."~";
                $mykey = trim(str_replace(array("-01 00:00:00","-"), "", $mykey));
                $retdata[$mykey]=$rolling_start_date . "|" . $rolling_end_date."~";
            }

            $strDate = substr(trim($strDate), 0, -1);  
            $in_params   = array($strDate,'~','tblindex');
            $resultSet   = $this->_spObj->getSpData("SplitString", $in_params,false); 
            //EOC By Dipa
            //for cursor EOC 10/14/14 9:57 AM
           return $retdata;
        }
       
 }
 ?>
