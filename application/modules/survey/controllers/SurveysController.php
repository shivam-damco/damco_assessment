<?php

/**
 * @author  Harpreet Singh
 * @date    21 Dec, 2015
 * @version 1.0
 *
 * Controller to handle event operations
 */

class Survey_SurveysController extends Damco_Core_CoreController
{
    protected $_auth = null;
    protected $_redirector = null;
    protected $_surveyModelObject = null;
    protected $_surveyEventsModelObject = null;
    protected $_config = null;
    protected $_emailObj = null;
    protected $_emailTemplateObj = null;
    protected $_surveyEventTypeObj = null;
    private $_configObject = null;

    /**
     * Method to initialize event controller
     */
    public function init()
    {
        parent::init();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_auth = Zend_Auth::getInstance();
        $this->_surveyModelObject = new Survey_Model_Survey();
        $this->_QuestionsModelObject = new Survey_Model_Questions();
        $this->_surveyEventsModelObject = new Survey_Model_SurveyEvents();
        $this->_surveyCategoriesModelObject = new Survey_Model_SurveyCategories();
        $this->_config = new Survey_Model_Config(); //survey_make_option_nootherbrand
        $this->_emailObj = new Damco_Email();
        $this->_emailTemplateObj = new Survey_Model_AlertEmailTemplates();
        $this->_surveyEventTypeObj = new Survey_Model_EventTypes();
        $arrConfigVariables = ["POST_API_URL"];
        $this->_configValues = $this->_config->getConfigQueIds(
            $arrConfigVariables
        );

        if (!$this->_auth->getIdentity()) {
            $this->_redirect($this->view->serverUrl());
        }
    }

    /**
     * Method to handle index action operations
     */
    public function indexAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_getByAjax();
        }
        $this->view->listcategory = $this->_surveyCategoriesModelObject->getAllSurveyCategoriesName();
        //print_r($listcategory);

        $get = $this->getRequest()->getParams();

        $this->view->get = $get;
        $this->view->role_name = $this->_user->role_name;

        //$eventTypesData = $this->_eventTypeModelObject->getAllEventTypes();
        //$this->view->EventTypesData = $eventTypesData;
        $this->view->flashMessages = $this->_flashMessenger->getMessages();
    }

    /**
     * Method to handle add action operations
     */
    public function addAction()
    {
        ini_set("max_execution_time", 300);
        //$this->_editoptions = new Event_Model_Events();
        $get = $this->getRequest()->getParams();
        $form = new Survey_Form_Surveys();
        $upload = new Zend_File_Transfer();
        $files = $upload->getFileInfo();
        $this->view->eventtypeid = $get["eventtypeid"];
        if ($this->_request->isPost()) {
            $message = "";
            $form->isValid($get);
            //echo "<pre>"; print_r($get);die;
            $form->populate([
                // 'survey_id' => $result[0]['survey_id'],
                // 'event_type' => $get['event_typeid'],
                // 'survey_category'=>$get['survey_category_id'],
                "required_time" => $get["required_time"],
                "survey_name" => $get["survey_name"],
                "survey_invite_subject" => $get["survey_invite_subject"],
                "start_date" => $get["start_date"], //date("d/m/Y", strtotime(implode('/', (explode('-', $get[0]['start_date']))))),
                "end_date" => $get["end_date"], //date("d/m/Y", strtotime(implode('/', (explode('-', $result[0]['end_date']))))),
                "survey_invite_text" => $get["survey_invite_text"],
                "landing_page_message" => $get["landing_page_message"],
                "survey_thanks_message" => $get["survey_thanks_message"],
            ]);

            if (!$form->isValid($get)) {
                /*if (array_key_exists("event_type", $form->getmessages())) {
                    $message.= "<br/>Please select survey type";
                }*/
                // if (array_key_exists("survey_name", $form->getmessages())) {
                // $message.= "<br/>Please enter survey name";
                // }

                if (array_key_exists("start_date", $form->getmessages())) {
                    $message .= "<br/>Please enter start date";
                }

                if (array_key_exists("end_date", $form->getmessages())) {
                    $message .= "<br/>Please enter end date";
                }
                $this->view->messages = $message;
            } else {
                if (!file_exists($_SERVER["DOCUMENT_ROOT"] . "/uploads/csv")) {
                    mkdir(
                        $_SERVER["DOCUMENT_ROOT"] . "/uploads/csv",
                        0777,
                        true
                    );
                }
                if ( isset($files["add_record"]["name"]) 
                    && !empty($files["add_record"]["name"])
                ) 
                {
                    $extension = explode(".", $files["add_record"]["name"]);
                    if ($extension["1"] == "csv") {
                        if (
                            !file_exists(
                                $_SERVER["DOCUMENT_ROOT"] . "/uploads/csv"
                            )
                        ) {
                            mkdir(
                                $_SERVER["DOCUMENT_ROOT"] . "/uploads/csv",
                                0777,
                                true
                            );
                        }
                        $file_path =
                            $_SERVER["DOCUMENT_ROOT"] .
                            "/uploads/csv/" .
                            $files["add_record"]["name"];
                        move_uploaded_file(
                            $files["add_record"]["tmp_name"],
                            $file_path
                        );
                        ($fp = fopen($file_path, "r")) or
                            die("can't open file");
                        $valid_employee_keys = [];
                        $all_csv_employee_id = [];
                        $all_employee_data = $this->getValidatecsvDataAction();
                        foreach ($all_employee_data as $values) {
                            $arr[] = $values->EmpCode;
                        }
                        while ($csv_line = fgetcsv($fp, 1024)) {
                            //$arr=array();
                            for ($i = 0, $j = count($csv_line); $i < $j; $i++) {
                                if (isset($csv_line[$i])) {
                                    if ($csv_line[$i] != "Employee Id") {
                                        $all_csv_employee_id[] = $csv_line[$i];
                                    }
                                    if (in_array($csv_line[$i], $arr)) {
                                        $valid_employee_keys[] = $csv_line[$i];
                                    } else {
                                        if ($csv_line[$i] != "Employee Id") {
                                            $employee_keys[] = $csv_line[$i];
                                        }
                                    }
                                }
                            }
                        }
                        fclose($fp) or die("can't close file");
                    } else {
                        $message .= "<br/>Wrong File Format";
                        $this->view->messages = $message;
                    }
                }

                if ( isset($files["add_external_aspirants"]["name"]) 
                    && !empty($files["add_external_aspirants"]["name"])
                ) 
                {
                    $extension = explode(".", $files["add_external_aspirants"]["name"]);
                    if ($extension["1"] == "csv") {
                        if (
                            !file_exists(
                                $_SERVER["DOCUMENT_ROOT"] . "/uploads/csv"
                            )
                        ) {
                            mkdir(
                                $_SERVER["DOCUMENT_ROOT"] . "/uploads/csv",
                                0777,
                                true
                            );
                        }
                        $file_path =
                            $_SERVER["DOCUMENT_ROOT"] .
                            "/uploads/csv/" .
                            $files["add_external_aspirants"]["name"];
                        move_uploaded_file(
                            $files["add_external_aspirants"]["tmp_name"],
                            $file_path
                        );
                        ($fp = fopen($file_path, "r")) or
                            die("can't open file");
                        $aspirants_employee_data = [];
                        $i = 0;
                        if (($handle = $fp) !== false) {
                            $columns = fgetcsv($handle, 1000, ",");
                            $num = count($columns);
                            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                                $csv[$i] = array_combine($columns, $row);
                                $aspirants_employee_data[] = $csv[$i];
                                $i++;
                            }
                            fclose($handle) or die("can't close file");
                        }
                    } else {
                        $message .= "<br/>Wrong File Format";
                        $this->view->messages = $message;
                    }
                }
                // echo "<pre>"; print_r($aspirants_employee_data); die;

                $isrecordExist = $this->_surveyModelObject->checkRecordExist(
                    $get["eventtypeid"],
                    $get["survey_name"]
                );

                //$check_duplicate =
                if (isset($valid_employee_keys)) {
                    foreach (
                        array_count_values($valid_employee_keys)
                        as $duplicate_key => $check_duplicate
                    ) {
                        if ($check_duplicate > 1) {
                            $duplicate_employee_keys[] = $duplicate_key;
                        }
                    }
                }
                if (
                    (isset($employee_keys) && count($employee_keys) != 0) ||
                    (isset($duplicate_employee_keys) &&
                        count($duplicate_employee_keys) != 0) ||
                    (isset($all_csv_employee_id) &&
                        count($all_csv_employee_id) == 0)
                ) {
                    if (
                        isset($all_csv_employee_id) &&
                        count($all_csv_employee_id) == 0
                    ) {
                        $message .= "<br/>No Data in File";
                    } elseif (isset($duplicate_employee_keys)) {
                        $employee_id = implode(",", $duplicate_employee_keys);
                        $message .=
                            "<br/>Duplicate Employee Id " . $employee_id;
                    } else {
                        $employee_id = implode(",", $employee_keys);
                        $message .=
                            "<br/>Employee Id does not exist " . $employee_id;
                    }
                    $this->view->messages = $message;
                }

                $valid_aspirants_email = [];
                $invalid_aspirants_email = [];
                foreach ($aspirants_employee_data as $key => $value) {
                    if (filter_var($value['Email'], FILTER_VALIDATE_EMAIL)) {
                      $valid_aspirants_email[] = $value['Email'];
                    } else{
                        unset($aspirants_employee_data[$key]);
                        $invalid_aspirants_email[] = $value['Email'];
                    }
                }

                //$check_duplicate emails of aspirants
                if (isset($valid_aspirants_email)) {
                    foreach (
                        array_count_values($valid_aspirants_email)
                        as $duplicate_email_key => $check_duplicate_email
                    ) {
                        if ($check_duplicate_email > 1) {
                            $duplicate_employee_email[] = $duplicate_email_key;
                        }
                    }
                }

                if ( isset($valid_aspirants_email) || isset($invalid_aspirants_email) || isset($duplicate_employee_email))
                {
                    if( isset($valid_aspirants_email) && count($valid_aspirants_email) == 0 ){
                        // echo "here"; die;
                        $message .= "<br/>No Data in File";
                    } elseif( isset($invalid_aspirants_email) 
                              && count($invalid_aspirants_email) != 0) {
                         // echo "reaching here"; die;
                        $employee_email = implode(",", $invalid_aspirants_email);
                        $message .= "<br/>Invalid Employee Email " . $employee_email;
                    } elseif(isset($duplicate_employee_email) 
                             && count($duplicate_employee_email) != 0){
                         // echo "No just here"; die;
                        $employee_email = implode(",", $duplicate_employee_email);
                        $message .= "<br/>Duplicate Employee Email " . $employee_email;
                    }
                    $this->view->messages = $message;
                }

                if ($isrecordExist) {
                    $message .=
                        "<br/>Survey instance with same name already exist with associated survey";
                    $this->view->messages = $message;
                } elseif (
                    date(
                        "Y-m-d",
                        strtotime(
                            implode("-", explode("/", $get["start_date"]))
                        )
                    ) >
                    date(
                        "Y-m-d",
                        strtotime(implode("-", explode("/", $get["end_date"])))
                    )
                ) {
                    $message .= "<br/>Start date should be less than end date";
                    $this->view->messages = $message;
                } else {
                    if (empty($message)) {
                        $eventTypeId = $get["eventtypeid"];
                        $surveyName = $get["survey_name"];
                        //$mailsubject = $get['email_subject'];
                        $mailsubject = $get["survey_invite_subject"];
                        $startDate = date(
                            "Y-m-d",
                            strtotime(
                                implode("-", explode("/", $get["start_date"]))
                            )
                        );
                        $endDate = date(
                            "Y-m-d",
                            strtotime(
                                implode("-", explode("/", $get["end_date"]))
                            )
                        );
                        if (
                            isset($get["required_time"]) &&
                            $get["required_time"] != ""
                        ) {
                            $requiredTime = $get["required_time"];
                        } else {
                            $requiredTime = 10;
                        }

                        $addedDate = $startDate;
                        $addedBy = $this->_user->id;
                        $modifiedBy = $this->_user->id;
                        if (isset($get["form_ajax_employee"])) {
                            $employeeDetails = $get["form_ajax_employee"];
                        } else {
                            $employeeDetails = "";
                        }
                        $survey_invite_subject = $get["survey_invite_subject"];
                        $landing_page_message = $get["landing_page_message"];
                        $survey_thanks_message = $get["survey_thanks_message"];
                        $survey_invite_text = $get["survey_invite_text"];

                        $gettest_email_data = $this->_surveyModelObject->getSurveyContents(
                            $eventTypeId
                        );
                        if ($get["test_email_status"] == 1) {
                            $arrEventType = [
                                "event_typeid" => $eventTypeId,
                                "survey_name" => $surveyName,
                                "start_date" => $startDate,
                                "end_date" => $endDate,
                                "added_on" => $addedDate,
                                "added_by" => $addedBy,
                                "modified_by" => $modifiedBy,
                                "required_time" => $requiredTime,
                                "email_subject" => $mailsubject,
                                "invite_subject" => $survey_invite_subject,
                                "invite_content" => $survey_invite_text,
                                "reminder_subject" => $survey_invite_subject,
                                "reminder_content" => $survey_invite_text,
                                "landing_page_content" => $landing_page_message,
                                "is_test_email" => $get["test_email_status"],
                                "thanks_message" => $survey_thanks_message,
                            ];

                            if ($gettest_email_data[0]["survey_id"] != "") {
                                $arrEventType["survey_id"] =
                                    $gettest_email_data[0]["survey_id"];
                            }
                            $surveyId = $this->_surveyModelObject->saveData(
                                $arrEventType
                            );
                            if ($surveyId == "") {
                                $surveyId = $gettest_email_data[0]["survey_id"];
                            }
                        } else {
                            /*if ( isset( $gettest_email_data[0]['survey_id'] ) && $gettest_email_data[0]['survey_id'] > 0 ) {
                                $arrEventType = array('event_typeid' => $eventTypeId, 
                                'survey_id'=>$gettest_email_data[0]['survey_id'],                           
                                'added_by'=>$addedBy,
                                'modified_by'=>$modifiedBy,
                                'required_time'=>$requiredTime,
                                'email_subject'=>$mailsubject,
                                'invite_subject'=>$survey_invite_subject,
                                'invite_content'=>$survey_invite_text,
                                'reminder_subject'=>$survey_invite_subject,
                                'reminder_content'=>$survey_invite_text,
                                'landing_page_content'=>$landing_page_message,                          
                                'thanks_message'=>$survey_thanks_message);
                                $this->_surveyModelObject->saveData($arrEventType);
                            }*/

                            $arrEventType = [
                                "event_typeid" => $eventTypeId,
                                "survey_name" => $surveyName,
                                "start_date" => $startDate,
                                "end_date" => $endDate,
                                "added_on" => $addedDate,
                                "added_by" => $addedBy,
                                "modified_by" => $modifiedBy,
                                "required_time" => $requiredTime,
                                "email_subject" => $mailsubject,
                                "invite_subject" => $survey_invite_subject,
                                "invite_content" => $survey_invite_text,
                                "reminder_subject" => $survey_invite_subject,
                                "reminder_content" => $survey_invite_text,
                                "landing_page_content" => $landing_page_message,
                                "thanks_message" => $survey_thanks_message,
                            ];

                            $surveyId = $this->_surveyModelObject->saveData(
                                $arrEventType
                            );
                        }

                        $eventTypeData = $this->_surveyEventTypeObj->getEventTypesByID(
                            $eventTypeId
                        );
                        //$emailTemplateData = $this->_emailTemplateObj->getDamcoInternalSurveyEmailTemplate();
                        $emailTemplateData[0]["content"] = $survey_invite_text;

                        $emailSubjectArray = $this->_config->getConfigQueIds(
                            "survey_mail_subject"
                        );
                        $emailSubject = $mailsubject;
                        if ($employeeDetails != "") {
                            foreach ($employeeDetails as $empData) {
                                $details = explode("<==>", $empData);
                                $surveyCode = bin2hex(
                                    openssl_random_pseudo_bytes("32")
                                );
                                //$surveyCode = bin2hex(('32'));
                                $arrEventTypeData = [
                                    "employee_id" => $details[0],
                                    "employee_name" => $details[1],
                                    "employee_department" => $details[2],
                                    "email" => $details[3],
                                    "survey_id" => $surveyId,
                                    "event_typeid" => $eventTypeId,
                                    "event_date" => $addedDate,
                                    "added_date" => $addedDate,
                                    "added_by" => $addedBy,
                                    "modified_by" => $modifiedBy,
                                    "survey_code" => $surveyCode,
                                ];
                                $eventID = $this->_surveyEventsModelObject->saveData(
                                    $arrEventTypeData
                                );

                                //$emailTemplateData = $this->_emailTemplateObj->getDamcoInternalSurveyEmailTemplate();
                                //$emailSubjectArray = $this->_config->getConfigQueIds('survey_mail_subject');
                                // $emailSubject = $emailSubjectArray['survey_mail_subject'];
                                //$emailSubject =  $mailsubject;
                                $this->_emailObj->surveyInvitationMail(
                                    $eventID,
                                    $emailTemplateData,
                                    $emailSubject,
                                    $requiredTime
                                );
                            }
                        } elseif ( !empty($aspirants_employee_data) ) {
                            foreach ($aspirants_employee_data as $values) {
                                $surveyCode = bin2hex(
                                    openssl_random_pseudo_bytes("32")
                                );
                                $arrEventTypeData = [
                                    "employee_name" => $values['Name'],
                                 // "employee_department" => $values['Designation'],
                                    "email" => $values['Email'],
                                    "survey_id" => $surveyId,
                                    "event_typeid" => $eventTypeId,
                                    "event_date" => $addedDate,
                                    "added_date" => $addedDate,
                                    "added_by" => $addedBy,
                                    "modified_by" => $modifiedBy,
                                    "survey_code" => $surveyCode,
                                ];
                                $eventID = $this->_surveyEventsModelObject->saveData(
                                    $arrEventTypeData
                                );

                                $this->_emailObj->surveyInvitationMail(
                                    $eventID,
                                    $emailTemplateData,
                                    $emailSubject,
                                    $requiredTime
                                );
                            }
                        } else {
                            foreach ($all_employee_data as $keys => $values) {
                                if (
                                    in_array(
                                        $values->EmpCode,
                                        $valid_employee_keys
                                    )
                                ) {
                                    $surveyCode = bin2hex(
                                        openssl_random_pseudo_bytes("32")
                                    );
                                    $arrEventTypeData = [
                                        "employee_id" => $values->EmpCode,
                                        "employee_name" => $values->Resource,
                                        "employee_department" => $values->Designation,
                                        "email" => $values->Email,
                                        "survey_id" => $surveyId,
                                        "event_typeid" => $eventTypeId,
                                        "event_date" => $addedDate,
                                        "added_date" => $addedDate,
                                        "added_by" => $addedBy,
                                        "modified_by" => $modifiedBy,
                                        "survey_code" => $surveyCode,
                                    ];
                                    $eventID = $this->_surveyEventsModelObject->saveData(
                                        $arrEventTypeData
                                    );

                                    $this->_emailObj->surveyInvitationMail(
                                        $eventID,
                                        $emailTemplateData,
                                        $emailSubject,
                                        $requiredTime
                                    );
                                }
                            }
                        }

                        if ($get["test_email_status"] == 1) {
                            $this->_flashMessenger->addMessage([
                                "success" =>
                                    "Test email is now scheduled to be sent",
                            ]);
                        } else {
                            $this->_flashMessenger->addMessage([
                                "success" =>
                                    "Survey instance has been successfully created",
                            ]);
                        }
                        $this->_redirect(
                            $this->view->serverUrl() . "/survey/surveys/index"
                        );
                    }
                }
            }
        }

        $arrConfigVar = ["SURVEY_SELECTION_FILTERS"];

        if (isset($get["eventtypeid"]) && $get["eventtypeid"] != "") {
            $eventTypeId = $get["eventtypeid"];
        } else {
            $this->_flashMessenger->addMessage([
                "error" => "Invalid Survey ID!",
            ]);
            $this->_redirect($this->view->serverUrl() . "/survey/eventtype");
        }

        $configSurveySelectionFilters = $this->_config->getConfigQueIds(
            $arrConfigVar
        );
        $arrSelectionFilterData = unserialize(
            $configSurveySelectionFilters["SURVEY_SELECTION_FILTERS"]
        );

        $eventTypeData = $this->_surveyEventTypeObj->getEventTypesByID(
            $eventTypeId
        );
        $this->view->radioData = $arrSelectionFilterData;
        $survey_data = [
            "event_type" => $eventTypeId,
            "survey_category" => $eventTypeData[0]["survey_category_id"],
        ];
        /*$old_survey_data = $this->_surveyModelObject->getRecordsByEventType($eventTypeId,'survey_id DESC');
        if ( !empty($old_survey_data) && is_array($old_survey_data[0]) ) {
            $survey_data['survey_invite_text'] = $old_survey_data[0]['invite_content'];
            $survey_data['landing_page_message'] = $old_survey_data[0]['landing_page_content'];
            $survey_data['survey_thanks_message'] = $old_survey_data[0]['thanks_message'];
        }*/
        $form->populate($survey_data);

        $this->view->form = $form;
    }
    /**
     * Method to return events data for Ajax requests
     */
    private function _getByAjax($returnArray = false)
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $get = $this->getRequest()->getParams();

        //$aColumns = array('s.survey_id','s.survey_name','set.event_type','sc.survey_category_name','status','s.start_date','s.end_date');
        $aColumns = [
            "survey_id",
            "survey_name",
            "event_type",
            "survey_category_name",
            "",
            "status",
            "start_date",
            "end_date",
        ];
        if (!isset($get["ordercolumn"])) {
            if (is_numeric($get["order"][0]["column"])) {
                $field = $aColumns[$get["order"][0]["column"]];
                $sortby = $get["order"][0]["dir"] === "asc" ? "ASC" : "DESC";
                $sOrder = $field . " " . $sortby;
            } else {
                $sOrder = "";
            }
        } else {
            $field = $aColumns[$get["ordercolumn"]];
            $sortby = $get["orderdir"] === "asc" ? "ASC" : "DESC";
            $sOrder = $field . " " . $sortby;
        }

        if (isset($get["category"])) {
            $category = $get["category"];
        } else {
            $category = "";
        }

        if (isset($get["start_date"]) && !empty($get["start_date"])) {
            $startdate = explode("/", $get["start_date"]);
            $startdate =
                $startdate["2"] . "-" . $startdate["1"] . "-" . $startdate["0"];
        } else {
            $startdate = "";
        }

        if (isset($get["end_date"]) && !empty($get["end_date"])) {
            $enddate = explode("/", $get["end_date"]);
            $enddate =
                $enddate["2"] . "-" . $enddate["1"] . "-" . $enddate["0"];
        } else {
            $enddate = "";
        }
        if (isset($_GET["search_data"])) {
            $searchValue = $_GET["search_data"];
        } else {
            $searchValue = "";
        }

        $params = [
            "start" => isset($get["start"]) ? $get["start"] : "",
            "length" => isset($get["length"]) ? $get["length"] : "",
            "orderBy" => $sOrder, //Added By Amit kumar 16/09/14 3:46 PM for Sorting
            "category" => $category,
            "start_date" => $startdate,
            "end_date" => $enddate,
            "searchBy" => $searchValue,
        ];
        $result = $this->_surveyModelObject->getEventTypesData($params);
        $rowCount = count($this->_surveyModelObject->getCountData($params));

        if (count($result) > 0) {
            $data = [
                "draw" => isset($get["draw"]) ? $get["draw"] : "",
                "recordsTotal" => $rowCount, //$rowCount[0]['COUNT'], //$result[1][0]['tot'],
                "recordsFiltered" => $rowCount, //$rowCount[0]['COUNT'] //$result[1][0]['tot'],
            ];
            $data["data"] = [];

            foreach ($result as $value) {
                $where = ["event_typeid" => $value["event_typeid"]];
                $question_dtls = $this->_QuestionsModelObject->getQuestionDetails(
                    $where
                );
                //print_r($question_dtls);die;
                if (!empty($question_dtls[0]) && !empty($question_dtls[1])) {
                    if (
                        $this->view->hasAccess(
                            $this->_user->role_name,
                            "survey",
                            "eventtype",
                            "previewsurvey"
                        )
                    ) {
                        $preview_var =
                            '<a data-toggle="tooltip" target="_blank" data-placement="top" ' .
                            'title="Preview" href="' .
                            $this->view->serverUrl() .
                            "/survey/eventtype/previewsurvey/eventtypeid/" .
                            $value["event_typeid"] .
                            '" ' .
                            'class="view-icon">Preview Survey</a>';
                    }
                } else {
                    $preview_var = "";
                }

                $reminder_mail = $delete_survey = "";
                if (
                    $this->view->hasAccess(
                        $this->_user->role_name,
                        "survey",
                        "surveys",
                        "delete"
                    )
                ) {
                    $delete_survey =
                        '<a data-toggle="tooltip" data-placement="top" title="Delete Survey" ' .
                        'href="#" onclick="deleteSurvey(' .
                        $value["survey_id"] .
                        ')" class="delete-survey">' .
                        "Delete</a>";
                }
                if ($value["STATUS"] != "Closed") {
                    if (
                        $this->view->hasAccess(
                            $this->_user->role_name,
                            "survey",
                            "surveys",
                            "remindermail"
                        )
                    ) {
                        $reminder_mail =
                            "<a href='javascript:void(0)' data-id='" .
                            $value["survey_id"] .
                            "'" .
                            " data-survey='" .
                            base64_encode($value["survey_id"]) .
                            "' title='Reminder Emails'" .
                            " class=reminder-icon>Reminder Mail</a>";
                    }
                }

                if (
                    $this->view->hasAccess(
                        $this->_user->role_name,
                        "survey",
                        "surveys",
                        "remindermail"
                    )
                ) {
                    $edit_survey =
                        '<a data-toggle="tooltip" data-placement="top" title="Edit Survey" ' .
                        'href="' .
                        $this->view->serverUrl() .
                        "/survey/surveys/edit/id/" .
                        base64_encode($value["survey_id"]) .
                        '"' .
                        ' class="edit-icon">Edit</a>';
                }
                if (
                    strpos($value["event_status_comb"], "In progress") !==
                        false ||
                    strpos($value["event_status_comb"], "Closed") !== false
                ) {
                    $delete_survey = "";
                }

                if (
                    $this->view->hasAccess(
                        $this->_user->role_name,
                        "event",
                        "index",
                        "surveystatus"
                    )
                ) {
                    $survey_instance =
                        '<a href = "' .
                        $this->view->serverUrl() .
                        "/event/index/surveystatus/surveyid/" .
                        $value["survey_id"] .
                        '">' .
                        $value["survey_name"] .
                        "</a>";
                } else {
                    $survey_instance = $value["survey_name"];
                }

                if (
                    $this->view->hasAccess(
                        $this->_user->role_name,
                        "survey",
                        "eventtype",
                        "index"
                    )
                ) {
                    $survey_title =
                        '<a href = "' .
                        $this->view->serverUrl() .
                        '/survey/eventtype/">' .
                        $value["event_type"] .
                        "</a>";
                } else {
                    $survey_title = $value["event_type"];
                }

                $temp = [
                    $value["survey_id"],
                    $survey_instance,
                    $survey_title,
                    $value["survey_category_name"],
                    "Open - " .
                    $value["OPEN"] .
                    "<br />In Progress - " .
                    $value["IN Progress"] .
                    "<br />Closed  - " .
                    $value["Closed"] .
                    "<br />Total - " .
                    ($value["OPEN"] + $value["IN Progress"] + $value["Closed"]),
                    $value["STATUS"],
                    $value["start_date"],
                    $value["end_date"],
                    $reminder_mail .
                    $edit_survey .
                    $delete_survey .
                    $preview_var .
                    "",
                ];
                $data["data"][] = $temp;
            }
        } else {
            $data = [
                "draw" => isset($get["draw"]) ? $get["draw"] : "",
                "recordsTotal" => "0", //$result[1][0]['tot'],
                "recordsFiltered" => "0", //$result[1][0]['tot'],
            ];
            $data["data"] = [];
        }

        echo json_encode($data);
        exit();
    }

    /**
     * Method to handle delete action operations
     */
    public function deleteAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $get = $this->getRequest()->getParams();
        if (!isset($get["id"])) {
            $this->_flashMessenger->addMessage([
                "error" => "Survey ID not recieved!!",
            ]);
            $this->_redirect(
                $this->view->serverUrl() . "/survey/surveys/index"
            );
        }
        $surveyID = $get["id"];

        $isValidId = $this->_surveyModelObject->checkRecordExistByServeyID(
            $surveyID
        );
        //$isValidId = $this->_validateEventTypeId($eventTypeID);
        if (!$isValidId) {
            $this->_flashMessenger->addMessage([
                "error" => "Invalid Survey ID!",
            ]);
            $this->_redirect(
                $this->view->serverUrl() . "/survey/surveys/index"
            );
        }

        if (!is_numeric($surveyID)) {
            $this->_flashMessenger->addMessage([
                "error" => "Invalid Survey ID!",
            ]);
            $this->_redirect(
                $this->view->serverUrl() . "/survey/surveys/index"
            );
        }

        $this->_surveyModelObject->delete($surveyID);

        $this->_flashMessenger->addMessage([
            "success" => "Survey instance has been successfully Deleted",
        ]);

        $this->_redirect($this->view->serverUrl() . "/survey/surveys/index");
    }

    /**
     * Method to handle edit action operations
     * @author  Anuj
     * @date    31 May, 2014
     */
    public function editAction()
    {
        $surveyId = $this->_request->getParam("id", "");

        $surveyId = base64_decode($surveyId);

        if (!isset($surveyId)) {
            $this->_flashMessenger->addMessage([
                "error" => "Survey ID not recieved!!",
            ]);
            $this->_redirect(
                $this->view->serverUrl() . "/survey/surveys/index"
            );
        }

        if (!is_numeric($surveyId)) {
            $this->_flashMessenger->addMessage([
                "error" => "Invalid Survey ID!",
            ]);
            $this->_redirect(
                $this->view->serverUrl() . "/survey/surveys/index"
            );
        }

        $result = $this->_surveyModelObject->getSurveyByID($surveyId);

        if (count($result) == 0) {
            $this->_flashMessenger->addMessage([
                "error" => "Invalid Survey ID!",
            ]);
            $this->_redirect(
                $this->view->serverUrl() . "/survey/surveys/index"
            );
        }

        $eventTypeData = $this->_surveyEventTypeObj->getEventTypesByID(
            $result[0]["event_typeid"]
        );

        $this->view->SurveyData = $result;
        $form = new Survey_Form_Surveys();
        $form->event_type->setAttrib("disabled", "true");
        $form->start_date->setAttrib("disabled", "true");
        $form->populate([
            "survey_id" => $result[0]["survey_id"],
            "event_type" => $result[0]["event_typeid"],
            "survey_category" => $eventTypeData[0]["survey_category_id"],
            "survey_name" => $result[0]["survey_name"],
            "email_subject" => $result[0]["email_subject"],
            "start_date" => date(
                "d/m/Y",
                strtotime(implode("/", explode("-", $result[0]["start_date"])))
            ),
            "end_date" => date(
                "d/m/Y",
                strtotime(implode("/", explode("-", $result[0]["end_date"])))
            ),
        ]);

        //$form->populate($result);

        $get = $this->getRequest()->getParams();

        if ($this->_request->isPost()) {
            $message = "";

            $form->isValid($get);

            if (!$form->isValid($get)) {
                if (array_key_exists("survey_name", $form->getmessages())) {
                    $message .= "<br/>Please enter survey name";
                }
                // if (array_key_exists("survey_name", $form->getmessages())) {
                // $message.= "<br/>Please enter survey name";
                // }

                if (array_key_exists("start_date", $form->getmessages())) {
                    $message .= "<br/>Please enter start date";
                }

                if (array_key_exists("end_date", $form->getmessages())) {
                    $message .= "<br/>Please enter end date";
                }
                $this->view->messages = $message;
            } else {
                $isrecordExist = $this->_surveyModelObject->checkRecordExistById(
                    $get["survey_id"],
                    $get["event_type"],
                    $get["survey_name"]
                );

                if ($isrecordExist) {
                    $message .=
                        "<br/>Survey instance name is already associated with this survey.";
                    $this->view->messages = $message;
                } else {
                    $surveyId = $get["survey_id"];
                    $eventTypeId = $get["event_type"];
                    $surveyName = $get["survey_name"];
                    //$emailsubject = $get['email_subject'];
                    $emailsubject = $get["survey_invite_subject"];
                    $startDate = date(
                        "Y-m-d",
                        strtotime(
                            implode("-", explode("/", $get["start_date"]))
                        )
                    );
                    $endDate = date(
                        "Y-m-d",
                        strtotime(implode("-", explode("/", $get["end_date"])))
                    );
                    $modifiedBy = $this->_user->id;

                    $arrSurvey = [
                        "survey_id" => $surveyId,
                        "event_typeid" => $eventTypeId,
                        "survey_name" => $surveyName,
                        "start_date" => $startDate,
                        "email_Subject" => $emailsubject,
                        "end_date" => $endDate,
                        "modified_by" => $modifiedBy,
                    ];

                    $this->_surveyModelObject->saveData($arrSurvey);
                    $this->_flashMessenger->addMessage([
                        "success" =>
                            "Survey instance has been successfully updated",
                    ]);
                    $this->_redirect(
                        $this->view->serverUrl() . "/survey/surveys/index"
                    );
                }
            }
        }
        $this->view->form = $form;
    }

    /*
     * Methid to view a survey
     */

    /**
     * Method to handle edit action operations
     * @author  Anuj
     * @date    31 May, 2014
     */
    public function viewAction()
    {
        $surveyId = $this->_request->getParam("id", "");

        $surveyId = base64_decode($surveyId);

        if (!isset($surveyId)) {
            $this->_flashMessenger->addMessage([
                "error" => "Survey ID not recieved!!",
            ]);
            $this->_redirect(
                $this->view->serverUrl() . "/survey/surveys/index"
            );
        }

        if (!is_numeric($surveyId)) {
            $this->_flashMessenger->addMessage([
                "error" => "Invalid Survey ID!",
            ]);
            $this->_redirect(
                $this->view->serverUrl() . "/survey/surveys/index"
            );
        }

        $result = $this->_surveyModelObject->getSurveyByID($surveyId);
        $eventTypeData = $this->_surveyEventTypeObj->getEventTypesByID(
            $result[0]["event_typeid"]
        );

        if (count($result) == 0) {
            $this->_flashMessenger->addMessage([
                "error" => "Invalid Survey ID!",
            ]);
            $this->_redirect(
                $this->view->serverUrl() . "/survey/surveys/index"
            );
        }

        $this->view->SurveyData = $result;
        $form = new Survey_Form_Surveys();
        $form->event_type->setAttrib("disabled", "disabled");
        $form->survey_name->setAttrib("disabled", "disabled");
        $form->start_date->setAttrib("disabled", "disabled");
        $form->end_date->setAttrib("disabled", "disabled");
        $form->populate([
            "survey_id" => $result[0]["survey_id"],
            "event_type" => $result[0]["event_typeid"],
            "survey_name" => $result[0]["survey_name"],
            "survey_category" => $eventTypeData[0]["survey_category_id"],
            "start_date" => date(
                "d/m/Y",
                strtotime(implode("/", explode("-", $result[0]["start_date"])))
            ),
            "end_date" => date(
                "d/m/Y",
                strtotime(implode("/", explode("-", $result[0]["end_date"])))
            ),
        ]);
        $this->view->form = $form;
    }

    /**
     * Method to Authorize access for event ID
     * @param type $eventID
     */

    private function _validateEvent($eventID)
    {
        if ($this->_user->role_id != "1") {
            $result = $this->_eventModelObject->getWhere("dealer_id", [
                "eventid" => $eventID,
            ]);

            if (!isset($result[0])) {
                $this->_flashMessenger->addMessage([
                    "error" => $this->view->translate(
                        "Invalid event ID. Please try again"
                    ),
                ]);
                $this->_redirect($this->view->serverUrl() . "/event/index");
            }
            $session = new Zend_Session_Namespace("access_heirarchy");
            if (
                !in_array(
                    $result[0]["dealer_id"],
                    $session->accessHierarchy["dealers"]
                )
            ) {
                $this->_flashMessenger->addMessage([
                    "error" => $this->view->translate("Unauthorized access"),
                ]);
                $this->_redirect($this->view->serverUrl() . "/event/index");
            }
        } else {
            return true;
        }
    }

    public function getselectorAction()
    {
        //$url = 'http://172.29.8.74:7070/api/Service/GetMasterData/';
        $url =
            $this->_configValues["POST_API_URL"] . "api/Service/GetMasterData/";

        /*$data = array(
                    "key"=> "department",
                    "DivisionIdsList"=> array("0"),
                    "DepartmentIdsList"=> array("0"),
                    "DesignationIdsList"=> array("0"),
                    "RoleIdsList" => array("0"),
                    "LocationIdsList"=> array("0"),
                    "ProjectidIdsList"=> array("0")); */

        $data = $_POST["data"];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Content-Length: " . strlen(json_encode($data)),
        ]);

        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        //execute post
        $result = curl_exec($ch);
        if (!empty($result)) {
            echo $result;
            exit();
        } else {
            var_dump(curl_error($ch));
            die();
        }

        /*//$data =array();
        $options = array(
            'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'GET',
            'content' => http_build_query($_POST['data']),
            ),
        );
        
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        
        echo '<pre>';
        print_r($result);
        exit;
        
        if(isset($_POST['selector']) && $_POST['selector'] != '')
        {
            $rahul = array(
                            "key"=> "department",
                            "DivisionIdsList"=> array("1","0"),
                            "DepartmentIdsList"=> array("0"),
                            "DesignationIdsList"=> array("0"),
                            "RoleIdsList"=> array("0"),
                            "LocationIdsList"=> array("0"),
                            "ProjectidIdsList"=> array("0"));
            $rahuljson = json_encode($rahul);
            //echo $rahuljson;exit;
            //$response = file_get_contents('http://172.29.18.61:7070/NutBoltService/api/Service/GetMasterData?key='.$_POST['selector']);
            //$response = file_get_contents('http://172.29.18.61:7070/api/Service/GetMasterData?key='.$_POST['selector']);
            //$response = file_get_contents('http://172.29.10.234:8080/api/Service/GetMasterData?key='.$_POST['selector']);            
            echo '<pre>';
            print_r($response);
            exit;
            
            if(!empty($response))
            {
                echo $response;exit;
            }
        } */
    }

    public function getemployeedataAction()
    {
        $url =
            $this->_configValues["POST_API_URL"] .
            "api/employee/GetEmployeeData/";

        /*$data = array(
                    "key"=> "department",
                    "DivisionIdsList"=> array("0"),
                    "DepartmentIdsList"=> array("0"),
                    "DesignationIdsList"=> array("0"),
                    "RoleIdsList" => array("0"),
                    "LocationIdsList"=> array("0"),
                    "ProjectidIdsList"=> array("0")); */

        $data = $_POST["data"];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Content-Length: " . strlen(json_encode($data)),
        ]);

        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        //execute post
        $result = curl_exec($ch);

        if (!empty($result)) {
            echo $result;
            exit();
        }

        /*exit;
        $employeeData = array();
        
        $uniqueEmployeeData = array();
        $allEmployeeIDsWithDuplicate = array();
        $finalData = array();
        
        if(isset($_POST['data']) && $_POST['data'] != '')
        {
            
            foreach($_POST['data'] as $key=>$value)
            {
                //$emaployeeData[] = json_decode(file_get_contents('http://172.29.18.61:7070/api/Service/GetEmployeeData?key='.$key.'&keyvalue='.$value));
            }
            
            
            $i = 0;
            $totalCount = count($emaployeeData);
            
            foreach($emaployeeData as $data)
            {
                foreach($data as $employeeDetails)
                {
                    $uniqueEmployeeData[$employeeDetails->EmpCode] = (array)$employeeDetails;
                    $allEmployeeIDsWithDuplicate[] = $employeeDetails->EmpCode;
                }
                $i++;
            }
            
            $countOfIds = array_count_values($allEmployeeIDsWithDuplicate);
            foreach($countOfIds as $id=>$count)
            {
                if($count == $totalCount)
                    $finalData[] = $uniqueEmployeeData[$id];
            }
            
            echo json_encode($finalData);
            exit;
            
            
            
        } */

        //        if(isset($_POST['selector']) && $_POST['selector'] != '' && isset($_POST['key']) && $_POST['key'] != '')
        //        {
        //            //$response = file_get_contents('http://172.29.18.61:7070/NutBoltService/api/Service/GetEmployeeData?key='.$_POST['selector'].'&keyvalue='.$_POST['key']);
        //            $response = file_get_contents('http://172.29.18.61:7070/api/Service/GetEmployeeData?key='.$_POST['selector'].'&keyvalue='.$_POST['key']);
        //            //$response = file_get_contents('http://172.29.10.234:8080/api/Service/GetEmployeeData?key='.$_POST['selector'].'&keyvalue='.$_POST['key']);
        //
        //            if(!empty($response))
        //            {
        //                echo $response;exit;
        //            }
        //        }
    }

    /*
     * Methid to send reminder email
     */

    public function remindermailAction()
    {
        $surveyId = $this->_request->getParam("id", "");

        $surveyId = base64_decode($surveyId);

        if (!isset($surveyId)) {
            $this->_flashMessenger->addMessage([
                "error" => "Survey ID not recieved!!",
            ]);
            $this->_redirect(
                $this->view->serverUrl() . "/survey/surveys/index"
            );
        }

        if (!is_numeric($surveyId)) {
            $this->_flashMessenger->addMessage([
                "error" => "Invalid Survey ID!",
            ]);
            $this->_redirect(
                $this->view->serverUrl() . "/survey/surveys/index"
            );
        }

        $result = $this->_surveyEventsModelObject->getReminderMailById(
            $surveyId
        );

        if (empty($result)) {
            $this->_flashMessenger->addMessage([
                "error" =>
                    "No reminder was sent, All employees have already submitted their surveys.",
            ]);

            $this->_redirect(
                $this->view->serverUrl() . "/survey/surveys/index"
            );
        }

        $arrSurvey = [
            "survey_id" => $surveyId,
            "reminder_subject" => $this->_request->getParam("reminder_subject"),
            "reminder_content" => $this->_request->getParam("reminder_content"),
        ];
        $this->_surveyModelObject->saveData($arrSurvey);

        $emailTemplateData = $this->_emailTemplateObj->getDamcoInternalSurveyEmailTemplate(
            "remindermail"
        );
        $emailTemplateData[0]["content"] = $this->_request->getParam(
            "reminder_content"
        );
        //print_r($emailTemplateData);die;
        foreach ($result as $keys => $data) {
            $eventID = $data["eventid"];
            $emailSubject = $this->_request->getParam("reminder_subject");

            $this->_emailObj->surveyInvitationMail(
                $eventID,
                $emailTemplateData,
                $emailSubject,
                $requiredTime = "10",
                "remindermail"
            );
        }

        $this->_flashMessenger->addMessage([
            "success" => "Reminder mail has been successfully sent",
        ]);

        $this->_redirect($this->view->serverUrl() . "/survey/surveys/index");
    }

    public function getValidatecsvDataAction()
    {
        $url =
            $this->_configValues["POST_API_URL"] . "api/Service/GetMasterData/";
        $LocationIdsList = [];
        /*$data = array(
                    "key"=> "Location",
                    "DivisionIdsList"=> array("0"),
                    "DepartmentIdsList"=> array("0"),
                    "DesignationIdsList"=> array("0"),
                    "RoleIdsList" => array("0"),
                    "LocationIdsList"=> array("0"),
                    "ProjectidIdsList"=> array("0")); 
        */
        $data = [
            "DivisionIdsList" => ["0" => "0"],
            "DepartmentIdsList" => ["0" => "0"],
            "LocationIdsList" => ["0" => "0"],
            "RoleIdsList" => ["0" => "0"],
            "DesignationIdsList" => ["0" => "0"],
            "ProjectsIdsList" => ["0" => "0"],
            "key" => "Location",
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Content-Length: " . strlen(json_encode($data)),
        ]);

        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        //execute post
        $result = curl_exec($ch);
        if (!empty($result)) {
            foreach (json_decode($result) as $keys => $values) {
                $LocationIdsList[] = $values->Key;
            }
        }

        $data = [
            "DivisionIdsList" => ["0" => "0"],
            "DepartmentIdsList" => ["0" => "0"],
            "LocationIdsList" => $LocationIdsList,
            "RoleIdsList" => ["0" => "0"],
            "DesignationIdsList" => ["0" => "0"],
            "ProjectsIdsList" => ["0" => "0"],
            "key" => "employee",
        ];

        $emp_url =
            $this->_configValues["POST_API_URL"] .
            "api/employee/GetEmployeeData/";

        $ch = curl_init($emp_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Content-Length: " . strlen(json_encode($data)),
        ]);

        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        //execute post
        $result = curl_exec($ch);
        if (!empty($result)) {
            return json_decode($result);
            exit();
        } else {
            echo curl_error($ch);
            die();
        }
    }

    public function getreminderAction()
    {
        $surveys_data = $this->_surveyModelObject->getSurveyByID(
            $this->_request->getParam("surveyid")
        );
        print json_encode($surveys_data);
        exit();
    }
}
