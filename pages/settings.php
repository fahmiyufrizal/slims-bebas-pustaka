<?php
/**
 * @Created by          : Drajat Hasan
 * @Date                : 2022-06-08 13:47:37
 * @File name           : index.php
 */

use SLiMS\DB;

defined('INDEX_AUTH') OR die('Direct access not allowed!');

require SB . 'admin/default/session_check.inc.php';
require_once SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require_once SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';

/* RECORD FORM */
ob_start();
// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'post');
$form->submit_button_attr = 'name="saveData" value="' . __('Update') . '" class="s-btn btn btn-default"';
// form table attributes
$form->table_attr = 'id="dataList" cellpadding="0" cellspacing="0"';
$form->table_header_attr = 'class="alterCell"';
$form->table_content_attr = 'class="alterCell2"';

$config = config('bebas_pustaka', [
    'fields' => [
        'letternumber' => null,
        'openstate' => null,
        'closestate' => null,
        'city' => null,
        'librarian_position' => null,
        'librarian' => null,
        'numid' => null,
        'signature' => 'data:' . mime_content_type(getBaseDir('static/signature.png')) . ';base64, ' . base64_encode(getStatic('signature.png')),
        'headerimage' => 'data:' . mime_content_type(getBaseDir('static/header.png')) . ';base64, ' . base64_encode(getStatic('header.png')),
    ],
    'default_provider' => [
        'MyDompdf', \BebasPustaka\Providers\Dompdf::class
    ]
]);

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
$content  = ob_get_clean();
$content .= <<<HTML
<script>
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