<?php
/**
 * Plugin Name: Bebas Pustaka
 * Plugin URI: -
 * Description: -
 * Version: 1.0.0
 * Author: -
 * Author URI: -
 */
use SLiMS\Plugins;

Plugins::getInstance()->registerAutoload(__DIR__);
Plugins::menu('circulation', 'Bebas Pustaka', __DIR__ . '/pages/bebas_pustaka.php');