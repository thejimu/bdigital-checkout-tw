<?php
/*
Plugin Name: Bdigital Checkout
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

//hide shipping
function bdigital_hide_shipping_when_free_is_available( $rates ) {
    $free = array();
    foreach ( $rates as $rate_id => $rate ) {
        if ( 'free_shipping' === $rate->method_id ) {
            $free[ $rate_id ] = $rate;
            break;
        }
    }
    return ! empty( $free ) ? $free : $rates;
}
add_filter( 'woocommerce_package_rates', 'bdigital_hide_shipping_when_free_is_available', 100 );

//regex check phone number
add_action('woocommerce_checkout_process', 'bdigital_validate_billing_phone');
function bdigital_validate_billing_phone() {
    $match = preg_match('/^(?=(09))[0-9]{10}$/', $_POST['billing_phone']);
    if ( $_POST['billing_phone'] && !$match) {
        wc_add_notice( __( $_POST['billing_phone'].' is not a valid Taiwan phone number.' ), 'error' );
    }
    if($_POST['office_delivery']==1){
        $_POST['order_comments'] .= '  !! THIS IS AN OFFICE DELIVERY !!';
    }
}

//customize fields
add_filter( 'woocommerce_checkout_fields' , 'bdigital_checkout_fields', 999 );

function get_cities(){
    $localeArray = json_decode(file_get_contents(plugin_dir_path(__FILE__).'/locales.json'));
    $cityArray = array('' => __('Select City'));
    foreach($localeArray->city as $item){
        $cityArray[$item->zhtw]  = __($item->en);
    }

    return $cityArray;
}

function bdigital_checkout_fields( $fields ) {

    $cities = get_cities();

    $fields['billing']['billing_city']['type'] = 'select';
    $fields['billing']['billing_city']['options'] = $cities;
    $fields['shipping']['shipping_city']['type'] = 'select';
    $fields['shipping']['shipping_city']['options'] = $cities;
    $fields['billing']['billing_address_2']['type'] = 'select';
    $fields['billing']['billing_address_2']['options'] = array('' => __('Select City First'));
    $fields['shipping']['shipping_address_2']['type'] = 'select';
    $fields['shipping']['shipping_address_2']['options'] = array('' => __('Select City First'));
    $fields['billing']['billing_address_2']['required'] = true;
    $fields['shipping']['shipping_address_2']['required'] = true;
    $fields['shipping']['shipping_address_2']['label'] = __('District/Township');
    $fields['billing']['billing_address_2']['label'] = __('District/Township');
    unset($fields['shipping']['shipping_address_2']['placeholder']);
    unset($fields['billing']['billing_address_2']['placeholder']);

    unset($fields['shipping']['shipping_state']);
    unset($fields['billing']['billing_state']);

    $fields['billing']['billing_last_name']['priority'] = 4;
    $fields['billing']['billing_last_name']['class'] = array('form-row-first');
    $fields['billing']['billing_first_name']['priority'] = 8;
    $fields['billing']['billing_first_name']['class'] = array('form-row-last');

    $fields['shipping']['shipping_last_name']['priority'] = 4;
    $fields['shipping']['shipping_last_name']['class'] = array('form-row-first');
    $fields['shipping']['shipping_first_name']['priority'] = 8;
    $fields['shipping']['shipping_first_name']['class'] = array('form-row-last');

    $order = array(
            'billing_last_name',
            'billing_first_name',
            'billing_company',
            'billing_city',
            'billing_address_2',
            'billing_address_1',
            'billing_postcode',
            'billing_phone',
            'billing_email'
    );

    $priority = 10;
    foreach($order as $field){
        $fields2['billing'][$field] = $fields['billing'][$field];
        $fields2['billing'][$field]['priority'] = $priority;
        $priority++;
    }

    $order = array(
        'shipping_last_name',
        'shipping_first_name',
        'shipping_company',
        'shipping_city',
        'shipping_address_2',
        'shipping_address_1',
        'shipping_postcode'
    );

    $priority = 10;
    foreach($order as $field){
        $fields2['shipping'][$field] = $fields['shipping'][$field];
        $fields2['shipping'][$field]['priority'] = $priority;
        $priority++;
    }

    //$fields2['shipping'] = $fields['shipping'];
    $fields2['order'] = $fields['order'];

    $fields2['order']['office_delivery'] = array(
        'type' => 'checkbox',
        'label' => __('Delivery is to an office')
    );

    $fields2['account'] = $fields['account'];

    return $fields2;
}

add_filter( 'wp_footer', 'bdigital_checkout_script' );
function bdigital_checkout_script( ){
    if( ! is_checkout() ) return;
    ?>
    <script type="text/javascript">
        jQuery(function($) {
            //var events = 'update_checkout',
            //    billingFields = '#billing_country_field';
            //billingFields += '#billing_city_field';

            $('#billing_city').on('change', function () {
                var city = document.getElementById('billing_city').value;

                $.ajax({
                    url: "<?php echo plugin_dir_url(__FILE__); ?>districts.php",
                    type: "get",
                    data: {
                        'city': city
                    },
                    success: function (result) {
                        document.getElementById('billing_address_2').innerHTML = result;
                    }
                })
            })
        });




        jQuery(function($) {
            //var events = 'update_checkout',
            //    billingFields = '#billing_country_field';
            //billingFields += '#billing_city_field';

            $('#shipping_city').on('change', function () {
                var city = document.getElementById('shipping_city').value;

                $.ajax({
                    url: "<?php echo plugin_dir_url(__FILE__); ?>districts.php",
                    type: "get",
                    data: {
                        'city': city
                    },
                    success: function (result) {
                        document.getElementById('shipping_address_2').innerHTML = result;
                    }
                })
            })
        });


    </script>
    <?php
}