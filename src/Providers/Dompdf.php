<?php
namespace BebasPustaka\Providers;

use Closure;
use SLiMS\Pdf\Contract;
use Dompdf\Dompdf as Core;
use Carbon\Carbon;

class Dompdf extends Contract
{
    public static string $name = 'Dompdf';
    public function setPdf():void
    {
        $this->pdf = new Core;
    }

    /**
     * Get template default formatter
     *
     * @return array
     */
    public function getDefaultFormat():array
    {
        $romanFormat = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        return [
            'rm' => $romanFormat[date('n')],
            'm' => date('n'),
            'zm' => date('d'),
            'y' => date('Y'),
            'date' => Carbon::parse(date('Y-m-d'))->locale('id_ID')->isoFormat('LL')
        ];
    }

    /**
     * Prepare and setup your content in 
     * this method. Create it and generate!
     *
     * @param array $data
     * @return self
     */
    public function setContent(array $data = []):self
    {
        // Template
        $currentTemplate = config('bebas_pustaka.default_template', 'default.html');

        // Get dummy config if main config is not set
        $fields = config('bebas_pustaka.fields', [
            'letternumber' => 'nomor surat belum diatur',
            'openstate' => 'kalimat pembuka belum diatur',
            'closestate' => 'kalimat penutup belum diatur',
            'city' => 'Nama kota belum diatur',
            'librarian_position' => 'Posisi Pustakawan belum diatur',
            'librarian' => 'Nama pustakawan belum diatur',
            'numid' => 'NIK/NIP Pustakwan belum diatur'
        ]);

        // Static property such as header image and signature
        $fields = array_merge($fields, [
            'signature' => 'data:' . mime_content_type(getBaseDir('static/signature.png')) . ';base64, ' . base64_encode(getStatic('signature.png')),
            'headerimage' => 'data:' . mime_content_type(getBaseDir('static/header.png')) . ';base64, ' . base64_encode(getStatic('header.png')),
        ]);

        /**
         * Content processing.
         * 
         * parsing data and another format
         */
        $content = '';
        if (count($data)) {
            foreach ($data as $order => $collection) {
                // Combine data, default fields and static format
                $collection = array_merge($collection, $fields, $this->getDefaultFormat(), getOrder($collection['member_id'],$fields['letternumber']));

                // Concating content if the data is multiple
                $content .= parseToTemplate(getTemplate($currentTemplate), $collection);

                // set page break if the data is more than one
                if (count($data) > 1 && $order !== array_key_last($data)) {
                    $content .= '<div style="page-break-before: always;"></div>';
                }
            }
        } else {
            // just for preview is only need $fields
            $content .= parseToTemplate(getTemplate($currentTemplate), $fields);
        }

        // Finnaly throw processed content to the PDF Generator
        $this->pdf->loadHtml(parseToTemplate(getTemplate('layout.html'), ['content' => $content]));
        return $this;
    }

    /**
     * Generate PDF output and download it
     * as file
     *
     * @param string $filname
     * @return void
     */
    public function download(string $filename):void
    {
        $this->stream($filename, ['Attachment' => true]);
    }
    
    /**
     * Stream your PDF report on browser
     * without download it
     *
     * @param string|null $filname
     * @param array|null $options
     * @return void
     */
    public function stream(?string $filename = null, ?array $options = null):void
    {
        $this->pdf->render();
        $this->pdf->stream(($filename??md5('this') . 'pdf'), ($options??['Attachment' => false]));
        exit;
    }
    
    /**
     * Generate PDF to save it into
     * file.
     *
     * @param string $filepath
     * @param Closure|null $callback
     * @return void
     */
    public function saveToFile(string $filepath, ?Closure $callback = null):void
    {
        $this->pdf->render();
        if ($callback !== null) {
            $callback($this->pdf, $filepath);
        } else {
            file_put_contents($filepath, $this->pdf->output());
            exit;
        }
    }
}