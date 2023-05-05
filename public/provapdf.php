<?php



   ob_start();
   $homepage = file_get_contents('http://192.168.1.106/feriemanager/public/index.php/calendario/daily');
    echo $homepage;

    $content = ob_get_clean();

    // convert to PDF
    require_once(dirname(__FILE__).'/html2pdf.class.php');
    try
    {
        $html2pdf = new HTML2PDF('P', 'A4', 'fr');
        $html2pdf->pdf->SetDisplayMode('fullpage');
        
        $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
        $html2pdf->Output('exemple04.pdf');
    }
    catch(HTML2PDF_exception $e) {
        echo $e;
        exit;
    }
