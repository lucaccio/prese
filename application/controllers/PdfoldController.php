<?php

class PdfController extends Zend_Controller_Action {
    
    
    
    
    
    public function init() {
        $this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();
    //set content for view here

    
                

        
        
    }
    
    public function stampaAction() {
     
        // create new PDF document        
    require_once('../library/tcpdf/tcpdf.php');
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 

    //whole TCPDF's settings goes here

    $htmlcontent = $this->view->render('calendario/index.phtml');
    // output the HTML content
    
      
    $pdf->writeHTML($htmlcontent, true, 0, true, 0);
   // $pdf->lastPage();
    $pdf->Output("pdf-name.pdf", 'D');
       
      /*  
        require_once("../library/dompdf/dompdf_config.inc.php");

$html =
  '<html><body>'.
  '<p>Put your html here, or generate it with your favourite '.
  'templating system.</p>'.
  '</body></html>';

$dompdf = new DOMPDF();
$dompdf->load_html($html);
$dompdf->render();
$dompdf->stream("sample.pdf");
        
        */
        
        
    }
    
    
    
}
