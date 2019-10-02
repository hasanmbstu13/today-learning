<?php

/**
 * Plugin Name: Noah Shipping Method
 * Plugin URI: https://www.noahsarkworkshop.com
 * Description: For total quantity in cart: 1-10 = $8(Yellow Zone, add $5), 11-25 = $15, 26-40 = $20, 41-60 = $25(Yellow Zone, add $10), 61-80 = $30, 81-100 = $35, 101-120 = $40(Yellow Zone, add $15), 121-140 = $45, 150-180 = $55, 181-200 = $60, 201-250 = $75(Yellow Zone, add $20), 251-300 = $100, 301-350 = $125, 350-400 = $150(Yellow Zone, add $25), 401-500 = $175, Above 500, $175 plus add $25 for increments of 25. Yellow Zones: WA, MT, OR, ID, WY, CA, NV, UT, CO, AZ, NM, VT, NH, ME
 * Version: 0.1
 * Author: MVI Solutions
 * Author URI: https://www.mvisolutions.com/
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    function noah_shipping_method_init()
    {
        if (!class_exists('WC_Your_Shipping_Method')) {
            class WC_Noah_Shipping_Method extends WC_Shipping_Method
            {

                public $yellow_zones;

                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct($instance_id = 0)
                {
                    $this->instance_id = absint($instance_id);
                    $this->id = 'noah_shipping_method';
                    $this->title = __('Noah Shipping Method');
                    $this->method_title = 'Noah Shipping Method';
                    $this->method_description = __('Noah custom shipping method'); //
                    $this->enabled = "yes"; // This can be added as an setting but for this example its forced enabled
                    $this->supports = array(
                        'shipping-zones',
                    );

                    $this->yellow_zones = array('WA', 'MT', 'OR', 'ID', 'WY', 'CA', 'NV', 'UT', 'CO', 'AZ', 'NM', 'VT', 'NH', 'ME');

                    $this->init();
                }

                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init()
                {
                    // Load the settings API
                    $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
                    $this->init_settings(); // This is part of the settings API. Loads settings you previously init.

                    // Save settings in admin if you have any defined
                    add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
                }

                /**
                 * calculate_shipping function.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping($package = array())
                {

                    $shipping_state = isset($package['destination']['state']) ? $package['destination']['state'] : '';

                    $shipping_charge = 0;
                    // total animal count
                    $total_products = 0;
                    $color_burst_products = 0;
                    $animal_parent_category_id = 292; // 292 is the term id of the Furry Friends category
                    $color_burst_tile_cat = 280; // Color burst category term id
                    $animal_cat_arr = array();
                    $animal_categories = get_terms('product_cat',array('child_of' => $animal_parent_category_id));
                    if(count($animal_categories) > 0 ){
                        foreach ($animal_categories as $category){
                            $animal_cat_arr[] = $category->term_id;
                        }
                    }

                    $products = WC()->cart->get_cart_contents();
                    if(count($products) > 0){
                        foreach ($products as $product) {
                            $categories = get_the_terms ( $product['product_id'], 'product_cat' );

                            if(count($categories) > 0 ){
                                foreach ($categories as $category) {
                                    if($category->term_id == $color_burst_tile_cat){
                                        $color_burst_products += $product['quantity'];
                                        break;
                                    }
                                    if(in_array($category->term_id, $animal_cat_arr)){
                                        $total_products += $product['quantity'];
                                        break;
                                    }
                                }
                            }

                        }
                    } // endif



                    if($total_products >= 1 && $total_products <=10 ){
                        $shipping_charge = 8;
                        if(in_array($shipping_state, $this->yellow_zones)){
                            $shipping_charge += 5;
                        }
                    }elseif($total_products >= 11 && $total_products <=25 ){
                        $shipping_charge = 15;
                    }elseif($total_products >= 26 && $total_products <=40 ){
                        $shipping_charge = 20;
                    }elseif($total_products >= 41 && $total_products <=60 ){
                        $shipping_charge = 25;
                        if(in_array($shipping_state, $this->yellow_zones)){
                            $shipping_charge += 10;
                        }
                    }elseif($total_products >= 61 && $total_products <=80 ){
                        $shipping_charge = 30;
                    }elseif($total_products >= 81 && $total_products <=100 ){
                        $shipping_charge = 35;
                    }elseif($total_products >= 101 && $total_products <=120 ){
                        $shipping_charge = 40;
                        if(in_array($shipping_state, $this->yellow_zones)){
                            $shipping_charge += 15;
                        }
                    }elseif($total_products >= 121 && $total_products <=140 ){
                        $shipping_charge = 45;
                    }elseif($total_products >= 141 && $total_products <=150 ){
                        $shipping_charge = 50;
                    }elseif($total_products >= 151 && $total_products <=180 ){
                        $shipping_charge = 55;
                    }elseif($total_products >= 181 && $total_products <=200 ){
                        $shipping_charge = 60;
                    }elseif($total_products >= 201 && $total_products <=250 ){
                        $shipping_charge = 75;
                        if(in_array($shipping_state, $this->yellow_zones)){
                            $shipping_charge += 20;
                        }
                    }elseif($total_products >= 251 && $total_products <=300 ){
                        $shipping_charge = 100;
                    }elseif($total_products >= 301 && $total_products <=350 ){
                        $shipping_charge = 125;
                    }elseif($total_products >= 351 && $total_products <=400 ){
                        $shipping_charge = 150;
                        if(in_array($shipping_state, $this->yellow_zones)){
                            $shipping_charge += 25;
                        }
                    }elseif($total_products >= 401 && $total_products <=500 ){
                        $shipping_charge = 175;
                    }elseif($total_products > 500 ){
                        $shipping_charge = 175;
                        $shipping_charge += ((int) (($total_products - 500) / 25)) * 25;
                    }


                    $shipping_charge = $shipping_charge + ($color_burst_products * 3);


                    // This is where you'll add your rates
                    $rate = array(
                        'id' => $this->id,
                        'label' => 'Noah Shipping Method',
                        'cost' => $shipping_charge,
                        'calc_tax' => 'per_order'
                    );

                    // Register the rate
                    $this->add_rate($rate);
                }
            }
        }
    }

    add_action('woocommerce_shipping_init', 'noah_shipping_method_init');


    function noah_shipping_method($methods)
    {
        $methods['noah_shipping_method'] = 'WC_Noah_Shipping_Method';
        return $methods;
    }

    add_filter('woocommerce_shipping_methods', 'noah_shipping_method');

    add_filter( 'woocommerce_cart_shipping_method_full_label', 'noah_free_shipping_label', 10, 2 );
    function noah_free_shipping_label( $label, $method ) {
        if ( $method->cost == 0 ) {
            $label = 'Free shipping';
        }
        return $label;
    }
}
