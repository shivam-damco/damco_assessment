<?php

class Damco_View_Helper_CreatePdfInfo  extends Zend_View_Helper_Abstract {

    function __construct() {
        $this->translate = Zend_Registry::get('Zend_Translate');       
    }
    
    function CreatePdfFilter($get) {
       
       $arrGetInfoToShow =  $this->createinfo($get);
       $str ="<table class=\"table table-condensed\" style='margin-bottom:0px !important;'><tbody><tr>";
       $tot=$tdcnt=1;
       $totArrayElem = count($arrGetInfoToShow);
       foreach($arrGetInfoToShow as $k=>$v)
       {           
           $str .= "<td width=\"18%\">". $k . "</td><td>" . $v. "</td>";          
           $tdcnt++;
           if($tdcnt == 3)
           {
               $str .= "</tr>"; 
//               if($totArrayElem != $tdcnt)
//               {
                   // $str .= "";
                    $tdcnt=1;
               //}
                 $tot++;    if($tot != $totArrayElem)
                    {$str .= "<tr>"; }
           }
          
       }
       if($tdcnt < 3)
       {
           if($tot%3 !=0)
           {
             $str .= "<td colspan='".(5-$tdcnt)."'></td>"; 
           }
           if(!empty($tdcnt)){
               $str .= "</tr>"; 
           }
       }
       $str .="</tbody>
    </table>";
       
       return $str;
        
        
    }   
    
    function CreatePdfFooter($get)
    { 
       $arrGetInfoToShow = $this->createinfo($get);
      // print_r($arrGetInfoToShow);die;
       $str="";
       foreach($arrGetInfoToShow as $k=>$v)
       {
           $str .= $k . $v. " ";
       }
       return $str;
    }
    
   Public function createinfo($get)
    {
        $this->_objConfig = Zend_Registry::get('config');
        $this->_config = new Survey_Model_Config();
        $this->_eventModelCompanyObject = new Event_Model_CompanyStructure();
        $this->dealerObj = new Dealer_Model_Dealers();
        $this->_eventtypesModelObject = new Event_Model_EventTypes();
        $this->_questionModelObject = new assessment_Model_Questions();
        $filterOptions= array();
        
        foreach($get as $k=>$v)
        {
            if(!empty($v))
            {
                switch($k)
                {
                   case "event_type" : 
                       $eventtype= $this->_eventtypesModelObject->getWhere('event_type',array('event_typeid' => $v));
                       $filterOptions[$this->translate->_("Choose Event:")] = $this->translate->_($eventtype[0]['event_type']);
                   break;
                   case "nation" : 
                        $nationname= $this->_eventModelCompanyObject->getnationlist('','one',"nationid='".trim($v)."'");                  
                        $filterOptions[$this->translate->_("Nation")] = $this->translate->_($nationname['nation_name']);
                   break;
                   case "questionid" :
                       $allquestiontext = $this->_questionModelObject->getQuestionDetails(array("seq.questionid"=>$v));
                       $filterOptions[$this->translate->_("Question:")] =  $allquestiontext[0]["question"];
                       //$filterOptions["Question:"] =  $allquestiontext[0]["question"];
                       break;
                   case "model" :
                       $filterOptions[$this->translate->_("Select Model:")] =  $v; 
                       //$filterOptions["Select Model:"] =  $v; 
                       break;
                   case "branch" :
                       if(!empty($v))
                       {
                            $branchname=$this->_eventModelCompanyObject->getWhere('struct_name',array('structid' =>$v,'hierarchy_level_id'=>'2'));
                       }

                       $filterOptions[$this->translate->_('Select Branch:')] =  $branchname[0]['struct_name']; 
                       //$filterOptions['Select Branch:'] =  $branchname[0]['struct_name']; 
                       break;
                   case "market" :
                       if(!empty($v)){
                            $countryname=$this->_eventModelCompanyObject->getWhere('struct_name',array('structid' =>$v,'hierarchy_level_id'=>'3'));
                       }
                       $filterOptions[$this->translate->_("Select Country:")] = $countryname[0]['struct_name']; break;
                       //$filterOptions["Select Country:"] = $countryname[0]['struct_name']; break;
                   case "sales_region" :
                       if(!empty($v)){
                            $salesregion=$this->_eventModelCompanyObject->getWhere('struct_name',array('structid' =>$v,'hierarchy_level_id'=>'4'));
                        }
                       $filterOptions[$this->translate->_("Select Area Sales Manager:")] =  $salesregion[0]['struct_name']; break;
                       //$filterOptions["Select Area Sales Manager:"] =  $salesregion[0]['struct_name']; break;
                   case "dealer" :
                       if(!empty($v)){
                            $dealername=$this->dealerObj->getWhere('dealer_name',array('id' => $v));
                        }                                   
                       $filterOptions[$this->translate->_("Select Dealer")] = $dealername[0]['dealer_name'] ; break;
                       //$filterOptions["Select Dealer"] = $dealername[0]['dealer_name'] ; break;
                   case "startDate" :
                       $filterOptions[$this->translate->_("From:")] = $v ; break;
                       //$filterOptions["From:"] = $v ; break;
                   case "endDate" :
                       $filterOptions[$this->translate->_("To:")] =  $v; break;
                       //$filterOptions["To:"] =  $v; break;
                   default: //do nothing 
                       break;
                }
            }
        }
        //print_r($filterOptions);die;
        return $filterOptions;
    }
    
    function CreatePdfInfo($get, $usedFor="") {
        switch($usedFor)
        {
            case "pdf_footer" :
                return $this->CreatePdfFooter($get);
            break;
            case "pdf_filter" :
                return $this->CreatePdfFilter($get);
                break;
            
        }
    }

}


