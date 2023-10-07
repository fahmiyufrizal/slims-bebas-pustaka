<?php
use SLiMS\DB;

if (!function_exists('getBaseDir')) {
    function getBaseDir(string $additionalPath = '') {
        return dirname(__DIR__) . DS . str_replace('..', '', $additionalPath);
    }
}

if (!function_exists('getProviders')) {
    function getProviders()
    {
        return array_diff(scandir(getBaseDir('src' . DS . 'Providers')), ['.','..']);
    }
}

if (!function_exists('getTemplates')) {
    function getTemplates()
    {
        return array_diff(scandir(getBaseDir('template' . DS)), ['.','..','layout.html']);
    }
}

if (!function_exists('getDirname')) {
    function getDirname() {
        return basename(getBaseDir());
    }
}

if (!function_exists('getTemplate')) {
    function getTemplate(string $filname) {
        return file_exists($path = getBaseDir('template/' . $filname)) ? file_get_contents($path) : null;
    }
}

if (!function_exists('getOrder')) {
    function getOrder($memberId, string $format) {
        $db = DB::getInstance();
        $state = $db->prepare('insert ignore into `bebas_pustaka_history` set `member_id` = ?, `letter_number_format` = ?, `created_at` = now()');
        $state->execute([$memberId, $format]);

        if ($db->lastInsertId() > 0) {
            return ['no' => $db->lastInsertId()];
        } else {
            $state = $db->prepare('select `id` from `bebas_pustaka_history` where `member_id` = ?');
            $state->execute([$memberId]);
            
            if ($state->rowCount() < 1) return ['no' => 0];

            $data = $state->fetchObject();
            return ['no' => $data->id];
        }
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