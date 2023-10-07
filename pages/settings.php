<?php
/**
 * @Created by          : Drajat Hasan
 * @Date                : 2022-06-08 13:47:37
 * @File name           : index.php
 */

use SLiMS\DB;
use SLiMS\Filesystems\Storage;
use SLiMS\Pdf\Factory;
use SLiMS\Config;

defined('INDEX_AUTH') OR die('Direct access not allowed!');

require SB . 'admin/default/session_check.inc.php';
require_once SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require_once SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';

$defaultConfig = [
    'fields' => [
        'letternumber' => null,
        'openstate' => null,
        'closestate' => null,
        'city' => null,
        'librarian_position' => null,
        'librarian' => null,
        'numid' => null
    ],
    'default_provider' => [
        'MyDompdf', \BebasPustaka\Providers\Dompdf::class
    ]
];

if (isset($_POST['saveData'])) {
    $pluginDir = getDirname();
    $pluginStorage = Storage::plugin();
    foreach ($_FILES as $name => $detail) {
        if (empty($_FILES[$name]['name'])) continue;

        $pluginStorage->upload($name, function($pluginStorage) use($sysconf) {
            // Extension check
            $pluginStorage->isExtensionAllowed($sysconf['allowed_images']);

            // destroy it if failed
            if (!empty($pluginStorage->getError())) $pluginStorage->destroyIfFailed();

            // remove exif data
            if (empty($pluginStorage->getError())) $pluginStorage->cleanExifInfo();
        })->as($pluginDir . DS . 'static' . DS . str_replace('image', '', $name));
    }
    
    foreach ($_POST['fields'] as $key => $value) {
        // if (!isset($defaultConfig['fields'][$key])) continue;
        $defaultConfig['fields'][$key] = $value;
    }

    $providerClass = $_POST['provider'];
    $defaultConfig['default_provider'] = [
        $providerClass::$name,
        $providerClass
    ];

    $defaultConfig['default_template'] = $_POST['template'];

    Config::createOrUpdate('bebas_pustaka', $defaultConfig);

    echo <<<HTML
    <script>
        parent.$( '#preview' ).attr( 'src', function ( i, val ) { return val; });
    </script>
    HTML;
    exit;
}

$config = config('bebas_pustaka', $defaultConfig);

$config['fields'] = array_merge($config['fields'], [
    'signature' => 'data:' . mime_content_type(getBaseDir('static/signature.png')) . ';base64, ' . base64_encode(getStatic('signature.png')),
    'headerimage' => 'data:' . mime_content_type(getBaseDir('static/header.png')) . ';base64, ' . base64_encode(getStatic('header.png')),
]);

if (isset($_GET['preview'])) {
    // Register provider
    $provider = config('bebas_pustaka.default_provider', [
        'MyDompdf', \BebasPustaka\Providers\Dompdf::class
    ]);
    Factory::registerProvider(...$provider);

    Factory::useProvider($provider[0]);
    Factory::preview();
    exit;
}

/* RECORD FORM */
ob_start();
// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'post');
$form->submit_button_attr = 'name="saveData" value="' . __('Update') . '" class="s-btn btn btn-default"';
// form table attributes
$form->table_attr = 'id="dataList" cellpadding="0" cellspacing="0"';
$form->table_header_attr = 'class="alterCell"';
$form->table_content_attr = 'class="alterCell2"';

// Provider l ist
$form->addSelectList('provider', __('Provider'), array_map(function($provider){
    $class = 'BebasPustaka\Providers\\' . str_replace('.php', '', $provider);
    return [
        $class,
        $class::$name
    ];
}, getProviders()), $config['default_provider'], 'class="form-control"', 'Pilih default provider');

// Provider l ist
$form->addSelectList('template', __('Template'), array_map(function($template){
    return [
        $template,
        ucfirst(str_replace('.html', '', $template))
    ];
}, getTemplates()), $config['default_template']??'default.html', 'class="form-control"', 'Pilih default provider');

// Fields
foreach ($config['fields'] as $label => $value) {
    if (substr($value??'', 0,5) !== 'data:' && !in_array($label, ['openstate','closestate'])) {
        $form->addTextField('text', 'fields[' . $label . ']', $label, $value??'', 'class="form-control" style="width: 100%;"', '');   
    } else if (in_array($label, ['openstate','closestate'])) {
        $form->addTextField('textarea', 'fields[' . $label . ']', $label, $value??'', 'class="form-control" style="width: 100%;"', '');   
    } else {
        $str_input = '<div class="row">';
        $str_input .= '<div class="col-12">';
        $str_input .= '<div id="imageFilename" class="s-margin__bottom-1">';
        $str_input .= '<img src="' . $value .'" id="' . $label . '" class="d-block img-fluid rounded" alt="Image cover">';
        $str_input .= '</div>';
        $str_input .= '</div>';
        $str_input .= '<div class="custom-file col-12">';
        $str_input .= simbio_form_element::textField('file', $label, '', 'data-img="#' . $label . '" class="custom-file-input" id="customFile"');
        $str_input .= '<label class="custom-file-label" for="customFile">' . __('Choose file') . '</label>';
        $str_input .= '</div>';
        $str_input .= ' <div class="mt-2 ml-2">Maximum ' . config('max_image_upload') . ' KB</div>';
        $str_input .= '</div>';
        $str_input .= '<textarea id="base64picstring" name="base64picstring" style="display: none;"></textarea>';
        $str_input .= '</div></div></div>';
        $form->addAnything($label, $str_input);
    }
}

echo $form->printOut();
$formOutput = ob_get_clean();

$previewUrl = $_SERVER['PHP_SELF'] . '?' . httpQuery(['preview' => 'ok']);
$content = <<<HTML
<div class="d-flex flex-row">
    <div class="col-4" style="height: 100vh; overflow-y: auto; overflow-x: hidden">
        {$formOutput}
    </div>
    <div class="col-8">
        <iframe id="preview" src="{$previewUrl}" style="height: 100vh; width: 100%"></iframe>
    </div>
</div>
HTML;
$content .= <<<HTML
<script>
    $(document).ready(function() {
        // setTimeout(() => {
            // $( '#preview' ).attr( 'src', function ( i, val ) { return val; });
        // }, 1500);
    })

    $(document).on('change', '.custom-file-input', function () {
        // $('img').attr('src',document.getElementById("image").files[0].name);
        var input = $(this);
        var fReader = new FileReader();
        fReader.readAsDataURL(input[0].files[0]);
        fReader.onloadend = function (event) {
            $(input.data('img')).attr('src',event.target.result)
        }
        // let fileName = $(this).val().replace(/\\/g, '/').replace(/.*\//, '');
        // // $(this).parent('.custom-file').find('.custom-file-label').text(fileName);
    });
</script>
HTML;
include SB . 'admin' . DS . 'admin_template' . DS . 'notemplate_page_tpl.php';