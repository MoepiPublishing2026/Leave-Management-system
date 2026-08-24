<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;

class PdfDocuments
{
    public static function make(string $view, array $data = []): DomPdfWrapper
    {
        $chroot = [public_path()];
        $windowsFonts = 'C:\\Windows\\Fonts';
        if (is_dir($windowsFonts)) {
            $chroot[] = $windowsFonts;
        }

        $pdf = Pdf::loadView($view, $data);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('chroot', $chroot);
        $pdf->setOption('defaultFont', 'DejaVu Sans');
        $pdf->getDomPDF()->setBasePath(public_path());

        return $pdf;
    }
}
