<?php

if (!defined('WPINC')) {
    exit;
}

if (!class_exists('Webtoffee_Product_Feed_Sync_Fruugo_Export')) {

    class Webtoffee_Product_Feed_Sync_Fruugo_Export extends Webtoffee_Product_Feed_Product {

        public $parent_module = null;
        public $product;
        public $current_product_id;
        public $form_data;
        
        public $fruugo_feed_keys = array();
        public $fruugo_xml_skipfeed_keys = array();
        public $max_variation_count = 0;

        public function __construct($parent_object) {

            $this->parent_module = $parent_object;
            
            $this->fruugo_feed_keys = array(

                'id' => 'ProductId',
                'title' => 'Title',
                'description' => 'Description',
                'sku_id' => 'SkuId',
                'brand' => 'Brand',
                'quantity' => 'StockQuantity',
                'google_product_category' => 'Category',
                'currency' => 'Currency',                
                'price_without_vat' => 'NormalPriceWithoutVAT',
                'vat_rate' => 'VATRate',                
                'availability' => 'StockStatus',               
                'package_weight' => 'PackageWeight',
                'language' => 'Language',
                'ean' => 'EAN',
                'isbn' => 'ISBN',


            );            
            
            $max_variations = $this->parent_module->get_max_variations_count_for_all_variable_products();
            if( $max_variations > 0 ){                            
                for($i=1; $i<=$max_variations; $i++){
                    $this->fruugo_feed_keys['Attribute'.$i] = 'Attribute'.$i;                                
                }
            } 
            $this->max_variation_count = $max_variations;
            
        }

        public function prepare_header() {

            $export_columns = $this->parent_module->get_selected_column_names();

            
            foreach ($export_columns as $key => $value) {
                if(isset($this->fruugo_feed_keys[$key])){
                    $export_columns[$key] = $this->fruugo_feed_keys[$key];                    
                }
            }
            
            if (isset($this->form_data['advanced_form_data']['wt_pf_file_as']) && 'xml' === $this->form_data['advanced_form_data']['wt_pf_file_as']) {
                unset($export_columns['title'], $export_columns['language'], $export_columns['currency'], $export_columns['price_without_vat'], $export_columns['vat_rate'], $export_columns['AttributeColor'], $export_columns['AttributeSize'] );
                $export_columns['Price'] = 'Price';
                
                    if($this->max_variation_count > 0 ){
                       for($i=1; $i<=$this->max_variation_count; $i++){
                           unset($export_columns['Attribute'.$i]);
                       }  
                    }
                
            }

            $export_columns = array_flip($export_columns); // Real feed column name of fruugo to CSV header
            return apply_filters('wt_pf_alter_product_feed_csv_columns', $export_columns);
        }

        /**
         * Prepare data that will be exported.
         */
        public function prepare_data_to_export($form_data, $batch_offset, $step) {

            $this->form_data = $form_data;

            
            $include_variations_type = !empty($form_data['post_type_form_data']['wt_pf_include_variations_type']) ? $form_data['post_type_form_data']['wt_pf_include_variations_type'] : '';
            
            $exc_stock_status = !empty($form_data['post_type_form_data']['item_outofstock']) ? $form_data['post_type_form_data']['item_outofstock'] : '';
            
            if('' === $exc_stock_status){
                $exc_stock_status = !empty($form_data['post_type_form_data']['wt_pf_exclude_outofstock']) ? $form_data['post_type_form_data']['wt_pf_exclude_outofstock'] : '';
            }
            
            $item_parentonly = !empty($form_data['post_type_form_data']['item_parentonly']) ? $form_data['post_type_form_data']['item_parentonly'] : '';

            if( '' === $item_parentonly ){
                $item_parentonly = !empty($form_data['post_type_form_data']['wt_pf_include_parent_only']) ? $form_data['post_type_form_data']['wt_pf_include_parent_only'] : '';                
            }
             if( '' !== $item_parentonly ){
                 $include_variations_type = 'default';
             }
            
            $prod_exc_categories = !empty($form_data['post_type_form_data']['item_exc_cat']) ? $form_data['post_type_form_data']['item_exc_cat'] : array();            
            $prod_inc_categories = !empty($form_data['post_type_form_data']['item_inc_cat']) ? $form_data['post_type_form_data']['item_inc_cat'] : array();

            $cat_filter_type = !empty($form_data['post_type_form_data']['cat_filter_type']) ? $form_data['post_type_form_data']['cat_filter_type'] : '';
            if( '' === $cat_filter_type ){
                $cat_filter_type = !empty($form_data['post_type_form_data']['wt_pf_export_cat_filter_type']) ? $form_data['post_type_form_data']['wt_pf_export_cat_filter_type'] : 'include_cat';
            }
            
            $inc_exc_category = !empty($form_data['post_type_form_data']['inc_exc_cat']) ? $form_data['post_type_form_data']['inc_exc_cat'] : array();
            if( empty($inc_exc_category) ){
                $inc_exc_category = !empty($form_data['post_type_form_data']['wt_pf_inc_exc_category']) ? $form_data['post_type_form_data']['wt_pf_inc_exc_category'] : array();
            }
            
            
            if ('include_cat' === $cat_filter_type) {
                $prod_inc_categories = $inc_exc_category;
            } else {
                $prod_exc_categories = $inc_exc_category;
            }

            
            $brand_filter_type = !empty($form_data['post_type_form_data']['wt_pf_export_brand_filter_type']) ? $form_data['post_type_form_data']['wt_pf_export_brand_filter_type'] : 'include_brand';
            $inc_exc_brand = !empty($form_data['post_type_form_data']['wt_pf_inc_exc_brand']) ? $form_data['post_type_form_data']['wt_pf_inc_exc_brand'] : array();

            if ('include_brand' === $brand_filter_type) {
                $prod_inc_brands = $inc_exc_brand;
            } else {
                $prod_exc_brands = $inc_exc_brand;
            }            

            $prod_exc = !empty($form_data['post_type_form_data']['item_exc_prd']) ? $form_data['post_type_form_data']['item_exc_prd'] : array();

            if( empty($prod_exc) ){
                $prod_exc = !empty($form_data['post_type_form_data']['wt_pf_exclude_products']) ? $form_data['post_type_form_data']['wt_pf_exclude_products'] : array();
            }
            
            /* WPML
             * 
             */
            $item_post_lang = !empty($form_data['post_type_form_data']['item_post_lang']) ? $form_data['post_type_form_data']['item_post_lang'] : '';

            if( '' === $item_post_lang ){
                $item_post_lang = !empty($form_data['post_type_form_data']['wt_pf_export_post_language']) ? $form_data['post_type_form_data']['wt_pf_export_post_language'] : '';
            }
            
            $prod_tags = !empty($form_data['filter_form_data']['wt_pf_product_tags']) ? $form_data['filter_form_data']['wt_pf_product_tags'] : array();
            
            $prod_types = !empty($form_data['post_type_form_data']['item_product_type']) ? $form_data['post_type_form_data']['item_product_type'] : array();
            
            if( empty( $prod_types ) ){
                $prod_types = !empty($form_data['post_type_form_data']['wt_pf_product_types']) ? $form_data['post_type_form_data']['wt_pf_product_types'] : array();
            }
            
            $prod_status = !empty($form_data['filter_form_data']['wt_pf_product_status']) ? $form_data['filter_form_data']['wt_pf_product_status'] : array();

            $export_sortby = !empty($form_data['filter_form_data']['wt_pf_sort_columns']) ? $form_data['filter_form_data']['wt_pf_sort_columns'] : 'ID';
            $export_sort_order = !empty($form_data['filter_form_data']['wt_pf_order_by']) ? $form_data['filter_form_data']['wt_pf_order_by'] : 'ASC';

            $export_limit = !empty($form_data['filter_form_data']['wt_pf_limit']) ? intval($form_data['filter_form_data']['wt_pf_limit']) : 999999999; //user limit
            $current_offset = !empty($form_data['filter_form_data']['wt_pf_offset']) ? intval($form_data['filter_form_data']['wt_pf_offset']) : 0; //user offset

            $batch_count = !empty($form_data['advanced_form_data']['wt_pf_batch_count']) ? $form_data['advanced_form_data']['wt_pf_batch_count'] : Webtoffee_Product_Feed_Sync_Common_Helper::get_advanced_settings('default_export_batch');
            $batch_count = apply_filters('wt_product_feed_limit_per_request', $batch_count); //ajax batch limit

            $this->export_children_sku = (!empty($form_data['advanced_form_data']['wt_pf_export_children_sku']) && $form_data['advanced_form_data']['wt_pf_export_children_sku'] == 'Yes') ? true : false;

            $this->export_shortcodes = (!empty($form_data['advanced_form_data']['wt_pf_export_shortcode_tohtml']) && $form_data['advanced_form_data']['wt_pf_export_shortcode_tohtml'] == 'Yes') ? true : false;

            $this->export_images_zip = (!empty($form_data['advanced_form_data']['wt_pf_image_export']) && $form_data['advanced_form_data']['wt_pf_image_export'] == 'Yes') ? true : false;

            $real_offset = ($current_offset + $batch_offset);

            if ($batch_count <= $export_limit) {
                if (($batch_offset + $batch_count) > $export_limit) { //last offset
                    $limit = $export_limit - $batch_offset;
                } else {
                    $limit = $batch_count;
                }
            } else {
                $limit = $export_limit;
            }

            $product_array = array();
            $total_products = 0;
            if ($batch_offset < $export_limit) {
                $args = array(
                    'status' => array('publish'),
                    'type' => array_keys(wc_get_product_types()),
                    'limit' => $limit,
                    'offset' => $real_offset,
                    'orderby' => $export_sortby,
                    'order' => $export_sort_order,
                    'return' => 'ids',
                    'paginate' => true,
                );

                if ( '' === $include_variations_type ) {
                    array_push($args['type'], 'variation');
                }

                if (!empty($prod_status)) {
                    $args['status'] = $prod_status;
                }

                if (!empty($prod_types)) {
                    $args['type'] = $prod_types;
                }

                if (!empty($prod_exc_categories)) {
                    $args['exclude_category'] = $prod_exc_categories;
                }

                if (!empty($prod_inc_brands)) {
                    $args['include_brands'] = $prod_inc_brands;
                }
                if (!empty($prod_exc_brands)) {
                    $args['exclude_brands'] = $prod_exc_brands;
                }                
                
                if (!empty($prod_inc_categories)) {
                    $args['category'] = $prod_inc_categories;
                }

                if (!empty($prod_tags)) {
                    $args['tag'] = $prod_tags;
                }

                if (!empty($prod_exc)) {
                    $args['exclude'] = $prod_exc;
                }

                if (!empty($exc_stock_status)) {
                    $args['stock_status'] = 'instock';
                }

                // Export all language products if WPML is active and the language selected is all.
                if (function_exists('icl_object_id') && isset($_SERVER["HTTP_REFERER"]) && strpos($_SERVER["HTTP_REFERER"], 'lang=all') !== false) {
                    $args['suppress_filters'] = true;
                }


                $args['exclude_discarded'] = '_wt_feed_discard'; // To exclude individual excluded from product fetching.

                $args = apply_filters("wt_feed_product_catalog_args", $args);


                $advanced_filter_options = array();
                
                $wt_pf_adv_filter_fields = !empty($form_data['post_type_form_data']['wt_pf_adv_filter_if[]']) ? (array)$form_data['post_type_form_data']['wt_pf_adv_filter_if[]'] : array();
                $wt_pf_adv_filter_condition = !empty($form_data['post_type_form_data']['wt_pf_adv_filter_condition[]']) ? (array)$form_data['post_type_form_data']['wt_pf_adv_filter_condition[]'] : array();
                $wt_pf_adv_filter_val = !empty($form_data['post_type_form_data']['wt_pf_adv_filter_val[]']) ? (array)$form_data['post_type_form_data']['wt_pf_adv_filter_val[]'] : array();
                $wt_pf_adv_filter_then = !empty($form_data['post_type_form_data']['wt_pf_adv_filter_then[]']) ? (array)$form_data['post_type_form_data']['wt_pf_adv_filter_then[]'] : array();
                      
                if( !empty($wt_pf_adv_filter_fields) ){
                    $advanced_filter_options['fields'] = $wt_pf_adv_filter_fields;
                    $advanced_filter_options['condition'] = $wt_pf_adv_filter_condition;
                    $advanced_filter_options['val'] = $wt_pf_adv_filter_val;
                    $advanced_filter_options['then'] = $wt_pf_adv_filter_then;       
                }

                /*
                 * WPML - Swicth language to selected language for temparory export
                 */
                if (class_exists('SitePress') && !empty($item_post_lang)) {
                    //$args['suppress_filters'] = true;
                    global $sitepress;
                    $current_lang = $sitepress->get_current_language(); // Take the current language to a variable to swicthback later.
                    $default_language = $sitepress->get_default_language();
                    $sitepress->switch_lang($item_post_lang);
                }

                $products = wc_get_products($args);

                $total_products = 0;
                if (0 == $batch_offset) { //first batch
                    $total_item_args = $args;
                    $total_item_args['limit'] = $export_limit; //user given limit
                    $total_item_args['offset'] = $current_offset; //user given offset
                    $total_products_count = wc_get_products($total_item_args);
                    $total_products = count($total_products_count->products);
                }

                /*
                 * WPML - Swicth language back to the previous site language after the batch reading.
                 */
                if (class_exists('SitePress') && !empty($item_post_lang)) {
                    //$args['suppress_filters'] = true;
                    global $sitepress;
                    $sitepress->switch_lang($current_lang); // Current language is previously stored
                }

                $products_ids = $products->products;

                // If include category is selected and variable products are under those category, the variations will not be returned by the WC query
                if (!empty($prod_inc_categories)) {
                    $temp_prod_ids = $products_ids;
                    foreach ($temp_prod_ids as $key => $product_id) {
                        $product = wc_get_product($product_id);
                        if ($product->is_type('variable')) {
                            $variations = $product->get_available_variations();
                            $variations_ids = wp_list_pluck($variations, 'variation_id');
                            foreach ($variations_ids as $variations_id) {
                                $products_ids[] = $variations_id;
                            }
                        }
                    }
                }

                foreach ($products_ids as $key => $product_id) {
                    $product = wc_get_product($product_id);

                    // Skip variations that belongs to a specific categories that is excluded in filter
                    if ($product->is_type('variation') && !empty($prod_exc_categories)) {
                        $parent_id = $product->get_parent_id();
                        if (has_term($prod_exc_categories, 'product_cat', $parent_id)) {
                            continue;
                        }
                    }
                    
                    //Skip variation other than default when Only include default product variation is checked.
                    if ($product->is_type('variation') && 'default' === $include_variations_type ) {
                        continue;
                    }                    

                    if ($product->is_type('variable') && 'default' === $include_variations_type ) {
                        $default_variation_id = $this->get_default_variation($product);
                        if ($default_variation_id) {
                            $product = wc_get_product($default_variation_id);
                        }
                    }
                    if ($product->is_type('variable') && 'lowest' === $include_variations_type ) {
                        $lowest_variation_id = $this->get_lowest_priced_variation_id($product);
                        if ($lowest_variation_id) {
                            $product = wc_get_product($lowest_variation_id);
                        }
                    }                                        
                    
                    if ($product->is_type('variable') && '' === $include_variations_type ) {
                        continue;
                    }

                    if( $product->is_type( 'variation' ) ){
                        $parent_id = $product->get_parent_id();
                        $parent_post = get_post( $parent_id );
                        if( !is_object( $parent_post ) || ( is_object( $parent_post ) && ( 'draft' == $parent_post->post_status || 'private' == $parent_post->post_status || 'pending' == $parent_post->post_status ) ) ){
                            continue;
                        }
                    }                    
                    
                    $this->parent_product = $product;
                    $this->product = $product;
                    $this->current_product_id = $product->get_id();
               
                    
                    $product_array[] = $this->generate_row_data_wc_lower($product);
                }
            }

            $return_products = array(
                'total' => $total_products,
                'data' => $product_array,
            );
            if (0 == $batch_offset && 0 == $total_products) {
                $return_products['no_post'] = __('Nothing to export under the selected criteria. Please try adjusting the filters.', 'webtoffee-product-feed');
            }
            return $return_products;
        }

        public function get_default_variation($product) {

            $variation_id = false;

            foreach ($product->get_available_variations() as $variation_values) {
                foreach ($variation_values['attributes'] as $key => $attribute_value) {
                    $attribute_name = str_replace('attribute_', '', $key);
                    $default_value = $product->get_variation_default_attribute($attribute_name);
                    if ($default_value == $attribute_value) {
                        $is_default_variation = true;
                    } else {
                        $is_default_variation = false;
                        break; // Stop this loop to start next main lopp
                    }
                }

                if ($is_default_variation) {
                    $variation_id = $variation_values['variation_id'];
                    break; // Stop the main loop
                }
            }
            return $variation_id;
        }

        protected function generate_row_data_wc_lower($product_object) {

            $export_columns = $this->parent_module->get_selected_column_names();

            if (isset($this->form_data['advanced_form_data']['wt_pf_file_as']) && 'xml' === $this->form_data['advanced_form_data']['wt_pf_file_as']) {
                $this->fruugo_xml_skipfeed_keys['title'] = $export_columns['title'];
                $this->fruugo_xml_skipfeed_keys['language'] = $export_columns['language'];
                $this->fruugo_xml_skipfeed_keys['currency'] = $export_columns['currency'];
                $this->fruugo_xml_skipfeed_keys['price_without_vat'] = $export_columns['price_without_vat'];
                $this->fruugo_xml_skipfeed_keys['vat_rate'] = $export_columns['vat_rate'];                 
                unset($export_columns['title'], $export_columns['language'], $export_columns['currency'], $export_columns['price_without_vat'], $export_columns['vat_rate'] , $export_columns['AttributeColor'], $export_columns['AttributeSize'] );
                $export_columns['Price'] = 'Price';
                if($this->max_variation_count > 0 ){
                   for($i=1; $i<=$this->max_variation_count; $i++){
                       unset($export_columns['Attribute'.$i]);
                   }  
                }                
            }            
            
            $product_id = $product_object->get_id();

            $row = array();

            foreach ($export_columns as $key => $value) {
                if (method_exists($this, $value)) {
                    $column_data = $this->$value($key, $value, $export_columns);                    
                    if(isset($this->fruugo_feed_keys[$key])){
                        $row[$this->fruugo_feed_keys[$key]] = $column_data;
                    }else{
                        $row[$key] = $column_data;
                    }
                }elseif (strpos($value, 'meta:') !== false) {
                    $mkey = str_replace('meta:', '', $value);
                    if(isset($this->fruugo_feed_keys[$key])){
                        $row[$this->fruugo_feed_keys[$key]] = get_post_meta($product_id, $mkey, true);
                    }else{
                        $row[$key] = get_post_meta($product_id, $mkey, true);
                    }
                    // TODO
                    // wt_image_ function can be replaced with key exist check
                }elseif (strpos($value, 'wt_pf_pa_') !== false) {
                    $atr_key = str_replace('wt_pf_pa_', '', $value);
                    if($product_object->is_type('variation')){
                        $product_object = wc_get_product($product_object->get_parent_id());
                    }
                    $value = '';
                    if(is_object($product_object)){
                        $value = $product_object->get_attribute( $atr_key );
                    }
                    if ( ! empty( $value ) ) {
				$value = trim( $value );
			}
                    $row[$key] = $value;
                }elseif (strpos($value, 'wt_pf_cattr_') !== false) {
                    $atr_key = str_replace('wt_pf_cattr_', '', $value);
                    if($product_object->is_type('variation')){
                        $product_object = wc_get_product($product_object->get_parent_id());
                    }
                    $value = '';
                    if(is_object($product_object)){
                        $value = $product_object->get_attribute( $atr_key );

                    }
                    if ( ! empty( $value ) ) {
				        $value = trim( $value );
                        $value = str_replace('|', ',', $value);
			}
                    $row[$key] = $value;
                } elseif (strpos($value, 'wt_static_map_vl:') !== false) {
                    $static_feed_value = str_replace('wt_static_map_vl:', '', $value);
                    $row[$key] = $static_feed_value;
                }else {
                    $row[$key] = '';
                }
            }

                        return apply_filters("wt_batch_product_export_row_data_{$this->parent_module->module_base}", $row, $product_object);
        }

        /**
         * Get product id.
         *
         * @return mixed|void
         */
        public function id($catalog_attr, $product_attr, $export_columns) {
            
            $id = $this->product->get_id();
            $sku = $this->product->get_sku();
            if($this->product->is_type('variation')){
                $id = $this->product->get_parent_id();
                $sku = get_post_meta($id, '_sku', true);
            }
            $product_id = $sku . $id;
            
            return apply_filters('wt_feed_filter_product_id', $product_id, $this->product);
        }

        /**
         * Price without tax
         */
        public function price_without_vat($catalog_attr, $product_attr, $export_columns){
            // Get the prices
            $price_excl_tax = wc_get_price_excluding_tax( $this->product ); // price without VAT
            
            return apply_filters('wt_feed_filter_product_price_without_vat', $price_excl_tax, $this->product);
        }
        /**
         * Get VAT rate for the product
         * 
         * @param string $catalog_attr The catalog attribute
         * @param string $product_attr The product attribute
         * @param array $export_columns Export columns configuration
         * @return string Numeric VAT rate without % symbol, 0 for non-EU retailers
         */
        public function vat_rate($catalog_attr, $product_attr, $export_columns) {
            // Default VAT rate to 0 for non-EU retailers
            $vat_rate = 0;
            
            // List of EU country codes
            $eu_countries = array('AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE');
            
            // Get store base country
            $store_country = WC()->countries->get_base_country();
            
            // Only calculate VAT if store is in EU
            if (in_array($store_country, $eu_countries)) {
                // Get the base tax rates for the product's tax class
                $base_tax_rates = WC_Tax::get_base_tax_rates($this->product->get_tax_class('unfiltered'));
                
                if (!empty($base_tax_rates)) {
                    // Get the first applicable tax rate
                    $base_rate = reset($base_tax_rates);
                    if (isset($base_rate['rate'])) {
                        $vat_rate = floatval($base_rate['rate']);
                    }
                }
            }
            
            // Format the VAT rate as a numeric value without % symbol
            $formatted_vat_rate = number_format($vat_rate, 0, '.', '');
            
            return apply_filters('wt_feed_filter_product_vat_rate', $formatted_vat_rate, $this->product);
        }       
        /**
         * Get product identification_type.
         *
         * @return mixed|void
         */
        public function identification_type($catalog_attr, $product_attr, $export_columns) {
            return apply_filters('wt_feed_filter_product_identification_type', 'GTIN', $this->product);
        }        
        
        
        /**
         * Get product language.
         *
         * @return mixed|void
         */
        public function language($catalog_attr, $product_attr, $export_columns) {
            return apply_filters('wt_feed_filter_product_language', substr( get_bloginfo ( 'language' ), 0, 2 ), $this->product);
        }         
        
        
	public function ean($catalog_attr, $product_attr, $export_columns){
		
			$custom_ean = get_post_meta($this->product->get_id(), '_wt_feed_ean', true);
                        if( ! $custom_ean ){
                            $custom_ean = get_post_meta($this->product->get_id(), '_wt_feed_gtin', true);
                        }                        
			$ean = ('' == $custom_ean) ? '' : $custom_ean;
			return apply_filters('wt_feed_product_ean', $ean, $this->product);
	}
        
	public function isbn($catalog_attr, $product_attr, $export_columns){
		
			$custom_isbn = get_post_meta($this->product->get_id(), '_wt_feed_isbn', true);
                        if( ! $custom_isbn ){
                            $custom_isbn = get_post_meta($this->product->get_id(), '_wt_feed_isbn', true);
                        }                        
			$isbn = ('' == $custom_isbn) ? '' : $custom_isbn;
			return apply_filters('wt_feed_product_isbn', $isbn, $this->product);
			
	}        
        
        /**
         * Get product name.
         *
         * @return mixed|void
         */
        public function title($catalog_attr, $product_attr, $export_columns) {

            $title = $this->product->get_name();

            // Add all available variation attributes to variation title.
            if ($this->product->is_type('variation') && !empty($this->product->get_attributes())) {
                $title = $this->parent_product->get_name();
                $attributes = [];
                foreach ($this->product->get_attributes() as $slug => $value) {
                    $attribute = $this->product->get_attribute($slug);
                    if (!empty($attribute)) {
                        $attributes[$slug] = $attribute;
                    }
                }

                // set variation attributes with separator
                $separator = ',';

                $variation_attributes = implode($separator, $attributes);

                //get product title with variation attribute
                $get_with_var_attributes = apply_filters("wt_feed_get_product_title_with_variation_attribute", true, $this->product);

                if ($get_with_var_attributes) {
                    $title .= " - " . $variation_attributes;
                }
            }

            return apply_filters('wt_feed_filter_product_title', $title, $this->product);
        }

        /**
         * Get parent product title for variation.
         *
         * @return mixed|void
         */
        public function parent_title($catalog_attr, $product_attr, $export_columns) {
            $title = '';
            if ($this->product->is_type('variation')) {
                $parent_product = wc_get_product($this->product->get_parent_id());
                if (is_object($parent_product)) {
                    $title = $parent_product->get_name();
                }
            } else {
                $title = $this->product->get_name();
            }

            return apply_filters('wt_feed_filter_product_parent_title', $title, $this->product);
        }

        /**
         * Get product description.
         *
         * @return mixed|void
         */
        public function description($catalog_attr, $product_attr, $export_columns) {
            $description = $this->product->get_description();

            
            // Get Variation Description
            if ('' === $description && $this->product->is_type('variation')) {
                $description = '';
                $parent_product = wc_get_product($this->product->get_parent_id());
                if (is_object($parent_product)) {
                    $description = $parent_product->get_description();
                }
            }

            if ('' === $description) {
                $description = $this->product->get_short_description();
            }

            // Add variations attributes after description to prevent Facebook error
            if ($this->product->is_type('variation') && ( '' === $description )) {
                $variationInfo = explode('-', $this->product->get_name());

                if (isset($variationInfo[1])) {
                    $extension = $variationInfo[1];
                } else {
                    $extension = $this->product->get_id();
                }
                $description .= ' ' . $extension;
            }

            //strip tags and special characters
            $description = strip_tags($description);
            
            
           if (isset($this->form_data['advanced_form_data']['wt_pf_file_as']) && 'xml' === $this->form_data['advanced_form_data']['wt_pf_file_as']) {
               
               $description = array(
                   'Language' => apply_filters('wt_feed_filter_product_language', substr( get_bloginfo ( 'language' ), 0, 2 ), $this->product),
                   'Title' => $this->title($catalog_attr, $product_attr, $export_columns),
                   'Description' => $this->wt_remove_emoji( $description )
                   
               );
               if($this->product->is_type('variation')){
                   $attribute_size = $this->AttributeSize($catalog_attr, $product_attr, $export_columns);
                   $attribute_color = $this->AttributeColor($catalog_attr, $product_attr, $export_columns);
                   $attr_count = 1;
                   if('' !== $attribute_size){
                       $description['AttributeSize'] = $attribute_size;
                       $attr_count++;
                   }
                   if('' !== $attribute_color){
                       $description['AttributeColor'] = $attribute_color;
                       $attr_count++;
                   }
                   
                   if($this->max_variation_count > 0 ){
                       $attr_fn_count = 1;
                       
                       for($attr_count; $attr_count<=$this->max_variation_count; $attr_count++){
                            $fn_name = 'Attribute'.$attr_count;
                            $attr_value = $this->$fn_name($catalog_attr, $product_attr, $export_columns, $attr_count);
                            if('' !== $attr_value){
                                $description["Attribute$attr_fn_count"] = $attr_value;
                                $attr_fn_count++;
                            }
                        }
                   }
               }
               
           }
            

            return apply_filters('wt_feed_filter_product_description', $description, $this->product);
        }

        /**
         * Remove emoji from string.
         * https://stackoverflow.com/a/68155491/1117368
         * @param type $string
         * @return type
         */
        public function wt_remove_emoji($string)
        {
            // Match Enclosed Alphanumeric Supplement
            $regex_alphanumeric = '/[\x{1F100}-\x{1F1FF}]/u';
            $clear_string = preg_replace($regex_alphanumeric, '', $string);

            // Match Miscellaneous Symbols and Pictographs
            $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
            $clear_string = preg_replace($regex_symbols, '', $clear_string);

            // Match Emoticons
            $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
            $clear_string = preg_replace($regex_emoticons, '', $clear_string);

            // Match Transport And Map Symbols
            $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
            $clear_string = preg_replace($regex_transport, '', $clear_string);

            // Match Supplemental Symbols and Pictographs
            $regex_supplemental = '/[\x{1F900}-\x{1F9FF}]/u';
            $clear_string = preg_replace($regex_supplemental, '', $clear_string);

            // Match Miscellaneous Symbols
            $regex_misc = '/[\x{2600}-\x{26FF}]/u';
            $clear_string = preg_replace($regex_misc, '', $clear_string);

            // Match Dingbats
            $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
            $clear_string = preg_replace($regex_dingbats, '', $clear_string);

            return $clear_string;
        }        
        
        /**
         * Get product description with HTML tags.
         *
         * @return mixed|void
         */
        public function description_with_html($catalog_attr, $product_attr, $export_columns) {
            $description = $this->product->get_description();

            // Get Variation Description
            if (empty($description) && $this->product->is_type('variation')) {
                $description = '';
                if (!is_null($this->parent_product)) {
                    $description = $this->parent_product->get_description();
                }
            }

            if (empty($description)) {
                $description = $this->product->get_short_description();
            }

            //$description = CommonHelper::remove_shortcodes( $description );
            // Add variations attributes after description to prevent Facebook error
            if ($this->product->is_type('variation')) {
                $variationInfo = explode('-', $this->product->get_name());
                if (isset($variationInfo[1])) {
                    $extension = $variationInfo[1];
                } else {
                    $extension = $this->product->get_id();
                }
                $description .= ' ' . $extension;
            }

            //remove spacial characters
            $description = wp_check_invalid_utf8(wp_specialchars_decode($description), true);

            return apply_filters('wt_feed_filter_product_description_with_html', $description, $this->product);
        }

        /**
         * Get product short description.
         *
         * @return mixed|void
         */
        public function short_description($catalog_attr, $product_attr, $export_columns) {
            $short_description = $this->product->get_short_description();

            // Get Variation Short Description
            if (empty($short_description) && $this->product->is_type('variation')) {
                $short_description = $this->parent_product->get_short_description();
            }


            // Strip tags and special characters
            $short_description = strip_tags($short_description);

            return apply_filters('wt_feed_filter_product_short_description', $short_description, $this->product);
        }

        /**
         * Get product primary category.
         *
         * @return mixed|void
         */
        public function primary_category($catalog_attr, $product_attr, $export_columns) {
            $parent_category = "";
            $separator = apply_filters('wt_feed_product_type_separator', ' - ');

            $full_category = $this->product_type();
            if (!empty($full_category)) {
                $full_category_array = explode($separator, $full_category);
                $parent_category = $full_category_array[0];
            }

            return apply_filters('wt_feed_filter_product_primary_category', $parent_category, $this->product);
        }

        /**
         * Get product primary category id.
         *
         * @return mixed|void
         */
        public function primary_category_id($catalog_attr, $product_attr, $export_columns) {
            $parent_category_id = "";
            $separator = apply_filters('wt_feed_product_type_separator', ' - ');
            $full_category = $this->product_type();
            if (!empty($full_category)) {
                $full_category_array = explode($separator, $full_category);
                $parent_category_obj = get_term_by('name', $full_category_array[0], 'product_cat');
                $parent_category_id = isset($parent_category_obj->term_id) ? $parent_category_obj->term_id : "";
            }

            return apply_filters('wt_feed_filter_product_primary_category_id', $parent_category_id, $this->product);
        }

        /**
         * Get product child category.
         *
         * @return mixed|void
         */
        public function child_category($catalog_attr, $product_attr, $export_columns) {
            $child_category = "";
            $separator = apply_filters('wt_feed_product_type_separator', ' - ');
            $full_category = $this->product_type();
            if (!empty($full_category)) {
                $full_category_array = explode($separator, $full_category);
                $child_category = end($full_category_array);
            }

            return apply_filters('wt_feed_filter_product_child_category', $child_category, $this->product);
        }

        public function get_shipping() {

            $shpping_country = $this->form_data['post_type_form_data']['item_country'];
            $shipping_obj = new Webtoffee_Product_Feed_Basic_Shipping($this->product, 'fruugo', $this->form_data);
            $shipping_info = $shipping_obj->get_shipping_by_location($shpping_country);

            return apply_filters("wt_feed_processed_shipping_infos", $shipping_info, $this->product);
        }

        /**
         * Get product child category id.
         *
         * @return mixed|void
         */
        public function child_category_id($catalog_attr, $product_attr, $export_columns) {
            $child_category_id = "";
            $separator = apply_filters('wt_feed_product_type_separator', ' - ');
            $full_category = $this->product_type();
            if (!empty($full_category)) {
                $full_category_array = explode($separator, $full_category);
                $child_category_obj = get_term_by('name', end($full_category_array), 'product_cat');
                $child_category_id = isset($child_category_obj->term_id) ? $child_category_obj->term_id : "";
            }

            return apply_filters('wt_feed_filter_product_child_category_id', $child_category_id, $this->product);
        }

        public function google_product_category($catalog_attr, $product_attr, $export_columns) {


            $custom_tiktok_category = get_post_meta($this->current_product_id, '_wt_google_google_product_category', true);

            if ('' == $custom_tiktok_category) {

                $product_id = $this->current_product_id;
                // If variation, take the category from the parent
                if($this->product->is_type('variation')){
                    $product_id = $this->product->get_parent_id();
                }
                
                $category_path = wp_get_post_terms( $product_id, 'product_cat', array('fields' => 'all'));

                $tiktok_product_category = [];
                foreach ($category_path as $category) {
                    $tiktok_category_id = get_term_meta($category->term_id, 'wt_google_category', true);
                    if ($tiktok_category_id) {

                        $tiktok_category_list = wp_cache_get('wt_fbfeed_google_product_categories_array');

                        if (false === $tiktok_category_list) {
                            $tiktok_category_list = Webtoffee_Product_Feed_Sync_Fruugo::get_category_array();
                            wp_cache_set('wt_fbfeed_google_product_categories_array', $tiktok_category_list, '', WEEK_IN_SECONDS);
                        }


                        $tiktok_category = isset($tiktok_category_list[$tiktok_category_id]) ? $tiktok_category_list[$tiktok_category_id] : '';
                        if ('' !== $tiktok_category) {
                            $tiktok_product_category[] = $tiktok_category;
                        }
                    }
                }


                $tiktok_product_category = empty($tiktok_product_category) ? '' : implode(', ', $tiktok_product_category);
            } else {

                $tiktok_category_list = wp_cache_get('wt_fbfeed_google_product_categories_array');

                if (false === $tiktok_category_list) {
                    $tiktok_category_list = Webtoffee_Product_Feed_Sync_Fruugo::get_category_array();
                    wp_cache_set('wt_fbfeed_google_product_categories_array', $tiktok_category_list, '', WEEK_IN_SECONDS);
                }

                $tiktok_product_category = $tiktok_category_list[$custom_tiktok_category];
            }

            return apply_filters('wt_feed_filter_product_tiktok_category', $tiktok_product_category, $this->product);
        }

        
        public function Category($catalog_attr, $product_attr, $export_columns) {


            $custom_fruugo_category = get_post_meta($this->current_product_id, '_wt_fruugo_product_category', true);

            if ( ! $custom_fruugo_category ) {

                $product_id = $this->current_product_id;
                // If variation, take the category from the parent
                if($this->product->is_type('variation')){
                    $product_id = $this->product->get_parent_id();
                }
                
                $category_path = wp_get_post_terms( $product_id, 'product_cat', array('fields' => 'all'));

                $fruugo_product_category = [];
                foreach ($category_path as $category) {
                    $fruugo_category_id = get_term_meta($category->term_id, 'wt_fruugo_category', true);
                    if ($fruugo_category_id) {

                        $fruugo_category_list = wp_cache_get('wt_fbfeed_fruugo_product_categories_array');

                        if (false === $fruugo_category_list) {
                            $fruugo_category_list = Webtoffee_Product_Feed_Sync_Fruugo::get_category_array();
                            wp_cache_set('wt_fbfeed_fruugo_product_categories_array', $fruugo_category_list, '', WEEK_IN_SECONDS);
                        }


                        $fruugo_category = isset($fruugo_category_list[$fruugo_category_id]) ? $fruugo_category_list[$fruugo_category_id] : '';
                        if ('' !== $fruugo_category) {
                            $fruugo_product_category[] = $fruugo_category;
                        }
                    }
                }

                $fruugo_product_category = empty($fruugo_product_category) ? '' : implode(', ', $fruugo_product_category);
            } else {

                $fruugo_category_list = wp_cache_get('wt_fbfeed_fruugo_product_categories_array');

                if (false === $fruugo_category_list) {
                    $fruugo_category_list = Webtoffee_Product_Feed_Sync_Fruugo::get_category_array();
                    wp_cache_set('wt_fbfeed_fruugo_product_categories_array', $fruugo_category_list, '', WEEK_IN_SECONDS);
                }

                $fruugo_product_category = $fruugo_category_list[$custom_fruugo_category];
            }
            
            // Fruugo allows only one category to pass in the feed
            if ( !empty( $fruugo_product_category ) ) {
                $product_category = is_scalar( $fruugo_product_category ) ? explode( ',', $fruugo_product_category ) : $fruugo_product_category;
                $fruugo_product_category = $product_category[0];
                $fruugo_product_category = str_replace(
                    array(' >> ', ' > '),
                    ' - ',
                    $fruugo_product_category
                );
            }

            return apply_filters('wt_feed_filter_product_fruugo_category', $fruugo_product_category, $this->product);
        }                
        
        /**
         * Get product type.
         *
         * @return mixed|void
         */
        public function product_type($catalog_attr, $product_attr, $export_columns) {

            $id = ( $this->product->is_type('variation') ? $this->product->get_parent_id() : $this->product->get_id() );

            $separator = apply_filters('wt_feed_product_type_separator', ' - ');
            $product_categories = '';
            $term_list = get_the_terms($id, 'product_cat');

            if (is_array($term_list)) {
                $col = array_column($term_list, "term_id");
                array_multisort($col, SORT_ASC, $term_list);
                $term_list = array_column($term_list, "name");                
                $product_categories = implode(' > ', $term_list);
            }


            return apply_filters('wt_feed_filter_product_local_category', $product_categories, $this->product);
        }

        /**
         * Get product full category.
         *
         * @return mixed|void
         */
        public function product_full_cat($catalog_attr, $product_attr, $export_columns) {

            $id = ( $this->product->is_type('variation') ? $this->product->get_parent_id() : $this->product->get_id() );

            $separator = apply_filters('wt_feed_product_type_separator', ' - ', $this->product);

            $product_type = wp_strip_all_tags(wc_get_product_category_list($id, $separator));

            return apply_filters('wt_feed_filter_product_local_category', $product_type, $this->product);
        }

        /**
         * Get product URL.
         *
         * @return mixed|void
         */
        public function link($catalog_attr, $product_attr, $export_columns) {
            $link = $this->product->get_permalink();

            return apply_filters('wt_feed_filter_product_link', $link, $this->product);
        }

        /**
         * Get product parent URL.
         *
         * @return mixed|void
         */
        public function parent_link($catalog_attr, $product_attr, $export_columns) {
            $link = $this->product->get_permalink();
            if ($this->product->is_type('variation')) {
                $link = $this->parent_product->get_permalink();
            }

            return apply_filters('wt_feed_filter_product_parent_link', $link, $this->product);
        }

        /**
         * Get product Canonical URL.
         *
         * @return mixed|void
         */
        public function canonical_link($catalog_attr, $product_attr, $export_columns) {
            //TODO: check if SEO plugin installed then return SEO canonical URL
            $canonical_link = $this->parent_link();

            return apply_filters('wt_feed_filter_product_canonical_link', $canonical_link, $this->product);
        }

        /**
         * Get external product URL.
         *
         * @return mixed|void
         */
        public function ex_link($catalog_attr, $product_attr, $export_columns) {
            $ex_link = '';
            if ($this->product->is_type('external')) {
                $ex_link = $this->product->get_product_url();
            }

            return apply_filters('wt_feed_filter_product_ex_link', $ex_link, $this->product);
        }

        /**
         * Get Formatted URL
         *
         * @param string $url
         *
         * @return string
         */
        public static function wt_feed_get_formatted_url($url = '') {
            if (!empty($url)) {
                if (substr(trim($url), 0, 4) === 'http' || substr(trim($url),
                                0,
                                3) === 'ftp' || substr(trim($url), 0, 4) === 'sftp') {
                    return rtrim($url, '/');
                } else {
                    $base = get_site_url();
                    $url = $base . $url;

                    return rtrim($url, '/');
                }
            }

            return '';
        }

        /**
         * Get product image URL.
         *
         * @return mixed|void
         */
        public function image_link($catalog_attr, $product_attr, $export_columns) {
            $image = '';
            if ($this->product->is_type('variation')) {
                // Variation product type
                if (has_post_thumbnail($this->product->get_id())) {
                    $getImage = wp_get_attachment_image_src(get_post_thumbnail_id($this->product->get_id()), 'single-post-thumbnail');
                    $image = self::wt_feed_get_formatted_url($getImage[0]);
                } elseif (has_post_thumbnail($this->product->get_parent_id())) {
                    $getImage = wp_get_attachment_image_src(get_post_thumbnail_id($this->product->get_parent_id()), 'single-post-thumbnail');
                    $image = self::wt_feed_get_formatted_url($getImage[0]);
                }
            } elseif (has_post_thumbnail($this->product->get_id())) { // All product type except variation
                $getImage = wp_get_attachment_image_src(get_post_thumbnail_id($this->product->get_id()), 'single-post-thumbnail');
                $image = isset($getImage[0]) ? self::wt_feed_get_formatted_url($getImage[0]) : '';
            }
            if ('' === $image) {
                $image = 'https://via.placeholder.com/300';
            }
            return apply_filters('wt_feed_filter_product_image', $image, $this->product);
        }

        /**
         * Get product featured image URL.
         *
         * @return mixed|void
         */
        public function feature_image($catalog_attr, $product_attr, $export_columns) {

            $id = ( $this->product->is_type('variation') ? $this->product->get_parent_id() : $this->product->get_id() );

            $getImage = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'single-post-thumbnail');
            $image = isset($getImage[0]) ? self::wt_feed_get_formatted_url($getImage[0]) : '';

            return apply_filters('wt_feed_filter_product_feature_image', $image, $this->product);
        }

        /**
         * Get product featured image URL.
         *
         * @return mixed|void
         */
        public function variant_image($catalog_attr, $product_attr, $export_columns) {

            $image = '';
            if($this->product->is_type('variation')){

                $getImage = wp_get_attachment_image_src(get_post_thumbnail_id($this->product->get_id()), 'thumbnail');
                $image = isset($getImage[0]) ? self::wt_feed_get_formatted_url($getImage[0]) : '';
            }
            return apply_filters('wt_feed_filter_product_variant_image', $image, $this->product);
        }   
        
        /**
         * Get variation attrs.
         *
         * @return mixed|void
         */
        public function wt_variation_option($catalog_attr, $product_attr, $export_columns, $var_id = 1) {

            $variant_attr = '';
            if($this->product->is_type('variation')){

                $variation_attrs = $this->product->get_variation_attributes();
                $var_attrs = array_values($variation_attrs);
                $attr_key = $var_id - 1 ; // Index starts from 0.
                $variant_attr = isset( $var_attrs[$attr_key] ) ? $var_attrs[$attr_key] : '';
            }
            return apply_filters('wt_feed_filter_product_variant_attr', $variant_attr, $this->product);
        } 
        
        public function Attribute1($catalog_attr, $product_attr, $export_columns) {
            return $this->wt_variation_option($catalog_attr, $product_attr, $export_columns, 1);
        }
        public function Attribute2($catalog_attr, $product_attr, $export_columns) {
            return $this->wt_variation_option($catalog_attr, $product_attr, $export_columns, 2);
        }
        public function Attribute3($catalog_attr, $product_attr, $export_columns) {
            return $this->wt_variation_option($catalog_attr, $product_attr, $export_columns, 3);
        }
        public function Attribute4($catalog_attr, $product_attr, $export_columns) {
            return $this->wt_variation_option($catalog_attr, $product_attr, $export_columns, 4);
        }
        public function Attribute5($catalog_attr, $product_attr, $export_columns) {
            return $this->wt_variation_option($catalog_attr, $product_attr, $export_columns, 5);
        }
        public function Attribute6($catalog_attr, $product_attr, $export_columns) {
            return $this->wt_variation_option($catalog_attr, $product_attr, $export_columns, 6);
        }
        
        public function Attribute7($catalog_attr, $product_attr, $export_columns) {
            return $this->wt_variation_option($catalog_attr, $product_attr, $export_columns, 7);
        }
        
        public function Attribute8($catalog_attr, $product_attr, $export_columns) {
            return $this->wt_variation_option($catalog_attr, $product_attr, $export_columns, 8);
        }
        
        public function Attribute9($catalog_attr, $product_attr, $export_columns) {
            return $this->wt_variation_option($catalog_attr, $product_attr, $export_columns, 9);
        }
        
        public function AttributeColor($catalog_attr, $product_attr, $export_columns) {
            $color_val = '';
            if($this->product->is_type('variation')){
                // Loop through product attributes for variations for this variable product
                foreach( $this->product->get_variation_attributes() as $taxonomy_slug => $taxonomy_val ){
                    if (strpos($taxonomy_slug, 'color') !== false) {
                        $color_val = $taxonomy_val;
                    }
                }
            }
            return apply_filters('wt_feed_product_attr_color', $color_val, $this->product);
        }
        public function AttributeSize($catalog_attr, $product_attr, $export_columns) {
            $size_val = '';
                       if($this->product->is_type('variation')){
                // Loop through product attributes for variations for this variable product
                foreach( $this->product->get_variation_attributes() as $taxonomy_slug => $taxonomy_val ){
                    if (strpos($taxonomy_slug, 'size') !== false) {
                        $size_val = $taxonomy_val;
                    }
                }
            }

            return apply_filters('wt_feed_product_attr_size', $size_val, $this->product);
        }
        
        public static function get_product_gallery($product) {
            $imgUrls = [];
            $attachmentIds = [];

            if ($product->is_type('variation')) {

                /**
                 * If any Variation Gallery Image plugin not installed then get Variable Product Additional Image Ids .
                 */
                $parent_prod = wc_get_product($product->get_parent_id());
                if (is_object($parent_prod)) {
                    $attachmentIds = $parent_prod->get_gallery_image_ids();
                }
            }

            /**
             * Get Variable Product Gallery Image ids if Product is not a variation
             * or variation does not have any gallery images
             */
            if (empty($attachmentIds)) {
                $attachmentIds = $product->get_gallery_image_ids();
            }

            if ($attachmentIds && is_array($attachmentIds)) {
                $mKey = 1;
                foreach ($attachmentIds as $attachmentId) {
                    $imgUrls[$mKey] = Wt_Pf_IE_Basic_Helper::feed_get_formatted_url(wp_get_attachment_url($attachmentId));
                    $mKey++;
                }
            }

            return $imgUrls;
        }

        /**
         * Get product images (comma separated URLs).
         *
         * @return mixed|void
         */
        public function images($catalog_attr, $product_attr, $export_columns, $additionalImg = '') {
            $imgUrls = self::get_product_gallery($this->product);
            $separator = apply_filters('wt_feed_filter_category_separator', ' > ', $this->product);

            // Return Specific Additional Image URL
            if ('' !== $additionalImg) {
                if (array_key_exists($additionalImg, $imgUrls)) {
                    $images = $imgUrls[$additionalImg];
                } else {
                    $images = '';
                }
            } else {

                $images = implode($separator, array_filter($imgUrls));
            }

            return apply_filters('wt_feed_filter_product_images', $images, $this->product);
        }

        public function Imageurl1($catalog_attr, $product_attr, $export_columns) {
            return $this->image_link($catalog_attr, $product_attr, $export_columns);
        }

        public function Imageurl2($catalog_attr, $product_attr, $export_columns) {
            return $this->images($catalog_attr, $product_attr, $export_columns, 1);
        }

        public function Imageurl3($catalog_attr, $product_attr, $export_columns) {
            return $this->images($catalog_attr, $product_attr, $export_columns, 2);
        }


        public function condition($catalog_attr, $product_attr, $export_columns) {

            $custom_condition = get_post_meta($this->product->get_id(), '_wt_feed_condition', true);

            if ('' == $custom_condition) {
                $custom_condition = get_post_meta($this->product->get_id(), '_wt_tiktok_condition', true);
            }

            $condition = ('' == $custom_condition) ? 'new' : $custom_condition;
            return apply_filters('wt_feed_product_condition', $condition, $this->product);
        }

        public function age_group($catalog_attr, $product_attr, $export_columns) {

            $age_group = get_post_meta($this->product->get_id(), '_wt_feed_agegroup', true);

            if ('' == $age_group) {
                $age_group = get_post_meta($this->product->get_id(), '_wt_tiktok_agegroup', true);
            }
            return apply_filters('wt_feed_tiktok_product_age_group', $age_group, $this->product);
        }

        public function material($catalog_attr, $product_attr, $export_columns) {

            $material = get_post_meta($this->product->get_id(), '_wt_feed_material', true);
            if ('' == $material) {
                $material = get_post_meta($this->product->get_id(), '_wt_tiktok_material', true);
            }
            return apply_filters('wt_feed_product_tiktok_material', $material, $this->product);
        }

        public function pattern($catalog_attr, $product_attr, $export_columns) {

            $pattern = get_post_meta($this->product->get_id(), '_wt_feed_pattern', true);
            if ('' == $pattern) {
                $pattern = get_post_meta($this->product->get_id(), '_wt_tiktok_pattern', true);
            }
            return apply_filters('wt_feed_product_tiktok_pattern', $pattern, $this->product);
        }

        public function unit_pricing_measure($catalog_attr, $product_attr, $export_columns) {

            $unit_pricing_measure = get_post_meta($this->product->get_id(), '_wt_feed_unit_pricing_measure', true);
            if ('' == $unit_pricing_measure) {
                $unit_pricing_measure = get_post_meta($this->product->get_id(), '_wt_tiktok_unit_pricing_measure', true);
            }
            return apply_filters('wt_feed_product_tiktok_unit_pricing_measure', $unit_pricing_measure, $this->product);
        }

        public function unit_pricing_base_measure($catalog_attr, $product_attr, $export_columns) {

            $unit_pricing_base_measure = get_post_meta($this->product->get_id(), '_wt_feed_unit_pricing_base_measure', true);
            if ('' == $unit_pricing_base_measure) {
                $unit_pricing_base_measure = get_post_meta($this->product->get_id(), '_wt_tiktok_unit_pricing_base_measure', true);
            }
            return apply_filters('wt_feed_product_tiktok_unit_pricing_base_measure', $unit_pricing_base_measure, $this->product);
        }

        public function energy_efficiency_class($catalog_attr, $product_attr, $export_columns) {

            $energy_efficiency_class = get_post_meta($this->product->get_id(), '_wt_feed_energy_efficiency_class', true);
            if ('' == $energy_efficiency_class) {
                $energy_efficiency_class = get_post_meta($this->product->get_id(), '_wt_tiktok_energy_efficiency_class', true);
            }
            return apply_filters('wt_feed_product_tiktok_energy_efficiency_class', $energy_efficiency_class, $this->product);
        }

        public function min_energy_efficiency_class($catalog_attr, $product_attr, $export_columns) {

            $min_energy_efficiency_class = get_post_meta($this->product->get_id(), '_wt_feed_min_energy_efficiency_class', true);
            if ('' == $min_energy_efficiency_class) {
                $min_energy_efficiency_class = get_post_meta($this->product->get_id(), '_wt_tiktok_min_energy_efficiency_class', true);
            }
            return apply_filters('wt_feed_product_tiktok_min_energy_efficiency_class', $min_energy_efficiency_class, $this->product);
        }

        public function max_energy_efficiency_class($catalog_attr, $product_attr, $export_columns) {

            $max_energy_efficiency_class = get_post_meta($this->product->get_id(), '_wt_feed_max_energy_efficiency_class', true);
            if ('' == $max_energy_efficiency_class) {
                $max_energy_efficiency_class = get_post_meta($this->product->get_id(), '_wt_tiktok_max_energy_efficiency_class', true);
            }
            return apply_filters('wt_feed_product_tiktok_max_energy_efficiency_class', $max_energy_efficiency_class, $this->product);
        }

        public function brand($catalog_attr, $product_attr, $export_columns) {


            $id = ( $this->product->is_type('variation') ? $this->product->get_parent_id() : $this->product->get_id() );
            $custom_brand = get_post_meta($this->current_product_id, '_wt_feed_brand', true);
            if ( !$custom_brand ) {
                $custom_brand = get_post_meta($this->product->get_id(), '_wt_tiktok_brand', true);
            }
            if ( !$custom_brand ) {

                $brand = get_the_term_list($id, 'product_brand', '', ', ');

                $has_brand = true;
                if (is_wp_error($brand) || false === $brand) {
                    $has_brand = false;
                }

                if (!$has_brand && is_plugin_active('perfect-woocommerce-brands/perfect-woocommerce-brands.php')) {
                    $brand = get_the_term_list($id, 'pwb-brand', '', ', ');
                }

                $string = is_wp_error($brand) || !$brand ? wp_strip_all_tags(self::get_store_name()) : self::clean_string($brand);
                $length = 100;
                if (extension_loaded('mbstring')) {

                    if (mb_strlen($string, 'UTF-8') <= $length) {
                        return apply_filters('wt_feed_filter_product_brand', $string, $this->product);
                    }

                    $length -= mb_strlen('...', 'UTF-8');

                    $brand_string = mb_substr($string, 0, $length, 'UTF-8') . '...';
                    return apply_filters('wt_feed_filter_product_brand', $brand_string, $this->product);
                } else {

                    $string = filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
                    $string = filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

                    if (strlen($string) <= $length) {
                        return apply_filters('wt_feed_filter_product_brand', $string, $this->product);
                    }

                    $length -= strlen('...');

                    $brand_string = substr($string, 0, $length) . '...';
                    return apply_filters('wt_feed_filter_product_brand', $brand_string, $this->product);
                }
            } else {
                return apply_filters('wt_feed_filter_product_brand', $custom_brand, $this->product);
            }
        }

        public function identifier_code($catalog_attr, $product_attr, $export_columns) {

            $custom_gtin = get_post_meta($this->product->get_id(), '_wt_feed_gtin', true);
            if (!$custom_gtin) {
                $custom_gtin = get_post_meta($this->product->get_id(), '_wt_tiktok_gtin', true);
            }
            $gtin = ('' == $custom_gtin) ? '' : $custom_gtin;
            return apply_filters('wt_feed_product_identifier_code', $gtin, $this->product);
        }

        public function mpn($catalog_attr, $product_attr, $export_columns) {

            $custom_mpn = get_post_meta($this->product->get_id(), '_wt_feed_mpn', true);
            if ('' == $custom_mpn) {
                $custom_mpn = get_post_meta($this->product->get_id(), '_wt_tiktok_mpn', true);
            }
            $mpn = ('' == $custom_mpn) ? '' : $custom_mpn;
            return apply_filters('wt_feed_product_mpn', $mpn, $this->product);
        }

        public function identifier_exists($catalog_attr, $product_attr, $export_columns) {

            $identifier_exists = 'no';
            if (isset($export_columns['sku']) || isset($export_columns['brand'])) {
                $identifier_exists = 'yes';
            }
            return apply_filters('wt_feed_product_identifier_exists', $identifier_exists, $this->product);
        }

        public function type($catalog_attr, $product_attr, $export_columns) {
            return apply_filters('wt_feed_filter_product_type', $this->product->get_type(), $this->product);
        }

        public function is_bundle($catalog_attr, $product_attr, $export_columns) {
            $is_bundle = 'no';
            if ($this->product->is_type('bundle') || $this->product->is_type('yith_bundle') || $this->product->is_type('bopobb') ) {
                $is_bundle = 'yes';
            }

            return apply_filters('wt_feed_filter_product_is_bundle', $is_bundle, $this->product);
        }

        public function multipack($catalog_attr, $product_attr, $export_columns) {
            $multi_pack = '';
            if ($this->product->is_type('grouped')) {
                $multi_pack = (!empty($this->product->get_children()) ) ? count($this->product->get_children()) : '';
            }

            return apply_filters('wt_feed_filter_product_is_multipack', $multi_pack, $this->product);
        }

        public function visibility($catalog_attr, $product_attr, $export_columns) {
            return apply_filters('wt_feed_filter_product_visibility', $this->product->get_catalog_visibility(), $this->product);
        }

        public function rating_total($catalog_attr, $product_attr, $export_columns) {
            return apply_filters('wt_feed_filter_product_rating_total', $this->product->get_rating_count(), $this->product);
        }

        public function rating_average($catalog_attr, $product_attr, $export_columns) {
            return apply_filters('wt_feed_filter_product_rating_average', $this->product->get_average_rating(), $this->product);
        }

        public function total_sold($catalog_attr, $product_attr, $export_columns) {
            //Todo
        }

        public function tags($catalog_attr, $product_attr, $export_columns) {

            $id = ( $this->product->is_type('variation') ? $this->product->get_parent_id() : $this->product->get_id() );

            /**
             * Separator for multiple tags
             *
             * @param string                     $separator
             * @param array                      $config
             * @param WC_Abstract_Legacy_Product $product
             *
             * @since 1.0.0
             */
            $separator = apply_filters('wt_feed_tags_separator', ',', $this->product);

            $tags = strip_tags( get_the_term_list($id, 'product_tag', '', $separator, '') );

            return apply_filters('wt_feed_filter_product_tags', $tags, $this->product);
        }

        public function item_group_id($catalog_attr, $product_attr, $export_columns) {

            $id = ( $this->product->is_type('variation') ? $this->product->get_parent_id() : $this->product->get_id() );

            return apply_filters('wt_feed_filter_product_item_group_id', $id, $this->product);
        }

        public function sku($catalog_attr, $product_attr, $export_columns) {
            return apply_filters('wt_feed_filter_product_sku', $this->product->get_sku(), $this->product);
        }

        public function sku_id($catalog_attr, $product_attr, $export_columns) {


            $id = $this->product->get_id();
            $sku = !empty($this->product->get_sku()) ? $this->product->get_sku() : '';
            $sku_id = $sku . $id;

            return apply_filters('wt_feed_filter_product_sku_id', $sku_id, $this->product);
        }

        public static function get_store_name() {

            $url = get_bloginfo('name');
            return ( $url) ? ( $url ) : 'My Store';
        }

        /**
         * Clean up strings for FB Graph POSTing.
         * This function should will:
         * 1. Replace newlines chars/nbsp with a real space
         * 2. strip_tags()
         * 3. trim()
         *
         * @access public
         * @param String string
         * @return string
         */
        public static function clean_string($string) {
            $string = do_shortcode($string);
            $string = str_replace(array('&amp%3B', '&amp;'), '&', $string);
            $string = str_replace(array("\r", '&nbsp;', "\t"), ' ', $string);
            $string = wp_strip_all_tags($string, false); // true == remove line breaks
            return $string;
        }



        public function availability($catalog_attr, $product_attr, $export_columns) {
            $status = $this->product->get_stock_status();
            if ('instock' === $status) {
                $status = 'INSTOCK';
            } elseif ('outofstock' === $status) {
                $status = 'OUTOFSTOCK';
            } elseif ('onbackorder' === $status) {
                $status = 'INSTOCK';
            } elseif ('preorder' === $status) {
                $status = 'INSTOCK';
            }else{
                $status = 'NOTAVAILABLE';
            }


            return apply_filters('wt_feed_filter_product_availability', $status, $this->product);
        }

        public function availability_date($catalog_attr, $product_attr, $export_columns) {

            $availability_date = get_post_meta($this->product->get_id(), '_wt_feed_availability_date', true);

            if ( $availability_date ) {
                $availability_date = gmdate('c', strtotime($availability_date));
            }

            return apply_filters('wt_feed_filter_product_availability_date', $availability_date, $this->product);
        }

        public function add_to_cart_link($catalog_attr, $product_attr, $export_columns) {
            $url = $this->link();
            $suffix = 'add-to-cart=' . $this->product->get_id();

            $add_to_cart_link = wt_feed_make_url_with_parameter($url, $suffix);

            return apply_filters('wt_feed_filter_product_add_to_cart_link', $add_to_cart_link, $this->product);
        }

        public function quantity($catalog_attr, $product_attr, $export_columns) {
            $quantity = $this->product->get_stock_quantity();
            $status = $this->product->get_stock_status();

            //when product is outofstock , and it's quantity is empty, set quantity to 0
            if ('outofstock' === $status && $quantity === null) {
                $quantity = 0;
            }

            if ($this->product->is_type('variable') && $this->product->has_child()) {
                $visible_children = $this->product->get_visible_children();
                $qty = array();
                foreach ($visible_children as $child) {
                    $childQty = get_post_meta($child, '_stock', true);
                    $qty[] = (int) $childQty;
                }

                $quantity = array_sum($qty);
            }

            if ( 'outofstock' !== $status && ( 0 === $quantity || null === $quantity ) ) {
                $quantity = 1;
            }

            return apply_filters('wt_feed_filter_product_quantity', $quantity, $this->product);
        }

        /**
         * Get Store Currency.
         *
         * @return mixed|void
         */
        public function currency($catalog_attr, $product_attr, $export_columns) {

            $currency = get_option('woocommerce_currency');

            if (class_exists('WCML_Multi_Currency') && !empty($this->form_data['post_type_form_data']['item_post_currency'])) {
                $currency = $this->form_data['post_type_form_data']['item_post_currency'];
            }

            return apply_filters('wt_feed_filter_product_currency', $currency, $this->product);
        }

        /**
         * Get Product Sale Price start date.
         *
         * @return mixed|void
         */
        public function sale_price_sdate($catalog_attr, $product_attr, $export_columns) {
            $startDate = $this->product->get_date_on_sale_from();
            if (is_object($startDate)) {
                $sale_price_sdate = $startDate->date_i18n();
            } else {
                $sale_price_sdate = '';
            }

            return apply_filters('wt_feed_filter_product_sale_price_sdate', $sale_price_sdate, $this->product);
        }

        /**
         * Get Product Sale Price End Date.
         *
         * @return mixed|void
         */
        public function sale_price_edate($catalog_attr, $product_attr, $export_columns) {
            $endDate = $this->product->get_date_on_sale_to();
            if (is_object($endDate)) {
                $sale_price_edate = $endDate->date_i18n();
            } else {
                $sale_price_edate = "";
            }

            return apply_filters('wt_feed_filter_product_sale_price_edate', $sale_price_edate, $this->product);
        }

        public function first_variation_price() {

            $children = $this->product->get_visible_children();
            $price = $this->product->get_variation_price();
            if (isset($children[0]) && !empty($children[0])) {
                $variation = wc_get_product($children[0]);
                $price = $variation->get_price();
            }

            return apply_filters('wt_feed_filter_product_first_variation_price', $price, $this->product);
        }

        public function get_converted_price($price, $selected_currency) {

            if ($selected_currency !== get_woocommerce_currency() && $price > 0) {
                $wcml_mc = new WCML_Multi_Currency();
                $currencies = $wcml_mc->get_currencies(true);

                $woo_currencies = get_woocommerce_currencies();

                if (!empty($woo_currencies[$selected_currency]) && !empty($currencies[$selected_currency])) {
                    $price = $price * $currencies[$selected_currency]['rate'];
                }
            }
            return $price;
        }

        public function price($catalog_attr, $product_attr, $export_columns) {
            
            if (isset($this->form_data['advanced_form_data']['wt_pf_file_as']) && 'xml' === $this->form_data['advanced_form_data']['wt_pf_file_as']) {
                $price = array(
                    'Currency' => $this->currency($catalog_attr, $product_attr, $export_columns),
                    'NormalPriceWithoutVAT' => $this->price_without_vat($catalog_attr, $product_attr, $export_columns),
                    'VATRate' => $this->vat_rate($catalog_attr, $product_attr, $export_columns)
                );
                if( strpos($this->fruugo_xml_skipfeed_keys['vat_rate'], 'wt_static_map_vl:') !== false) {
                    $price['VATRate'] = str_replace('wt_static_map_vl:', '', $this->fruugo_xml_skipfeed_keys['vat_rate']);
                }else{
                    $price['VATRate'] = $this->vat_rate($catalog_attr, $product_attr, $export_columns);
                }
            }else{ 
            
                $price = $this->product->get_regular_price();

                if ($this->product->is_type('variable')) {
                    $price = $this->first_variation_price();
                }

                $selected_currency = get_woocommerce_currency();
                if (class_exists('WCML_Multi_Currency') && !empty($this->form_data['post_type_form_data']['item_post_currency'])) {
                    $selected_currency = $this->form_data['post_type_form_data']['item_post_currency'];
                    $price = $this->get_converted_price($price, $selected_currency);
                }

                if ($price > 0) {
                    $price = wc_format_decimal($price, 2);
                }
            }
            return apply_filters('wt_feed_filter_product_price', $price, $this->product);
        }

        public function current_price($catalog_attr, $product_attr, $export_columns) {
            $price = $this->product->get_price();

            $selected_currency = get_woocommerce_currency();
            if (class_exists('WCML_Multi_Currency') && !empty($this->form_data['post_type_form_data']['item_post_currency'])) {
                $selected_currency = $this->form_data['post_type_form_data']['item_post_currency'];
                $price = $this->get_converted_price($price, $selected_currency);
            }

            if ($price > 0) {
                $price = wc_format_decimal($price, 2);
            }
            return apply_filters('wt_feed_filter_product_current_price', $price, $this->product);
        }

        public function sale_price($catalog_attr, $product_attr, $export_columns) {
            $price = $this->product->get_sale_price();

            $selected_currency = get_woocommerce_currency();
            if (class_exists('WCML_Multi_Currency') && !empty($this->form_data['post_type_form_data']['item_post_currency'])) {
                $selected_currency = $this->form_data['post_type_form_data']['item_post_currency'];
                $price = $this->get_converted_price($price, $selected_currency);
            }

            if ($price > 0) {
                $price = wc_format_decimal($price, 2);
            }
            return apply_filters('wt_feed_filter_product_sale_price', $price, $this->product);
        }

        public function price_with_tax($catalog_attr, $product_attr, $export_columns) {

            $tprice = $this->product->get_regular_price();
            $price = wc_get_price_including_tax($this->product, array('price' => $tprice));
            if ($price > 0) {
                $price = wc_format_decimal($price, 2);
            }
            return apply_filters('wt_feed_filter_product_price_with_tax', $price, $this->product);
        }

        public function current_price_with_tax($catalog_attr, $product_attr, $export_columns) {
            $cprice = $this->product->get_price();
            $price = wc_get_price_including_tax($this->product, array('price' => $cprice));
            if ($price > 0) {
                $price = wc_format_decimal($price, 2);
            }
            return apply_filters('wt_feed_filter_product_current_price_with_tax', $price, $this->product);
        }

        public function sale_price_with_tax($catalog_attr, $product_attr, $export_columns) {
            $sprice = $this->product->get_sale_price();
            $price = wc_get_price_including_tax($this->product, array('price' => $sprice));
            if ($price > 0) {
                $price = wc_format_decimal($price, 2);
            }
            return apply_filters('wt_feed_filter_product_sale_price_with_tax', $price, $this->product);
        }

        /**
         * Get Product Weight.
         *
         * @return mixed|void
         */
        public function package_weight($catalog_attr, $product_attr, $export_columns) {
                        
            $package_weight = $this->product->get_weight();
            $package_weight = ( $package_weight > 0 ) ? round( $package_weight ) : '';
            return apply_filters('wt_feed_filter_product_weight', $package_weight, $this->product);

        }

        /**
         * Get Weight Unit.
         *
         * @return mixed|void
         */
        public function weight_unit($catalog_attr, $product_attr, $export_columns) {
            return apply_filters('wt_feed_filter_product_weight_unit', get_option('woocommerce_weight_unit'), $this->product);
        }

        /**
         * Get Product Width.
         *
         * @return mixed|void
         */
        public function package_width($catalog_attr, $product_attr, $export_columns) {
            return apply_filters('wt_feed_filter_product_width', $this->product->get_width(), $this->product);
        }

        /**
         * Get Product Height.
         *
         * @return mixed|void
         */
        public function package_height($catalog_attr, $product_attr, $export_columns) {
            return apply_filters('wt_feed_filter_product_height', $this->product->get_height(), $this->product);
        }

        /**
         * Get Product Length.
         *
         * @return mixed|void
         */
        public function package_length($catalog_attr, $product_attr, $export_columns) {
            return apply_filters('wt_feed_filter_product_length', $this->product->get_length(), $this->product);
        }

        /**
         * Tiktok Formatted Shipping info
         *
         */
        public function shipping($catalog_attr, $product_attr, $export_columns, $key = '') {


            $shipping_details = $this->get_shipping();
            $shipping_details_xml = array();
            $shipping_str = '';
            if (isset($shipping_details) && is_array($shipping_details)) {
                foreach ($shipping_details as $k => $shipping_item) {

                    unset($shipping_details['zone_name']);

                    if (isset($shipping_item['region']) && empty($shipping_item['region'])) {
                        unset($shipping_item['region']);
                    }
                    
                    $shipping_child = '';
                    foreach ($shipping_item as $shipping_item_attr => $shipping_value) {

                        if ('price' === $shipping_item_attr) {
                            $shipping_value = number_format($shipping_value, 2) . ' ' . get_woocommerce_currency();
                        }
                        if (( 'zone_name' !== $shipping_item_attr)) {
                            $shipping_details_xml[$k][$shipping_item_attr] = $shipping_value;
                        }
                        if ('postal_code' !== $shipping_item_attr) {
                            $shipping_child .= $shipping_value . ":";
                        }
                    }
                    $shipping_child = trim($shipping_child, ":");

                    //Add separator for multiple shipping method -  comma for tiktok
                    $shipping_str .= $shipping_child . ',';
                }

                $shipping_str = trim($shipping_str, ',');
            }

            if (isset($this->form_data['advanced_form_data']['wt_pf_file_as']) && 'xml' === $this->form_data['advanced_form_data']['wt_pf_file_as']) {
                return apply_filters('wt_feed_tiktok_product_shipping_xml', $shipping_details_xml, $shipping_details, $this->product);
            }


            return apply_filters('wt_feed_tiktok_product_shipping', $shipping_str, $shipping_details, $this->product);
        }

        public function shipping_data($catalog_attr, $product_attr, $export_columns) {

            return $this->shipping($catalog_attr, $product_attr, $export_columns);
        }

        /**
         * Get Shipping Cost.
         *
         */
        public function shipping_cost($catalog_attr, $product_attr, $export_columns) {
            //Todo
        }

        /**
         * Get Product Shipping Class
         *
         *
         */
        public function shipping_class($catalog_attr, $product_attr, $export_columns) {
            return apply_filters('wt_feed_filter_product_shipping_class', $this->product->get_shipping_class(), $this->product);
        }

        public function custom_label_0($catalog_attr, $product_attr, $export_columns) {

            $custom_label_0 = get_post_meta($this->product->get_id(), '_wt_feed_custom_label_0', true);

            if ('' == $custom_label_0) {
                $custom_label_0 = get_post_meta($this->product->get_id(), '_wt_facebook_custom_label_0', true);
            }
            if ('' == $custom_label_0) {
                $custom_label_0 = get_post_meta($this->product->get_id(), '_wt_google_custom_label_0', true);
            }
            return apply_filters('wt_feed_product_tiktok_custom_label_0', $custom_label_0, $this->product);
        }

        public function custom_label_1($catalog_attr, $product_attr, $export_columns) {
            $custom_label_1 = get_post_meta($this->product->get_id(), '_wt_feed_custom_label_1', true);

            if ('' == $custom_label_1) {
                $custom_label_1 = get_post_meta($this->product->get_id(), '_wt_facebook_custom_label_1', true);
            }
            if ('' == $custom_label_1) {
                $custom_label_1 = get_post_meta($this->product->get_id(), '_wt_google_custom_label_1', true);
            }
            return apply_filters('wt_feed_product_tiktok_custom_label_1', $custom_label_1, $this->product);
        }

        public function custom_label_2($catalog_attr, $product_attr, $export_columns) {
            $custom_label_2 = get_post_meta($this->product->get_id(), '_wt_feed_custom_label_2', true);

            if ('' == $custom_label_2) {
                $custom_label_2 = get_post_meta($this->product->get_id(), '_wt_facebook_custom_label_2', true);
            }
            if ('' == $custom_label_2) {
                $custom_label_2 = get_post_meta($this->product->get_id(), '_wt_google_custom_label_2', true);
            }
            return apply_filters('wt_feed_product_tiktok_custom_label_2', $custom_label_2, $this->product);
        }

        public function custom_label_3($catalog_attr, $product_attr, $export_columns) {

            $custom_label_3 = get_post_meta($this->product->get_id(), '_wt_feed_custom_label_3', true);

            if ('' == $custom_label_3) {
                $custom_label_3 = get_post_meta($this->product->get_id(), '_wt_facebook_custom_label_3', true);
            }
            if ('' == $custom_label_3) {
                $custom_label_3 = get_post_meta($this->product->get_id(), '_wt_google_custom_label_3', true);
            }
            return apply_filters('wt_feed_product_tiktok_custom_label_3', $custom_label_3, $this->product);
        }

        public function custom_label_4($catalog_attr, $product_attr, $export_columns) {
            $custom_label_4 = get_post_meta($this->product->get_id(), '_wt_feed_custom_label_4', true);

            if ('' == $custom_label_4) {
                $custom_label_4 = get_post_meta($this->product->get_id(), '_wt_facebook_custom_label_4', true);
            }
            if ('' == $custom_label_4) {
                $custom_label_4 = get_post_meta($this->product->get_id(), '_wt_google_custom_label_4', true);
            }
            return apply_filters('wt_feed_product_tiktok_custom_label_4', $custom_label_4, $this->product);
        }

        /**
         * Get author name.
         *
         * @return string
         */
        public function author_name($catalog_attr, $product_attr, $export_columns) {
            $post = get_post($this->product->get_id());

            return get_the_author_meta('user_login', $post->post_author);
        }

        /**
         * Get Author Email.
         *
         * @return string
         */
        public function author_email($catalog_attr, $product_attr, $export_columns) {
            $post = get_post($this->product->get_id());

            return get_the_author_meta('user_email', $post->post_author);
        }

        /**
         * Get Date Created.
         *
         * @return mixed|void
         */
        public function date_created($catalog_attr, $product_attr, $export_columns) {
            $date_created = gmdate('Y-m-d', strtotime($this->product->get_date_created()));

            return apply_filters('wt_feed_filter_product_date_created', $date_created, $this->product);
        }

        /**
         * Get Date updated.
         *
         * @return mixed|void
         */
        public function date_updated($catalog_attr, $product_attr, $export_columns) {
            $date_updated = gmdate('Y-m-d', strtotime($this->product->get_date_modified()));

            return apply_filters('wt_feed_filter_product_date_updated', $date_updated, $this->product);
        }

        /** Get Tiktok Sale Price effective date.
         *
         * @return string
         */
        public function sale_price_effective_date($catalog_attr, $product_attr, $export_columns) {
            $effective_date = '';
            $from = $this->sale_price_sdate($catalog_attr, $product_attr, $export_columns);
            $to = $this->sale_price_edate($catalog_attr, $product_attr, $export_columns);
            if (!empty($from) && !empty($to)) {
                $from = gmdate('c', strtotime($from));
                $to = gmdate('c', strtotime($to));

                $effective_date = $from . '/' . $to;
            }

            return $effective_date;
        }

        public function tax_class($catalog_attr, $product_attr, $export_columns) {
            return apply_filters('wt_feed_filter_product_tax_class', $this->product->get_tax_class(), $this->product);
        }

        public function tax_status($catalog_attr, $product_attr, $export_columns) {
            return apply_filters('wt_feed_filter_product_tax_status', $this->product->get_tax_status(), $this->product);
        }

    }
    
}
