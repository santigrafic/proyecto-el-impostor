<?php

namespace App\Services;

use Mpdf\Mpdf;

class PdfService
{
    protected Mpdf $mpdf;
    public function __construct()
    {
        $this->mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 24,
            'margin_bottom' => 16,
            'margin_left' => 15,
            'margin_right' => 15,
        ]);
    }
    public function render(string $view, array $data = []): string
    {
        $html = view($view, $data)->render();
        $this->mpdf->WriteHTML($html);
        return $this->mpdf->Output('', 'S'); // devuelve string
    }
}