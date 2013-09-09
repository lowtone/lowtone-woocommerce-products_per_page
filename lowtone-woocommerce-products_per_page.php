<?php
/*
 * Plugin Name: Products per Page
 * Plugin URI: http://wordpress.lowtone.nl/plugins/woocommerce-products_per_page/
 * Description: Add an option for visitors to select the number of products per page for WooCommerce.
 * Version: 1.0
 * Author: Lowtone <info@lowtone.nl>
 * Author URI: http://lowtone.nl
 * License: http://wordpress.lowtone.nl/license
 */
/**
 * @author Paul van der Meijs <code@lowtone.nl>
 * @copyright Copyright (c) 2011-2013, Paul van der Meijs
 * @license http://wordpress.lowtone.nl/license/
 * @version 1.0
 * @package wordpress\plugins\lowtone\woocommerce\products_per_page
 */

namespace lowtone\woocommerce\products_per_page {

	// Add a drop-down selection menu after ordering

	add_action("woocommerce_before_shop_loop", function() {

		echo '<form method="get" class="woocommerce-products_per_page" onchange="this.form.submit()">' . 
			sprintf('<select name="%s" class="orderby">', esc_attr(att()));
		
		foreach (options() as $val => $label) 
			echo sprintf('<option value="%s" %s>', esc_attr($val), selected(val(), $val)) . 
				esc_html($label) . 
				'</option>'; 

		echo '</select>';

		$recursiveInput = function($name, $value) use (&$recursiveInput) {
			if (!is_array($value)) 
				return sprintf('<input type="hidden" name="%s" value="%s" />', esc_attr($name), esc_attr($value));

			return implode(array_map(function($key, $value) use (&$recursiveInput, $name) {
					return $recursiveInput($name . "[" . $key . "]", $value);
				}, array_keys($value), array_values($value)));
		};

		foreach ($_GET as $name => $value) {
			if (att() == $name)
				continue;
			
			echo $recursiveInput($name, $value);
		}
			
		echo '</form>';

	}, 20);

	// Overwrite products per page if a valid custom value is set

	add_filter("loop_shop_per_page", function($number) {
		if (NULL !== ($val = val()) && in_array($val, numbers()))
			$number = $val;

		return $number;
	}, 9999);

	// Register textdomain
	
	add_action("plugins_loaded", function() {
		load_plugin_textdomain("lowtone_woocommerce_products_per_page", false, basename(__DIR__) . "/assets/languages");
	});

	// Functions
	
	function att() {
		return "products_per_page";
	}
	
	function val() {
		return isset($_REQUEST[att()]) ? $_REQUEST[att()] : NULL;
	}
	
	function options() {
		$options = array(
				"" => __("Products per page", "lowtone_woocommerce_products_per_page"),
			);

		foreach (numbers() as $number)
			$options[$number] = sprintf(__("%s per page", "lowtone_woocommerce_products_per_page"), $number);

		return apply_filters("lowtone_woocommerce_products_per_page_options", $options);
	}

	function numbers() {
		return apply_filters("lowtone_woocommerce_products_per_page_numbers", array(
				24, 
				36, 
				48,
				64,
			));
	}

}