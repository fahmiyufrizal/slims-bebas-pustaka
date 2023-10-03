<?php
if (!function_exists('getBaseDir')) {
    function getBaseDir(string $additionalPath = '') {
        return dirname(__DIR__) . DS . str_replace('..', '', $additionalPath);
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