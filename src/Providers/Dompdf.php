<?php
namespace BebasPustaka\Providers;

use Closure;
use SLiMS\Pdf\Contract;
use Dompdf\Dompdf as Core;
use Carbon\Carbon;

class Dompdf extends Contract
{
    protected string $name = 'Dompdf';
    public function setPdf():void
    {
        $this->pdf = new Core;
    }

    public function setContent(array $data = []):self
    {
        $currentTemplate = config('bebas_pustaka.default_template', 'default.html');
        $fields = config('bebas_pustaka.fields', [
            'letternumber' => 'nomor surat belum diatur',
            'openstate' => 'kalimat pembuka belum diatur',
            'closestate' => 'kalimat penutup belum diatur',
            'city' => 'Nama kota belum diatur',
            'librarian_position' => 'Posisi Pustakawan belum diatur',
            'librarian' => 'Nama pustakawan belum diatur',
            'numid' => 'NIK/NIP Pustakwan belum diatur'
        ]);

        $fields = array_merge($fields, [
            'signature' => 'data:' . mime_content_type(getBaseDir('static/signature.png')) . ';base64, ' . base64_encode(getStatic('signature.png')),
            'headerimage' => 'data:' . mime_content_type(getBaseDir('static/header.png')) . ';base64, ' . base64_encode(getStatic('header.png')),
        ]);

        $content = '';
        if (count($data)) {
            foreach ($data as $order => $collection) {
                $collection = array_merge($collection, $fields, ['date' => Carbon::parse(date('Y-m-d'))->locale('id_ID')->isoFormat('LL')]);
                $content .= parseToTemplate(getTemplate($currentTemplate), $collection);
                if (count($data) > 1 && $order !== array_key_last($data)) {
                    $content .= '<div style="page-break-before: always;"></div>';
                }
            }
        } else {
            $content .= parseToTemplate(getTemplate($currentTemplate), $fields);
        }

        $this->pdf->loadHtml(parseToTemplate(getTemplate('layout.html'), ['content' => $content]));
        return $this;
    }

    public function download(string $filename):void
    {
        $this->stream($filename, ['Attachment' => true]);
    }
    
    public function stream(?string $filename = null, ?array $options = null):void
    {
        $this->pdf->render();
        $this->pdf->stream(($filename??md5('this') . 'pdf'), ($options??['Attachment' => false]));
        exit;
    }
    
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