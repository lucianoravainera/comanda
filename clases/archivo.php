<?php
    //require_once ("Producto.php");
    require('fpdf/fpdf.php');


class Archivo
{

    public static function moveUploadedFile($uploadedFile, $email, $destino)
    {
        if (!file_exists($destino)) {
            mkdir($destino);
        }
        
        $email = str_replace(" ", "_", $email);
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $filename = trim($email) .'.'. $extension;

        $ruta = $destino . $filename;
        $uploadedFile->moveTo($ruta);
        
        return $ruta;
    }

    public static function moverFotoABackup($pathviejo, $email, $destino)
    {
        if (!file_exists($destino)) {
            mkdir($destino);
        }
        $extension = pathinfo($pathviejo, PATHINFO_EXTENSION);
        rename($pathviejo, "./ProductosBorrados/".trim($email)."-".date("Ymd_His").".".$extension);
    }

    public static function exportarExcel($array, $nombreArchivo)
    {
        //$array = estacionamiento::TraerDatosEstacionamiento();
        if (!empty($array)) {
            $objPHPExcel = new PHPExcel();
                    
            $objPHPExcel->getActiveSheet()->getStyle("A1:T1")->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()
            ->getStyle('A1:T1')
            ->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor();
            //->setARGB('5bc0de');


            //Informacion del excel
            $objPHPExcel->
             getProperties()
                 ->setCreator("Sabrina")
                 ->setTitle("Exportar Excel")
                 ->setSubject("Estacionamiento")
                 ->setDescription("Documento generado con PHPExcel");
                
            $row=1;
            $col=0;
            foreach ($array as $elemento) {
                foreach ($elemento as $key => $value) {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, $key);
                    $col++;
                }
                break;
            }
            $row=2;
            $col=0;
            foreach ($array as $elemento) {
                foreach ($elemento as $key => $value) {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, $value);
                    $col++;
                }
                $col=0;
                $row++;
            }
        }
        //header('Content-Type: application/vnd.ms-excel');
        header("Content-type: application/octet-stream");
        header('Content-Disposition: attachment;filename="'.$nombreArchivo.'.xlsx"');
        header("Pragma: no-cache");
        header("Expires: 0");
        header('Cache-Control: max-age=0');
        
                 
        $objWriter=PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        // ob_start();
        // $xlsData = ob_get_contents();
        // ob_end_clean();
        
        // $response =  array(
        //         'op' => 'ok',
        //         'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData)
        //     );
        // die(json_encode($response));
        exit;
    }

    public static function exportarPDF($array, $titulo)
    {
        $textColour = array( 0, 0, 0 );
        $tableHeaderTopProductTextColour = array( 0, 0, 0 );
        $tableHeaderTopProductFillColour = array( 143, 173, 204 );
        $tableHeaderLeftTextColour = array( 60, 42, 57 );
        $tableHeaderLeftFillColour = array( 184, 207, 229 );
        $tableBorderColour = array( 50, 50, 50 );
        $tableRowFillColour = array( 213, 170, 170 );
        $reportNameYPos = 20;

        $pdf = new FPDF( 'P', 'mm', 'A4' );
        $pdf->AddPage();

        $pdf->SetFont( 'Arial', 'B', 14 );
        //$pdf->Write( 19, $titulo);
        $pdf->Ln( $reportNameYPos );
        $pdf->SetDrawColor( $tableBorderColour[0], $tableBorderColour[1], $tableBorderColour[2] );
        $pdf->SetFont( 'Arial', 'B', 10 );
        $pdf->SetTextColor( $tableHeaderTopProductTextColour[0], $tableHeaderTopProductTextColour[1], $tableHeaderTopProductTextColour[2] );
        $pdf->SetFillColor( $tableHeaderTopProductFillColour[0], $tableHeaderTopProductFillColour[1], $tableHeaderTopProductFillColour[2] );
                
        $row=1;
        $col=0;
        foreach ($array as $elemento) {
            foreach ($elemento as $key => $value) {
               // $pdf->Cell(30, 9, $key, 0, 0, 'C');
                $pdf->Cell(32, 9, $key, 1, 0, 'C', true);
                $col++;
            }
            break;
        }


        $row=2;
        $col=0;
        $pdf->SetTextColor( $textColour[0], $textColour[1], $textColour[2] );
        $pdf->SetFillColor( $tableRowFillColour[0], $tableRowFillColour[1], $tableRowFillColour[2] );
        $fill = false;
        $pdf->Ln( 10 );
        foreach ($array as $elemento) {
            $pdf->SetTextColor( $tableHeaderLeftTextColour[0], $tableHeaderLeftTextColour[1], $tableHeaderLeftTextColour[2] );
            $pdf->SetFillColor( $tableHeaderLeftFillColour[0], $tableHeaderLeftFillColour[1], $tableHeaderLeftFillColour[2] );
            $pdf->SetFont('Arial', '', 8);
            foreach ($elemento as $key => $value) {
                $pdf->Cell(32, 9, $value, 1, 0, 'C', $fill);
                $col++;
            }
            $col=0;
            $row++;
            $fill = !$fill;
            $pdf->Ln( 10 );
        }
        $path = $titulo.".pdf";
        header('Content-Type: application/application/pdf');
        header('Content-Disposition: attachment;filename='.$path. "'");
        $pdf->Output('D', $path);
    }
}
