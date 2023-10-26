<?php
//require_once(DOCUMENT_ROOT.'/public/tcpdf/tcpdf.php');
class Damco_Tcpdf_Mytcpdf extends Damco_Tcpdf_Tcpdf {
	
	protected $_logo;
	protected $_company;
	protected $_date;

	public function setHeaderHTML($logo='', $companyName='', $date='')
	{
		$this->_logo = $logo;
		$this->_company = $companyName;
		$this->_date = $date;
	}	
   public function Header() {
        // Logo
		if($this->_logo!=''){
			$image_file = $this->_logo;//'images/logo.png'; // *** Very IMP: make sure this image is available on given path on your server
			$this->Image($image_file,0,0,0);
			// Set font
			$this->SetFont('helvetica', 'C', 12);
		
			// Line break
			$this->Ln(30);    
		}
		
		$this->SetFillColor(21,42,84);	
		$this->SetTextColor(256, 256, 256);
		//$this->SetMargins(0, PDF_MARGIN_TOP, 0);
		$this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP+20, 0, true);
		$this->SetX(0);
		
		if($this->_company!=''){
			$this->SetFont('helvetica', '', 14);
			$this->Cell(2, 10, '', 0, false, 'L', 1, '', 0, false, 'M', 'M');
			$this->Cell(0, 10, $this->_company, 0, false, 'L', 1, '', 0, false, 'M', 'M');
		}
		if($this->_date!=''){
			//$this->Ln(5);
			$this->SetFont('helvetica', '', 10);	
			$this->Cell(0, 10, $this->_date.'  ', 0, false, 'R', 1, '', 0, false, 'M', 'M');
			
		}
    }

}