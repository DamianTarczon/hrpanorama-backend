<?php

namespace Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use TCPDF;

class PDFService
{
  public function createPDFWithQRCode(string $text, string $url): ?string
  {
    $options = new QROptions([
      'outputType' => QRCode::OUTPUT_IMAGE_PNG,
      'eccLevel' => QRCode::ECC_L,
    ]);

    $qrCode = new QRCode($options);
    $qrCodeFilePath = sys_get_temp_dir() . '/qr_code_' . uniqid() . '.png';
    $qrCode->render($url, $qrCodeFilePath);

    $pdf = new TCPDF();
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();

    $pdf->SetMargins(10, 10, 10, true);

    $logoPath = __DIR__ . '/../images/logo.png';
    $logoWidth = 62;
    $logoHeight = 10;

    $pdf->Image($logoPath, 10, 10, $logoWidth, $logoHeight, 'PNG');

    $pdf->SetFont('dejavusans', 'B', 16);
    $pdf->SetY($logoHeight + 20);
    $pdf->MultiCell(0, 10, $text, 0, 'C', false, 1, '', '', true, 0, false, true, 0, 'T', false);

    $qrCodeSize = 50;
    $x = ($pdf->getPageWidth() - $qrCodeSize) / 2;
    $y = $pdf->GetY() + 10;
    $pdf->Image($qrCodeFilePath, $x, $y, $qrCodeSize, $qrCodeSize, 'PNG');

    $pdfFileName = uniqid() . '.pdf';
    $pdfFilePath = __DIR__ . '/../temp/' . $pdfFileName;
    $pdf->Output($pdfFilePath, 'F');

    unlink($qrCodeFilePath);

    if (file_exists($pdfFilePath)) {
      return 'temp/' . $pdfFileName;
    }

    return null;
  }
}
