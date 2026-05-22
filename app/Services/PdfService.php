<?php

namespace App\Services;

use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;

class PdfService
{
    protected Mpdf $mpdf;

    public function __construct()
    {
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $this->mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 24,
            'margin_bottom' => 16,
            'margin_left' => 15,
            'margin_right' => 15,

            'fontDir' => array_merge($fontDirs, [
                storage_path('fonts')
            ]),

            'fontdata' => $fontData + [
                'pressstart2p' => [
                    'R' => 'PressStart2P-Regular.ttf',
                ]
            ],

            'default_font' => 'pressstart2p'
        ]);
    }
    public function render(string $view, array $data = []): string
    {
        $html = view($view, $data)->render();
        $this->mpdf->WriteHTML($html);
        return $this->mpdf->Output('', 'S'); // devuelve string
    }
}