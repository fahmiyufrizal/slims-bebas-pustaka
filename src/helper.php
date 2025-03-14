<?php
use SLiMS\DB;
use SLiMS\Plugins;

if (!function_exists('getBaseDir')) {
    function getBaseDir(string $additionalPath = '') {
        return dirname(__DIR__) . DS . str_replace('..', '', $additionalPath);
    }
}

if (!function_exists('getProviders')) {
    function getProviders()
    {
        $providers = scandir(getBaseDir('src' . DS . 'Providers'));
        Plugins::run('bebas_pustaka_provdier_init', [&$providers]);

        return array_diff($providers, ['.','..']);
    }
}

if (!function_exists('getTemplates')) {
    function getTemplates()
    {
        $templates = scandir(getBaseDir('template' . DS));
        Plugins::run('bebas_pustaka_template_init', [&$templates]);

        // filtering only html file
        $templates = array_filter($templates, function($template) {
            // by pass layout
            if (strpos($template, 'layout.html') !== false) return false;

            // only template with .html format
            return strpos($template, '.html') !== false ? str_replace('.php', '', $template) : false;
        });

        return array_diff($templates, ['.','..']);
    }
}

if (!function_exists('getDirname')) {
    function getDirname() {
        return basename(getBaseDir());
    }
}

if (!function_exists('getTemplate')) {
    function getTemplate(string $filname) {
        if (count(explode('/', trim($filname))) > 1) {
            $path = $filname;
        } else {
            $path = getBaseDir('template/' . $filname);
        }

        return file_exists($path) ? file_get_contents($path) : null;
    }
}

if (!function_exists('getOrder')) {
    function getOrder($memberId, string $format) {
        $result = [
            'no' => ''
        ];

        Plugins::run('bebas_pustaka_order_init', [&$result]);

        if (empty($result['no'])) {
            $db = DB::getInstance();
            $state = $db->prepare('INSERT IGNORE INTO `bebas_pustaka_history` (`member_id`, `letter_number_format`, `created_at`) VALUES (?, ?, NOW())');
            $state->execute([$memberId, $format]);
        
            if ($db->lastInsertId() > 0) {
                $result['no'] = str_pad($db->lastInsertId(), 3, '0', STR_PAD_LEFT); // ganti digit ke 3
            } else {
                $state = $db->prepare('SELECT `id` FROM `bebas_pustaka_history` WHERE `member_id` = ?');
                $state->execute([$memberId]);
        
                if ($state->rowCount() < 1) return ['no' => '000']; // memastikan 3 digit
        
                $data = $state->fetchObject();
                $result['no'] = str_pad($data->id, 3, '0', STR_PAD_LEFT); // memastikan 3 digit
            }
        }



        return $result;
    }
}

if (!function_exists('getStatic')) {
    function getStatic(string $filname) {
        return file_exists($path = getBaseDir('static/' . $filname)) ? file_get_contents($path) : null;
    }
}

if (!function_exists('parseToTemplate')) {
    function parseToTemplate(string $template, array $data) {
        foreach ($data as $key => $value) {
            $template = str_replace('{'.$key.'}', $value??'', $template);
        }

        return $template;
    }
}
