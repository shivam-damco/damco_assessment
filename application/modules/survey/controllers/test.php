<?php
/************************************************************************
*Filename            : IndexController.php
*File Description    : Controller for user
*Database Tables     : users, tblevteventtypes
*Author              : Richa Sharma
*Date of Creation    : August 4 2008
*Comments            : Module is 'User'
*****/


class User_IndexController extends Zend_Controller_Action
{

	private $_objRegistry;
	private $_objConfig;
	private $_objAuth;
	private $_objUserDetails;
	private $_showAllModule;

	function init()
	{

		$this->_objRegistry      = Zend_Registry::getInstance();
		$this->_objAuth          = Zend_Auth::getInstance();
		$this->_objUserDetails   = $this->_objAuth->getStorage()->read($app);
		$this->_objDb            = $this->_objRegistry->get('objDb');

		$this->Username         = $this->_objUserDetails->user_name;
		$this->UserId           = $this->_objUserDetails->userid;
		$this->_objConfig		= $this->_objRegistry->get("config");
		$this->view->imagesPath = $this->_objConfig->paths->web->layout_images;
		$this->view->cssPath    = $this->_objConfig->paths->web->cssPath;
		$this->view->appCssPath = $this->_objConfig->paths->web->appCssPath;

		$this->loggedUserUserType = $this->UserType  =  $this->_objUserDetails->user_type; // PO:APDGR430
		///PO:APDGR430
		$this->view->showAllModule = $this->_showAllModule = 'N';
		
		if($this->UserType == "user" && ($this->_objUserDetails->is_superadmin == 1) || $this->_objUserDetails->user_manager == "Yes")
		{
			$this->view->showAllModule = $this->_showAllModule='Y';
		}
		///////
		if (trim($this->UserType) == 'client' || trim($this->UserType) == 'supplier' ||trim($this->UserType) == 'clientuser') {
			$this->clientId     =  $_SESSION['client'];//$this->objUserDetails->ParentUserId;
			$this->UserType = 'clientuser';
		} else {
			if ($_SESSION['client'] !='' && $_SESSION['client'] !=0) {
				$this->clientId = $_SESSION['client'];
				$this->UserType = 'clientuser';
			} else {
				$this->clientId =0;
				$this->UserType = 'user';
			}
		}
		$this->view->setUserType    = $this->UserType;
		$strModName = strtoupper($this->getRequest()->getModuleName());
		clsAcl::SetAcl($strModName,$_SESSION['Default']['ActionPriv_info'][$strModName]);
		$this->view->module     = $this->getRequest()->getModuleName();
		$this->strModule        = strtoupper($this->getRequest()->getModuleName());
		
	}

	/**********************************************
	*Function : index Action controls all listing of users.
	*Tables Refered/Modified : usrusers.
	*Author: Richa Sharma
	*/
	function indexAction() {
		//print_R($this->_objUserDetails);
		$strModName = strtoupper($this->getRequest()->getModuleName());
		$this->view->actionflag = 'false';
		$this->view->privcounter = 0;

		/*****************
		*Authentication/privileges check.
		*************/

		//clsAcl::IsAllowed('View',$this->strModule);
		if (!clsAcl::IsAllowed('View',$this->strModule)) {
			$this->view->authentication = 'false';
		}


		if (!clsAcl::IsAllowed('Edit',$this->strModule)) {
			$this->view->edit = 'false';
		}
		//21/11/2012
		elseif($this->UserType == "user" && $this->_objUserDetails->user_manager == 'Yes')
		{
			$this->view->edit = 'false';
		}
		else {
				
			$this->view->privcounter++;
			$this->view->actionflag = 'true';
			$this->view->edit = 'true';
		}

		if (!clsAcl::IsAllowed('Delete',$this->strModule)) {
			$this->view->delete = 'false';
		}	
		//21/11/2012
		elseif($this->UserType == "user" && $this->_objUserDetails->user_manager == 'Yes')
		{
			$this->view->delete = 'false';
		}
		else {
			$this->view->privcounter++;
			$this->view->actionflag = 'true';
			$this->view->delete = 'true';
		}

		if (!clsAcl::IsAllowed('Add',$this->strModule)) {
			$this->view->add = 'false';
		}
		else {
			$this->view->add = 'true';
		}


		if (!clsAcl::IsAllowed('Export to Excel',$this->strModule)) {
			$this->view->ExporttoExcel = 'false';
		}	else {
			$this->view->ExporttoExcel = 'true';
		}

		if (!clsAcl::IsAllowed('Assign Privileges',$this->strModule)) {

			$this->view->assignPrivileges = 'false';
		}
		//21/11/2012
		elseif($this->UserType == "user" && $this->_objUserDetails->user_manager == 'Yes')
		{
			$this->view->assignPrivileges = 'false';
		}
		else {
			$this->view->privcounter++;
			$this->view->assignPrivileges = 'true';
		}

		$txtsearch = addslashes($this->_request->getParam('data')) ;
		$msg             = $this->_request->getParam('priv');
		if ($msg != '') {
			$this->view->msg = "The privileges have been ".$msg;
		}
		$this->view->UserId = $this->UserId;

		$this->view->SearchData = $txtsearch;

		$intStrt=$objPager->StartLimit;

		///////////////////////BOC///////////////////////////////

		$objPager = new Lib_Paging('0','','');

		$strPagerQueryString = $objPager->GetQueryString();
		$objPager->SetQueryString($strPagerQueryString);

		//if provide searching keyword
		$txtsearch    = addslashes($this->_request->getParam('data')) ;
		///BOC for paginition

		$in_param = array($txtsearch ,$this->clientId , $this->UserType, $objPager->StartLimit, $objPager->EndLimit);
		$proc = $this->_objDb->getSpCallString('usp_getUsrUsers' , $in_param);

		$Result      = $this->_objDb->processSpCall($proc);
		$resultSet   = $this->_objDb->getSpData();

		$arrTotalrow = $resultSet[1][0]['tot'];
		$alldata     = $resultSet[0];


		//EOC for paginition
		$objPager->SetPagingParams($arrTotalrow);
		$this->view->arrGridData = $alldata;
		//$config =  $this->objRegistry->get("config");

		if(trim($arrTotalrow)>trim($this->_objConfig->paths->page->length))
		{
			$this->view->strlinks=$objPager->GetLinks();
		}
		else
		{
			$this->view->strlinks='';
		}
		$this->view->show = $alldata ;
		$form             = new searchlist();
		$this->view->form = $form;
		//to fill search textbox by the keyword provided for searching
		$this->view->txtData = $txtsearch;
		//to show current page
		$this->view->rec=$this->_request->getParam('_rec',$objPager->EndLimit);
		$this->view->CurrentPage=$this->_request->getParam('_pge');

	}


	/**********************************************
	*Function : Adduser Action controls all operation relating adding a new apd user.
	*Tables Refered/Modified : users
	*Author: Richa Sharma
	*/
	Public function adduserAction()
	{
		if (!clsAcl::IsAllowed('Add',$this->strModule)) {
			$this->view->authentication = 'false';
		}
		else {
			$this->view->authentication = 'true';
		}

		//// 21/11/2012				
		if($this->_objUserDetails->user_type == "user" && $this->_objUserDetails->user_manager == 'Yes')
		{
			$this->view->userTypeDropdown = 'no';
			$_SESSION['client'] = 1;
		}

		$form = new addUser($this->_request->getPost('CnfPassword'));
		$btnLabel = $this->_helper->Translate('Users_Index_LabelButtonSubmit');
		$form->submit->setLabel($btnLabel);
		$this->view->form = $form;
		$form->resetButton->setAttrib('onclick','window.location.href=\''.HTTP_HOST."/".$this->_request->getModuleName().'\'');


		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {

				$in_param = array($form->getValue('user_name'),$this->clientId);
				$proc = $this->_objDb->getSpCallString('usp_usrChkUserdetails' , $in_param);
				$this->_objDb->processSpCall($proc);
				$prv_data = $this->_objDb->getSpData();
				$Countusr = $prv_data['0']['p'];

				if($Countusr == 0)
				{

					$uesr = new Users();
					$row  = $uesr->createRow();

					$row->user_name      = $form->getValue('user_name');
					$row->user_password      = md5($form->getValue('user_password'));
					$row->user_salted_password = $this->better_crypt($form->getValue('user_password'));//TT:770 Anuj
					$row->user_type      = $this->UserType;
					$row->first_name     = $form->getValue('first_name');
					$row->last_name      = $form->getValue('last_name');
					$row->email          = trim(addslashes($form->getValue('email')));
					$row->is_superadmin  = $form->getValue('is_superadmin');
					$row->user_manager  = ($form->getValue('user_manager') == 1) ? "Yes" : "No"; //21/11/2012
					$row->user_status    = $form->getValue('user_status');
					$row->added_date     = date("Y-m-d H:i:s");
					$row->modified_date  = date("Y-m-d H:i:s");
					$row->added_by       = $this->Username;
					$row->modified_by    = $this->Username;

					if ($this->clientId != 0) {
						$row->parent_userid = $this->clientId;
					}

					$row->save();

					$this->_redirect('/'.$this->_request->getModuleName().'/index/assignpriv/UserId/'.$row->userid.'?msg=added');


				}
				else
				{
					$this->view->Error = "Username already exists";
					$form->populate($formData);
				}

			} else {
				$form->populate($formData);
			}
		}
	}


	/**********************************************
	*Function : edit Action controls all operation relating editing an apd user's details.
	*Tables Refered/Modified : users
	*Author: Richa Sharma
	*/
	function editAction() 
	{

		if (!clsAcl::IsAllowed('Edit',$this->strModule)) {
			$this->view->authentication = 'false';
		}
		

		$form = new addUser($this->_request->getParam('CnfPassword'));
		$btnLabel = $this->_helper->Translate('Users_Index_LabelButtonSave');
		$form->submit->setLabel($btnLabel);

		$this->view->form = $form;
		$form->resetButton->setAttrib('onclick','window.location.href=\''.HTTP_HOST."/".$this->_request->getModuleName().'\'');
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			$objUser    = new Users();
			if ($form->isValid($formData))
			{

				$id  = (int)$form->getValue('userid');

				//chk user_name uniqueness

				$row = $this->_objDb->fetchRow("select count(userid) as tot from users where user_name='".$form->getValue('user_name')."' and userid !=".$id);
				
				if($row["tot"] > 0 )
				{

					$row=array();
					$row['userid']        = $form->getValue('userid');
					$row['user_password'] = $form->getValue('user_password');
					$row['first_name']    = $form->getValue('first_name');
					$row['last_name']     = $form->getValue('last_name');
					$row['email']         = $form->getValue('email');
					$row['is_superadmin'] = $form->getValue('is_superadmin');
					$row['user_manager'] = $form->getValue('user_manager'); //21/11/2012
					$row['user_status']   = $form->getValue('user_status');
					$row['modified_by']   = $this->UserId;

					$id = (int)$this->_request->getParam('UserId', 0);
					$userData = $objUser->fetchRow('userid='.$id);
					//print_r($userData);die;
					$this->view->ErrorMsg = "This user name is already exist.";
					$this->view->addedData =$userData;

					$form->populate($row);

				}
				else
				{

					$row = $this->_objDb->fetchRow('select user_password,user_type from users where userid='.$id);

					if($row["user_password"] == $form->getValue('user_password'))
					{
						$Password = $form->getValue('user_password');
					}
					else
					{
						$Password = md5($form->getValue('user_password'));
					}

					
					$editRow =array(
					'userid' => $form->getValue('userid'),
					'user_name' => $form->getValue('user_name'),
					'user_password' => $Password,
					'first_name' => $form->getValue('first_name') ,
					'last_name' => $form->getValue('last_name'),
					'email' => trim(addslashes($form->getValue('email'))) ,
					'user_status' => $form->getValue('user_status'),
					'modified_by' => $this->Username,
					'modified_date' => date("Y-m-d H:i:s")
					);
					/////PO:APDGR430
					if($row["user_type"] == "user")
					{

						$editRow["is_superadmin"] = $form->getValue('is_superadmin');
						$editRow["user_manager"] = ($form->getValue('user_manager') == 1) ? "Yes" : "No"; //21/11/2012
					}

					//
					$this->_objDb->update('users',$editRow,'userid = '.$form->getValue('userid'));

					$this->_redirect('/'.$this->_request->getModuleName().'?msg=updated');
				}

			}
			else
			{
				$row=array();
				$row['userid']        = $form->getValue('userid');
				$row['user_password'] = $form->getValue('user_password');
				$row['first_name']    = $form->getValue('first_name');
				$row['last_name']     = $form->getValue('last_name');
				$row['email']         = $form->getValue('email');
				$row['is_superadmin'] = $form->getValue('is_superadmin');
				$row['user_manager'] = $form->getValue('user_manager'); //21/11/2012
				$row['user_status']   = $form->getValue('user_status');
				$row['modified_by']   = $this->UserId;

				$id = (int)$this->_request->getParam('UserId', 0);
				$userData = $objUser->fetchRow('userid='.$id);
				$this->view->addedData =$userData;

				$form->populate($row);

			}
		} else {

			$id = (int)$this->_request->getParam('UserId', 0);
			if ($id > 0) {
				$users = new Users();
				$user = $users->fetchRow('userid='.$id);
				$this->view->addedData = $user;
				$aa = $user->toArray();
				//// 21/11/2012				
				if($this->_objUserDetails->user_type == "user" && $aa["user_type"] == "user" && $this->_objUserDetails->user_manager == 'Yes')
				{
					$this->view->authentication = 'false';
				}
				///				
				//print_R($aa);
				$aa['CnfPassword'] = $aa['user_password'];
				$aa["user_manager"] = ($aa["user_manager"] == "Yes") ? 1 : 0; //21/11/2012
				$form->populate($aa);
			}

		}
	}

	/**********************************************
	*Function : assignpriv Action controls all operation relating assigning priveledges to user's.
	*Tables Refered/Modified : module_privileges,modules,user_privileges
	*Author: Dipa
	*/
	function assignprivAction() 
	{
		//PO: APDGR430
		if (!clsAcl::IsAllowed('Assign Privileges',$this->strModule)) 
		{
			$this->view->authentication = 'false';
		}
		
		//Getting All Global var for this event
		$UserId = $this->_request->getParam('UserId' , 0);
		//21/11/2012
		$arrUserData	= $this->_objDb->fetchAll("select user_type from users where userid='".$UserId."'");
		
		if($this->_objUserDetails->user_type == "user" && $arrUserData[0]["user_type"] == "user" && $this->_objUserDetails->user_manager == 'Yes')
		{
			$this->view->authentication = 'false';
		}
		//Get Executive report and Report module id from config 
		//$this->ReportModuleId = $this->_objConfig->ModuleMenu->ReportModuleId;
		//$this->ExecutiveModuleId = $this->view->ExecutiveModuleId = $this->_objConfig->ModuleMenu->ExecutiveModuleId;
		
		

		if ($this->_request->getParam('from') == 'cl' || $_GET['from'] == 'cl') {
			$this->view->redirection = 'client';
		} else {
			$this->view->redirection = 'user';
		}


		$arrSelectedAfterSalesRegion 	= array();
		$arrSelectedSalesRegion			= array();
		$arrSelectedDealer 				= array();
		$arrSelectedManager 			= array();


		/**
		 * select user related  data from user_data_source table to show the dropdown or not
		 * 
		 */
		$arrRsUserData	= $this->_objDb->fetchAll("select data_sourceid, userid, source_type,sourceid from user_data_source where userid='".$UserId."'");
		//TT: 770 Anuj 
        $this->view->arrRsUserData = $arrRsUserData;
             
       $arrUserDetail = $this->_objDb->fetchAll("select user_name,user_password,email,user_salted_password from users where userid='" . $UserId . "'");
    
        //Dipa
        $this->view->usernew = (empty($arrRsUserData)) ? "Yes" : "No" ;
       
        //Dipa
        if (empty($arrRsUserData)&& !empty($_POST['rdotype'])) {
		    $toaddress=$arrUserDetail['0']['email'];
            if ($_POST['rdotype'] == 'national') {
               $toaddress=$arrUserDetail['0']['email'];
            } 
            elseif ($_POST['rdotype'] == 'manager') {
                if (!empty($_POST['cboBDManager']) || !empty($_POST['cboSDManager'])) {

                 
                    $bdm_id = implode(",", $_POST['cboBDManager']);
                
					$arrbdmDetail = $this->_objDb->fetchAll("select GROUP_CONCAT(mgr_email)as ccaddress from development_managers where mgrid IN(" . $bdm_id . ")");
                    $ccadress_bdm[]=$arrbdmDetail[0]['ccaddress'];
					
                    $sdm_id = implode(",", $_POST['cboSDManager']);
					
                    $arrsdmDetail = $this->_objDb->fetchAll("select GROUP_CONCAT(mgr_email) as ccaddress from development_managers where mgrid IN(" . $sdm_id . ")");
                    $ccadress_sdm[]=$arrsdmDetail[0]['ccaddress'];
					
					
						
                }
            } else if ($_POST['rdotype'] == 'dealer') {
                if (!empty($_POST['cboDealer'])) {
                    $dealer_id = implode(",", $_POST['cboDealer']);
					
					$arrManagerData = $this->_objDb->fetchAll("  SELECT GROUP_CONCAT(mgr_email)ccaddress FROM( SELECT (mgr_email) FROM dealers d 
					INNER JOIN development_managers dm ON d.BDM_id=dm.mgrid OR d.SDM_id=dm.mgrid 
					WHERE dealerid IN (" . $dealer_id . ") GROUP BY mgrid,mgr_email )tbl");
                    $ccadress[] = $arrManagerData[0]["ccaddress"];
                }
            } else if ($_POST['rdotype'] == 'region') {

                if (!empty($_POST['cboSalesRegion']) && !empty($_POST['cboAfterSalesRegion'])) {
                    $dealeridconcat = $aft_dealeridconcat = '';
                    $sales_reg_ids = implode(",", $_POST['cboSalesRegion']);
					
                   
					$arrSalesRegionData = $this->_objDb->fetchAll(" SELECT GROUP_CONCAT(mgr_email)ccaddress FROM(
					SELECT (mgr_email) FROM dealers d
					INNER JOIN regions r ON r.regionid=d.sales_region_id
					INNER JOIN development_managers dm ON d.bdm_id=dm.mgrid OR d.sdm_id=dm.mgrid
					WHERE r.regionid IN (" . $sales_reg_ids . ") 
					GROUP BY mgrid,mgr_email
					)tbl");
                    $ccadresssales[] = $arrSalesRegionData[0]["ccaddress"];
                    $aftersales_reg_ids = implode(",", $_POST['cboAfterSalesRegion']);
	
                    $arrafterSalesRegionData = $this->_objDb->fetchAll(" SELECT GROUP_CONCAT(mgr_email) ccaddress FROM(
                                SELECT (mgr_email) FROM dealers d
                                INNER JOIN regions r ON r.regionid=d.sales_region_id
                                INNER JOIN development_managers dm ON d.bdm_id=dm.mgrid OR d.sdm_id=dm.mgrid
                                WHERE r.regionid IN (" . $aftersales_reg_ids . ") 
                                GROUP BY mgrid,mgr_email
                                )tbl");

                    $ccadressaftersales[] = $arrafterSalesRegionData[0]["ccaddress"];
					
					
                }
            }
			
            //send email
			
            if (!empty($toaddress)) {
				
				if(!empty($ccadresssales) ||!empty($ccadressaftersales) )
				{
				foreach($ccadresssales as $salescc)
				{ 
					$sales_cc=explode(",",$salescc);
					
				}
				foreach($ccadressaftersales as $aftsalescc)
				{ 
					$aftersales_cc=explode(",",$aftsalescc);
					
				}
				
						$ccadress=array_merge($sales_cc,$aftersales_cc);
						
						$ccaddress=array_unique($ccadress);
				}
				if(!empty($ccadress_bdm) ||!empty($ccadress_sdm) )
				{
				foreach($ccadress_bdm as $bdmcc)
				{ 
					$bdm_cc=explode(",",$bdmcc);
					
				}
				foreach($ccadress_sdm as $sdmcc)
				{ 
					$sdm_cc=explode(",",$sdmcc);
					
				}
				
						$ccadress=array_merge($bdm_cc,$sdm_cc);
						
						$ccaddress=array_unique($ccadress);
				}
				
				
				
                $str_ccaddresslist= implode(",",$ccadress);
				//print_r($toaddress);print_r($ccaddress); die;
				if(!empty($str_ccaddresslist))
					$str_ccaddress=$str_ccaddresslist.",".$this->_objConfig->mbaupemail->mbaup_email;
				else
					$str_ccaddress=$this->_objConfig->mbaupemail->mbaup_email;
                $objMail = new Lib_Email();
                /*$LogoUrl = $this->ClientInfo['logo_url']; //////???????????????????????????
                $logo = HTTP_HOST . "/public/uploads/clients/logo/$LogoUrl";*/

                $arrTags = array();	
                
                $arrTags["{UserName}"] = $arrUserDetail['0']['user_name'];
               
                $arrTags["{password}"] = $this->better_decrypt($arrUserDetail['0']['user_salted_password']);
                $arrTags["{Website_site}"] = HTTP_HOST;

              $SQL = $this->_objDb->fetchall(" Select tmplid from email_templates  Where email_token= 'new_user_creation' ");   
			 
               $TemplateId = $SQL[0]['tmplid'];
               if(!empty($TemplateId))
               {
				  
				    $return_array= $objMail->SendEmail($arrTags, $TemplateId, $toaddress,'',$str_ccaddress);
			   $test=   $this->updateAlertsLog('new_user_creation','',$return_array['subject'],$return_array['body'],'',
			   $toaddress,$str_ccaddress,'',$UserId);
               }
						
            }
        }
        //TT:770 Anuj
		foreach($arrRsUserData as $key => $arrVal)
		{
			if($arrVal['source_type']=='salesregion' || $arrVal['source_type']=='aftersalesregion')
			{
				if($arrVal['source_type']=='salesregion')
				{
					$arrSelectedSalesRegion[]=$arrVal["sourceid"];
				}
				elseif ($arrVal['source_type']=='aftersalesregion')
				{
					$arrSelectedAfterSalesRegion[]=$arrVal["sourceid"];
				}

				$this->view->showComboRegion ='';
				$this->view->showComboDealer ="display:none;";
				$this->view->showComboManager ="display:none;";
			}
			elseif($arrVal['source_type']=='dealer')
			{
				$arrSelectedDealer[]=$arrVal["sourceid"];
				$this->view->showComboRegion ="display:none;";
				$this->view->showComboManager ="display:none;";
				$this->view->showComboDealer ='';
			}
			elseif($arrVal['source_type']=='BDM/SDM')
			{
				$arrSelectedManager[]=$arrVal["sourceid"];
				$this->view->showComboRegion ="display:none;";
				$this->view->showComboDealer ="display:none;";
				$this->view->showComboManager ='';
			}
			elseif($arrVal['source_type']=='national')
			{
				//$arrSelectedSDManager[]=$arrVal["sourceid"];
				$this->view->showComboRegion ="display:none;";
				$this->view->showComboDealer ="display:none;";
				$this->view->showComboManager ='display:none;';
			}

		}
		$this->view->rdotype = $arrVal["source_type"];
		
		//Addedd by gaurav
        $arrSystems	= $this->_objDb->fetchAll("select systemid, system_name from systems where active='1' order by system_name ASC");
        $this->view->arrSystems = $arrSystems;
        if($UserId){
           $systemSet	= $this->_objDb->fetchRow("select systems  from users where userid = '".$UserId."'");
           if($systemSet['systems']){
             $userSystems = unserialize($systemSet['systems']);
           }else{
             $userSystems = array();
           }
        }else{
          $userSystems = array();
        } 
        $this->view->userSystems = $userSystems;
        
        //EOC gaurav
		/**
		 * To create value of sales region Drop down
		 * 
		 */

		$arrRsRegion	= $this->_objDb->fetchAll("select regionid, region_name from regions where is_sales='Yes' order by region_name ASC");
		$arrSalesRegionOption ='';
		foreach($arrRsRegion as $regionKey => $arrregionVal )
		{
			$arrSalesRegionOption .= "<option value='".$arrregionVal["regionid"]."' title='".$arrdealerVal["regionid"]."'";

			if(in_array($arrregionVal["regionid"],$arrSelectedSalesRegion))
			{
				$arrSalesRegionOption .= "selected";
			}

			$arrSalesRegionOption .= ">".$arrregionVal["region_name"]."</option>\n";
		}

		$this->view->salesRegionOption=$arrSalesRegionOption;

		/**
		 * To create value of after sales region Drop down
		 * 
		 */

		$arrRsRegion	= $this->_objDb->fetchAll("select regionid, region_name from regions where is_after_sales='Yes' order by region_name ASC");
		$arrAfterSalesRegionOption ='';
		foreach($arrRsRegion as $regionKey => $arrregionVal )
		{
			$arrAfterSalesRegionOption .= "<option value='".$arrregionVal["regionid"]."' title='".$arrdealerVal["regionid"]."'";

			if(in_array($arrregionVal["regionid"],$arrSelectedAfterSalesRegion))
			{
				$arrAfterSalesRegionOption .= "selected";
			}

			$arrAfterSalesRegionOption .= ">".$arrregionVal["region_name"]."</option>\n";
		}

		$this->view->afterSalesRegionOption=$arrAfterSalesRegionOption;

		/**
		 * To create value of Dealer Drop down
		 * 
		 */
		$arrRsDealer	= $this->_objDb->fetchAll("select dealerid, dealer_name,business_name from dealers where is_deleted='No' order by dealer_name ");


		$arrDealerOption ='';
		foreach($arrRsDealer as $regionKey => $arrdealerVal )
		{
			$arrDealerOption .= "<option value='".$arrdealerVal["dealerid"]."' title='".$arrdealerVal["business_name"]."'";
			if(in_array($arrdealerVal["dealerid"],$arrSelectedDealer))
			{
				$arrDealerOption .= "selected";
			}

			$arrDealerOption .= ">".$arrdealerVal["dealer_name"]."</option>\n";
		}

		$this->view->dealerOption=$arrDealerOption;

		/**
		 * To create value of Manager Drop down
		 * 
		 */
		$arrRsManager	= $this->_objDb->fetchAll("select mgrid,mgr_type,mgr_name from development_managers order by mgr_type ");

		$arrBDManagerOption ='';
		$arrSDManagerOption ='';

		if(count($arrRsManager)>0)
		{
			foreach($arrRsManager as $regionKey => $arrManagerVal )
			{
				if($arrManagerVal["mgr_type"]=='business')
				{
					$arrBDManagerOption .= "<option value='".$arrManagerVal["mgrid"]."' title='".$arrManagerVal["mgr_name"]."'";
					if(in_array($arrManagerVal["mgrid"],$arrSelectedManager))
					{
						$arrBDManagerOption .= "selected";
					}

					$arrBDManagerOption .= ">".$arrManagerVal["mgr_name"]."</option>\n";
				}
				elseif($arrManagerVal["mgr_type"]=='service')
				{
					$arrSDManagerOption .= "<option value='".$arrManagerVal["mgrid"]."' title='".$arrManagerVal["mgr_name"]."'";
					if(in_array($arrManagerVal["mgrid"],$arrSelectedManager))
					{
						$arrSDManagerOption .= "selected";
					}

					$arrSDManagerOption .= ">".$arrManagerVal["mgr_name"]."</option>\n";
				}


			}
		}

		$this->view->BDManagerOption=$arrBDManagerOption;
		$this->view->SDManagerOption=$arrSDManagerOption;

		/**
		 * If Page is Posted
		*/
		if($this->_request->isPost())
		{
			/**
			 * TO store Source of Data related settings
			 * 
			 */

			if($this->_request->__isset('rdotype'))
			{

				/**
				 * Delete existing data of this partcular user 
				 */

				$qry=$this->_objDb->Query("delete from user_data_source where userid = '".$UserId."'");

				/**
				 * if user select region
				*/
				$sourceType=$this->_request->getPost("rdotype");
				if($sourceType=='region')
				{
					$arrSourceId = $this->_request->getPost("cboSalesRegion");
					$arrAfterSalesSourceId = $this->_request->getPost("cboAfterSalesRegion");
					if(count($arrAfterSalesSourceId)>0)
					{
						foreach($arrAfterSalesSourceId as $val)
						{
							$insertValues .= "('".$UserId."','aftersalesregion','".$val."'),";

						}
					}
					$sourceType ='salesregion';
				}
				/**
				 * if user select dealer
				*/
				elseif ($sourceType=='dealer')
				{
					$arrSourceId = $this->_request->getPost("cboDealer");
				}
				/**
				 * if user select dealer
				*/
				elseif ($sourceType=='national')
				{
					/**
					 * Insert new data as set by the admin
					 */

					$qry=$this->_objDb->Query("insert into user_data_source ( userid, source_type, sourceid ) values ('".$UserId."','".$sourceType."','0')");
				}
				/**
				 * if user select BDmaneger / SDmaneger
				*/
				elseif ($sourceType=='manager')
				{
					$arrSourceId = $this->_request->getPost("cboBDManager");

					$arrSDSourceId = $this->_request->getPost("cboSDManager");
					if(count($arrSDSourceId)>0)
					{
						foreach($arrSDSourceId as $val)
						{
							$insertValues .= "('".$UserId."','BDM/SDM','".$val."'),";

						}
					}
					$sourceType ='BDM/SDM';
				}

				if(count($arrSourceId)>0)
				{
					foreach($arrSourceId as $val)
					{
						$insertValues .= "('".$UserId."','".$sourceType."','".$val."'),";

					}

					$insertValues = substr($insertValues , 0 , -1);

					/**
					 * Insert new data as set by the admin
					 */

					$qry=$this->_objDb->Query("insert into user_data_source ( userid, source_type, sourceid ) values ". $insertValues);
				}
			}
			//Addedd By Gaurav
			if(is_array($this->_request->getPost("chksystems")))
            {
             $arrSystems = serialize($this->_request->getPost("chksystems"));
             $this->_objDb->Query("update users set systems ='$arrSystems' where userid = $UserId");
            }else{
             $this->_objDb->Query("update users set systems ='' where userid = $UserId");
            }
			
			//EOC Gaurav

			$data = "";
			$arrPrv = $this->_request->getPost("chkbs");
			if(is_array($arrPrv))
			{
				foreach($arrPrv as $val)
				{
					if($data == "") $data = " ($UserId , $val) ";
					else $data .= " , ($UserId , $val) ";
				}

			}
			$in_param = array($data , $UserId , 1, $this->_showAllModule);
			$proc = $this->_objDb->getSpCallString("usp_processPrv" , $in_param);
			$this->_objDb->processSpCall($proc);
			if ($this->view->redirection == 'client' || $_POST['from']== 'client') {
				if ($this->_request->getParam('msg') == "clientadded") {
					$this->_redirect('/client/index/index/?msg=added');
				} else {
					$this->_redirect("/client/index/index/?msg=Privileges updated");
				}
			} else {
				if ($this->_request->getParam('msg') == "added") {
					$this->_redirect('/'.$this->_request->getModuleName()."/index/index/?msg=added");
				} else {
					$this->_redirect('/'.$this->_request->getModuleName()."/index/index/?msg=Privilegesupdated");
				}
			}
		}
		else //otherwise if page is normaly coming
		{
			$in_param = array('' , $UserId  , 0, $this->_showAllModule);
			$proc = $this->_objDb->getSpCallString('usp_processPrv' , $in_param);
			$this->_objDb->processSpCall($proc);
		}
		$prv_data = $this->_objDb->getSpData(); //Get all previledeges data

		//Format data as per require
		$rec_prv ="";
		foreach($prv_data  as $a )
		{
			$rec_prv[$a['privid']] = 1;
		}
		$this->view->Checker = $rec_prv;

		$resultSet	= $this->_objDb->fetchRow("select user_type  from users where userid = '".$UserId."'");

		$userType1 = $resultSet["user_type"];


		if ($userType1 == 'supplier' || $userType1 == 'client'|| $userType1 == 'clientuser') {
			$userType = 'CLIENT';$this->view->userTyp = 'CLIENT';
		} else {
			$userType = 'APD';$this->view->userTyp = 'APD';
		}

		//Get Module Name with positions
		$this->_objDb->processSpCall("call usp_getPrvForModule('".$userType."','".$this->_showAllModule."')");
		$Rows_Modules =$this->_objDb->getSpData();
		$this->view->Modules = $Rows_Modules;

		//Get All Module priv

		if ($this->_objUserDetails->is_superadmin == 1 || $this->_objUserDetails->user_manager == "Yes")
		{
			$Rows_Priv = $this->_objDb->fetchAll("Select * from module_privileges order by sort_order ");
		}		
		else
		{
			/* commented By when implemented PO: APDGR430
			$Rows_Priv = $this->_objDb->fetchAll("Select * from module_privileges where  privid in (select PrivId from user_privileges where userid = '".$UserId."') order by sort_order ");
			*/
			/////PO: APDGR430
			$Rows_Priv = $this->_objDb->fetchAll("Select * from module_privileges where  privid in (select PrivId from user_privileges where userid = '".$this->_objUserDetails->userid."') and moduleid !=2 order by sort_order ");
		}
		

		//Format priv accourdign to requirement
		$arrPriv = array();
		foreach($Rows_Priv as $val)
		{
			if(!array_key_exists($val['moduleid'], $arrPriv))
			{
				$arrPriv[$val['moduleid']] = array();
				$arrPriv[$val['moduleid']][] = $val;
			}
			else
			{
				$arrPriv[$val['moduleid']][] = $val;
			}
		}
//print_r($arrPriv);die;
		$this->view->Priv = $arrPriv;
		$Rows_Nm = $this->_objDb->fetchAll("Select concat(first_name , ' ' , last_name) as UNM from users where userid='".$this->_request->getParam("UserId")."'");

		$this->view->unm = $Rows_Nm[0]['UNM'];

		$arrSM = $this->getPrivMod($prv_data , $Rows_Modules , $arrPriv );
         
		$this->view->ASM = $arrSM;



	}


	/**********************************************
	*Function : delete Action controls all operation relating deleting an apd user's details.
	*Tables Refered/Modified : users
	*Author: Richa Sharma
	*/
	function deleteAction() {

		$this->view->title = "Delete Users";

		if (!clsAcl::IsAllowed('Delete',$this->strModule)) {
			$this->view->authentication = 'false';
		}

		if ($this->_request->isPost()) {
			$id = (int)$this->_request->getPost('userid');
			$del = $this->_request->getPost('del');
			if ($del == 'Yes' && $id > 0) {
				$user = new Users();
				$where = ' userid = ' . $id;
				$user->delete($where);
				$this->_redirect('/'.$this->_request->getModuleName().'?msg=deleted');
			}
			$this->_redirect('/'.$this->_request->getModuleName());
		} else {
			$id = (int)$this->_request->getParam('UserId');
			if ($id > 0) {
				$user = new Users();
				$this->view->user = $user->fetchRow('userid='.$id);
			}

		}
	}


	/**************************************************************
	* Function              : (exportuser)function to create excel file from user table
	* Created By            : dipanwita
	* Added Date            : 9/2/2008 10:10:30 AM
	* Description           : create excel file from user table
	* Modified on           : September 12 08
	* Modified By           : Richa Sharma
	* Modification Comments : The header info, excel name and user
	*                         details were changed for better understandability.
	*******************************************/
	public function exportuser_oldAction()
	{

		require_once(DOCUMENT_ROOT.'/library/Lib/excel/Writer.php');
		$workbook = new Spreadsheet_Excel_Writer();
			$workbook->setVersion(8);
		if ($this->UserType == 'supplier' || $this->UserType == 'client'|| $this->UserType == 'clientuser') {
			$userType = 'clientuser';
			$patch = " and parent_useriD = '".$this->clientId."' and user_type='clientuser' ";
		} else {
			$userType = 'user';
			$patch = " and user_type='user'";
		}
		$data1 = $this->_request->getParam('data')==''?$_GET['data']:$this->_request->getParam('data');
		if ($data1!='')
		{
			$patch.=" and (first_name like '%".$data1."%' or last_name like '%".$data1."%' or user_name like '%".$data1."%' or email like '%".$data1."%')";
		}

		$allData = $this->_objDb->fetchAll("Select SQL_CALC_FOUND_ROWS userid,user_name, concat(first_name,' ',last_name) as Name, email, (CASE WHEN user_status=1 THEN 'Active' ELSE 'Inactive' END) as ActivationStatus, added_date as AddedOn from users where 1=1 ".$patch." order by userid limit 5");

		$header .=$this->_helper->Translate("User_Export_HeadingUserId").",".
		$this->_helper->Translate("User_Export_HeadingUserName").",".
		$this->_helper->Translate("User_Export_HeadingName").",".
		$this->_helper->Translate("User_Export_HeadingEmail").",".
		$this->_helper->Translate("User_Export_HeadingStatus").",".
		$this->_helper->Translate("User_Export_HeadingAddedDate").",".
		$this->_helper->Translate("User_Export_HeadingDataSourceNational").",".
		$this->_helper->Translate("User_Export_HeadingDataSourceSalesRegional").",".
		$this->_helper->Translate("User_Export_HeadingDataSourceAfterSalesRegional").",".
		$this->_helper->Translate("User_Export_HeadingDataSourceDealers").",".
		$this->_helper->Translate("User_Export_HeadingDataSourceBDM").",".
		$this->_helper->Translate("User_Export_HeadingDataSourceSDM").",";

		//$dataSource = array("national"=>array(),"dealer"=>array(),"salesregion"=>array(),"aftersalesregion"=>array(),"BDM"=>array(),"SDM"=>array());
		/**
		 * Create header for privillages heading
		 */
		$xtraHeader ='';
		$moduleData = $this->_objDb->fetchAll("select moduleid,module_description from modules where moduleid not in (16,19)");
		foreach($moduleData as $arrVal)
		{
			$xtraHeader .= $arrVal["module_description"].",";

		}
		$xtraHeader = substr($xtraHeader,0,-1);
		if(strlen($xtraHeader)>0)
		{
			$header .= ",".$xtraHeader;
		}
		//$header_file['0'] = $xls_header;

		$totRows =  $this->_objDb->fetchRow("select FOUND_ROWS() as tot");
		if($totRows > 0)
		{
			foreach($allData as $dbdata) :

			// for datasource
			$dbdata["national"] = '';
			$dbdata["salesregion"] = '';
			$dbdata["aftersalesregion"] = '';
			$dbdata["dealer"] = '';
			$dbdata["BDM"] = '';
			$dbdata["SDM"] = '';

			$proc = $this->_objDb->getSpCallString("usp_userDatasourceDetail" , array($dbdata["userid"]));
			$Result      = $this->_objDb->processSpCall($proc);
			$resultSet   = $this->_objDb->getSpData();

			foreach($resultSet as $key=>$arrVal)
			{
				/**
				 * if chosse both sales & aftersales region
				 * 
				 */						
				if(is_array($arrVal[0]))
				{
					if($arrVal[0][0]=="salesregion")
					{
						$dbdata["salesregion"] = $arrVal[0]["region_name"];
					}
					if($arrVal[0][0]=="aftersalesregion")
					{
						$dbdata["aftersalesregion"] = $arrVal[0]["region_name"];
					}

				}
				else
				{
					/**
							 * For dealer or BDM / SDM or national
							 */
					if($arrVal[0]=="national")
					{
						$dbdata["national"] = "True";
					}
					elseif($arrVal[0]=="dealer")
					{
						$dbdata["dealer"] = $arrVal["dealer_name"];
					}
					elseif($arrVal[0]=="business")
					{
						$dbdata["BDM"] = $arrVal["mgr_name"];
					}
					elseif($arrVal[0]=="service")
					{
						$dbdata["SDM"] = $arrVal["mgr_name"];
					}
					elseif($arrVal[0]=="salesregion")
					{
						$dbdata["salesregion"] = $arrVal["region_name"];
					}
					elseif($arrVal[0]=="aftersalesregion")
					{
						$dbdata["aftersalesregion"] = $arrVal["region_name"];
					}

				}
			}
			///
			foreach($moduleData as $arrVal)
			{
				$privData = $this->_objDb->fetchAll("select group_concat(m.privilege) priv from user_privileges u
															Inner join module_privileges m on u.privid= m.privid
															 where userid ='".$dbdata["userid"]."' and moduleid = '".$arrVal["moduleid"]."' 
															 group by moduleid"); 		
				$dbdata[$arrVal["module_description"]] =$privData[0]["priv"];
			}
			//$header_file[] = $dbdata;
			
			$line = '';
			foreach($dbdata as $key=>$value)
			{
				
					$line .= $workbook->writeStringCSVformat($key,$value);
			}
			$data .= trim($line)."\n";
			endforeach;
			# this line is needed because returns embedded in the data have "\r"
			# and this looks like a "box character" in Excel
			$data = str_replace("\r", "", $data);

			if(strlen($data)>0)
			{

				#This line will stream the file to the user rather than spray it across the screen
				 header("Pragma: public"); // required
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Cache-Control: private",false); 
				header('Content-Type: text/csv');
				header("Content-Disposition: attachment; filename=\"UserDetail".time().".csv\""); 
				header("Content-Transfer-Encoding: binary");


				

				$msg=$header."\n".$data;
				echo $header."\n".$data; exit;

				$this->view->msg1="Success";
			}
			else  {
				$this->view->msg1="No data!!!!!";
			}
		}
	}




	//create excel but not in use

	public function exportuserAction()
	{

		require_once(DOCUMENT_ROOT.'/library/Lib/excel/Writer.php');
		
		if ($this->UserType == 'supplier' || $this->UserType == 'client'|| $this->UserType == 'clientuser') {
			$userType = 'clientuser';
			$patch = " and parent_useriD = '".$this->clientId."' and user_type='clientuser' ";
		} else {
			$userType = 'user';
			$patch = " and user_type='user'";
		}
		$data1 = $this->_request->getParam('data')==''?$_GET['data']:$this->_request->getParam('data');
		if ($data1!='')
		{
			$patch.=" and (first_name like '%".$data1."%' or last_name like '%".$data1."%' or user_name like '%".$data1."%' or email like '%".$data1."%')";
		}

		$allData = $this->_objDb->fetchAll("Select SQL_CALC_FOUND_ROWS userid,user_name, concat(first_name,' ',last_name) as Name, email, (CASE WHEN user_status=1 THEN 'Active' ELSE 'Inactive' END) as ActivationStatus, added_date as AddedOn from users where 1=1 ".$patch." order by userid ");

		$xls_header = array(
		$this->_helper->Translate("User_Export_HeadingUserId"),
		$this->_helper->Translate("User_Export_HeadingUserName"),
		$this->_helper->Translate("User_Export_HeadingName"),
		$this->_helper->Translate("User_Export_HeadingEmail"),
		$this->_helper->Translate("User_Export_HeadingStatus"),
		$this->_helper->Translate("User_Export_HeadingAddedDate"),
		$this->_helper->Translate("User_Export_HeadingDataSourceNational"),
		$this->_helper->Translate("User_Export_HeadingDataSourceSalesRegional"),
		$this->_helper->Translate("User_Export_HeadingDataSourceAfterSalesRegional"),
		$this->_helper->Translate("User_Export_HeadingDataSourceDealers"),
		$this->_helper->Translate("User_Export_HeadingDataSourceBDM"),
		$this->_helper->Translate("User_Export_HeadingDataSourceSDM")
		);

		//$dataSource = array("national"=>array(),"dealer"=>array(),"salesregion"=>array(),"aftersalesregion"=>array(),"BDM"=>array(),"SDM"=>array());
		/**
		 * Create header for privillages heading
		 */
		$moduleData = $this->_objDb->fetchAll("select moduleid,module_description from modules where moduleid not in (16,19)");
		foreach($moduleData as $arrVal)
		{
			$xls_header[] = $arrVal["module_description"];

		}
		$header_file['0'] = $xls_header;

		$totRows =  $this->_objDb->fetchRow("select FOUND_ROWS() as tot");
		if($totRows > 0)
		{
			foreach($allData as $dbdata) 
			{

			// for datasource
			$dbdata["national"] = '';
			$dbdata["salesregion"] = '';
			$dbdata["aftersalesregion"] = '';
			$dbdata["dealer"] = '';
			$dbdata["BDM"] = '';
			$dbdata["SDM"] = '';

			$proc = $this->_objDb->getSpCallString("usp_userDatasourceDetail" , array($dbdata["userid"]));
			$Result      = $this->_objDb->processSpCall($proc);
			$resultSet   = $this->_objDb->getSpData();

			foreach($resultSet as $key=>$arrVal)
			{
				/**
						 * if chosse both sales & aftersales region
						 * 
						 */						
				if(is_array($arrVal[0]))
				{
					if($arrVal[0][0]=="salesregion")
					{
						$dbdata["salesregion"] = $arrVal[0]["region_name"];
					}
					if($arrVal[0][0]=="aftersalesregion")
					{
						$dbdata["aftersalesregion"] = $arrVal[0]["region_name"];
					}

				}
				else
				{
					/**
							 * For dealer or BDM / SDM or national
							 */
					if($arrVal[0]=="national")
					{
						$dbdata["national"] = "True";
					}
					elseif($arrVal[0]=="dealer")
					{
						$dbdata["dealer"] = $arrVal["dealer_name"];
					}
					elseif($arrVal[0]=="business")
					{
						$dbdata["BDM"] = $arrVal["mgr_name"];
					}
					elseif($arrVal[0]=="service")
					{
						$dbdata["SDM"] = $arrVal["mgr_name"];
					}
					elseif($arrVal[0]=="salesregion")
					{
						$dbdata["salesregion"] = $arrVal["region_name"];
					}
					elseif($arrVal[0]=="aftersalesregion")
					{
						$dbdata["aftersalesregion"] = $arrVal["region_name"];
					}

				}
			}
			///
			foreach($moduleData as $arrVal)
			{
				$privData = $this->_objDb->fetchAll("select cast(group_concat(m.privilege)as char(10000)) priv from user_privileges u
															Inner join module_privileges m on u.privid= m.privid
															 where userid ='".$dbdata["userid"]."' and moduleid = '".$arrVal["moduleid"]."' 
															 group by moduleid"); 		
				$dbdata[$arrVal["module_description"]] =$privData[0]["priv"];
			}

			$header_file[] = $dbdata;
			}



			$workbook = new Spreadsheet_Excel_Writer();
			$workbook->setVersion(8);
			$exp_name = "UserDetail".time().".xls";
			$worksheetName = substr($exp_name, 0,strrpos($exp_name, "."));

			$worksheet =&$workbook->addWorksheet($worksheetName);
			reset($header_file);
			$i = 0;
			foreach($header_file as $a)
			{
				$j = 0;
				foreach($a as $p)
				{
					$worksheet->writeString($i , $j , $p);
					$j++;
				}
				$i++;
			}
			$workbook->close();
			$workbook->send($exp_name);
		}
		else
		{
			$this->view->$msg='No data!!';
		}
		die;

	}


	function getPrivMod($PrivId , $Modules , $Prv)
	{

		$PAR = array();
		foreach($Modules as $Module)
		{

			$PAR[$Module[0]['moduleid']] = 0;

			foreach($Module as $Val)
			{
				//echo $Val['ModuleId']."<br />";
				if (isset($Prv[$Val['moduleid']])) {
					foreach($Prv[$Val['moduleid']] as $p_d )
					{
						foreach($PrivId as $p)
						{
							if($p['privid'] == $p_d['privid'])
							{
								$PAR[$Module[0]['moduleid']] = 1;
							}
						}
					}
				}
			}
		}
		//print_r($PAR);
		return $PAR;
	}

//TT:770 Anuj 
Public function better_crypt($string, $salt = NULL) 
        {
	
            $mcrypt_iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
			//var_dump($mcrypt_iv_size);die;
            $mcrypt_iv = mcrypt_create_iv($mcrypt_iv_size, MCRYPT_RAND);

            $mcrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $salt, $string, MCRYPT_MODE_ECB, $mcrypt_iv);

           $encoded = base64_encode($mcrypted);

            return $encoded;
        }
public  function better_decrypt($hash, $salt = NULL){

        $mcrypt_iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $mcrypt_iv = mcrypt_create_iv($mcrypt_iv_size, MCRYPT_RAND);

        $basedecoded = base64_decode($hash);

        $mcrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $salt, $basedecoded, MCRYPT_MODE_ECB, $mcrypt_iv);

        return $mcrypted;
    } 
    
    
	public function updateAlertsLog($alert_type,$event_type, $email_subject,$alert_content,$attachments='',$to_addess,$cc_address='',
      $bcc_address='',$importid='')
      {
        $alert_type = mysql_real_escape_string($alert_type);
        $email_subject = mysql_real_escape_string($email_subject);
        $alert_content = mysql_real_escape_string($alert_content);
        $attachments = mysql_real_escape_string($attachments);
        $to_addess = mysql_real_escape_string($to_addess);
        $cc_address = mysql_real_escape_string($cc_address);
        $bcc_address = mysql_real_escape_string($bcc_address);
        

		$qry = $this->_objDb->Query("insert into alerts_log 
                  (alert_type,event_type,email_subject,to_addess,
                  cc_address,bcc_address,alert_content,attachments,object_type,
                  object_id)
                  values
                  ('".$alert_type."','".$event_type."',
                  '".$email_subject."','".$to_addess."','".$cc_address."',
                  '".$bcc_address."','".$alert_content."','".$attachments."',
                  'other','".$importid."')");
     
          return mysql_insert_id();
        
       
      } 
	   //TT:770 Anuj 

}
?>
