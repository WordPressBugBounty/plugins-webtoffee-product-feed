<?php
/**
 * Product section of the plugin
 *
 * @link          
 *
 * @package  Webtoffee_Product_Feed_Sync_Leguide 
 */
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('Webtoffee_Product_Feed_Sync_Leguide')) {

	class Webtoffee_Product_Feed_Sync_Leguide {

		public $module_id = '';
		public static $module_id_static = '';
		public $module_base = 'leguide';
		public $module_name = 'Webtoffee Product Feed Catlaog for Leguide';
		public $min_base_version = '1.0.0'; /* Minimum `Import export plugin` required to run this add on plugin */
		private $importer = null;
		private $exporter = null;
		private $product_categories = null;
		private $product_tags = null;
		private $product_taxonomies = array();
		private $all_meta_keys = array();
		private $product_attributes = array();
		private $exclude_hidden_meta_columns = array();
		private $found_product_meta = array();
		private $found_product_hidden_meta = array();
		private $selected_column_names = null;

		public function __construct() {
			/**
			 *   Checking the minimum required version of `Import export plugin` plugin available
			 */
			if (!Webtoffee_Product_Feed_Sync_Common_Helper::check_base_version($this->module_base, $this->module_name, $this->min_base_version)) {
				return;
			}
			if (!function_exists('is_plugin_active')) {
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
			}
			if (!is_plugin_active('woocommerce/woocommerce.php')) {
				return;
			}

			$this->module_id = Webtoffee_Product_Feed_Sync::get_module_id($this->module_base);
			self::$module_id_static = $this->module_id;

			add_filter('wt_pf_exporter_post_types_basic', array($this, 'wt_pf_exporter_post_types_basic'), 10, 1);

			add_filter('wt_pf_exporter_alter_filter_fields_basic', array($this, 'exporter_alter_filter_fields'), 10, 3);

			add_filter('wt_pf_exporter_alter_mapping_fields_basic', array($this, 'exporter_alter_mapping_fields'), 10, 3);

			add_filter('wt_pf_exporter_alter_advanced_fields_basic', array($this, 'exporter_alter_advanced_fields'), 10, 3);

			add_filter('wt_pf_exporter_alter_meta_mapping_fields_basic', array($this, 'exporter_alter_meta_mapping_fields'), 10, 3);

			add_filter('wt_pf_exporter_alter_mapping_enabled_fields_basic', array($this, 'exporter_alter_mapping_enabled_fields'), 10, 3);			

			add_filter('wt_pf_exporter_do_export_basic', array($this, 'exporter_do_export'), 10, 7);

			add_filter('wt_pf_feed_category_mapping', array($this, 'map_google_category'), 10, 1);
				
		}
		
		public function map_google_category($form_data) {

			if ( ( isset($form_data['post_type_form_data']['item_type']) &&  $form_data['post_type_form_data']['item_type'] != $this->module_base ) || ( isset($form_data['post_type_form_data']['wt_pf_export_post_type']) &&  $form_data['post_type_form_data']['wt_pf_export_post_type'] != $this->module_base )) {
				return $form_data;
			} else {

				foreach ($form_data['category_mapping_form_data'] as $local_cat => $merchant_cat) {
					if (!empty($merchant_cat)) {
						$term_id = absint(str_replace('cat_mapping_', '', $local_cat));
						$wt_fb_category = absint($merchant_cat);
						update_term_meta($term_id, 'wt_google_category', $wt_fb_category);
					}
				}
				return $form_data;
			}
		}



		public function exporter_do_export($export_data, $base, $step, $form_data, $selected_template_data, $method_export, $batch_offset) {
			if ($this->module_base != $base) {
				return $export_data;
			}

			$this->set_selected_column_names($form_data);

                        include WT_PRODUCT_FEED_PLUGIN_PATH . '/admin/modules/export/wt-product.php';
			include plugin_dir_path(__FILE__) . 'export/export.php';
			$export = new Webtoffee_Product_Feed_Sync_Leguide_Export($this);

			$header_row = $export->prepare_header();

			$data_row = $export->prepare_data_to_export($form_data, $batch_offset, $step);

			$export_data = array(
				'head_data' => $header_row,
				'body_data' => $data_row['data'],
				'total' => $data_row['total'],
			);

			if (isset($data_row['no_post'])) {
				$export_data['no_post'] = $data_row['no_post'];
			}


			return $export_data;
		}

		/**
		 * Adding current post type to export list
		 *
		 */
		public function wt_pf_exporter_post_types_basic($arr) {

			$arr['leguide'] = __('Leguide', 'webtoffee-product-feed-pro');
			return $arr;
		}

		/**
		 * Read txt file which contains facebook taxonomy list
		 *
		 * @return array
		 */
		public static function get_category_array() {
			// Get All Google Taxonomies
					
			$taxonomy = wp_cache_get('wt_iew_feed_google_categories');

			if (false === $taxonomy) {
			
			$fileName = WT_PRODUCT_FEED_PLUGIN_PATH . '/admin/modules/google/data/google_taxonomy.txt';
			$customTaxonomyFile = fopen($fileName, 'r');  // phpcs:ignore
			$taxonomy = array();
			$taxonomy[''] = 'Do not map';
			if ($customTaxonomyFile) {
				// First line contains metadata, ignore it
				fgets($customTaxonomyFile);  // phpcs:ignore
				while ($line = fgets($customTaxonomyFile)) {  // phpcs:ignore
					list( $catId, $cat ) = explode('-', $line);
					$cat_key = absint(trim($catId));
					$cat_val = trim($cat);
					$taxonomy[$cat_key] = $cat_val;
				}
			}
				wp_cache_set('wt_iew_feed_google_categories', $taxonomy, '', WEEK_IN_SECONDS);
			}

			return $taxonomy;
		}

		/**
		 * Get product categories
		 * @return array $categories 
		 */
		private function get_product_categories() {
			if (!is_null($this->product_categories)) {
				return $this->product_categories;
			}
			$out = array();
			$product_categories = get_terms('product_cat', array('hide_empty' => false));
			if (!is_wp_error($product_categories)) {
				$version = get_bloginfo('version');
				foreach ($product_categories as $category) {
					$out[$category->slug] = (( $version < '4.8') ? $category->name : get_term_parents_list($category->term_id, 'product_cat', array('separator' => ' -> ')));
				}
			}
			$this->product_categories = $out;
			return $out;
		}

		private function get_product_tags() {
			if (!is_null($this->product_tags)) {
				return $this->product_tags;
			}
			$out = array();
			$product_tags = get_terms('product_tag');
			if (!is_wp_error($product_tags)) {
				foreach ($product_tags as $tag) {
					$out[$tag->slug] = $tag->name;
				}
			}
			$this->product_tags = $out;
			return $out;
		}

		public static function get_product_statuses() {
			$product_statuses = array('publish', 'private', 'draft', 'pending', 'future');
			return apply_filters('wt_pf_allowed_product_statuses', array_combine($product_statuses, $product_statuses));
		}

		public static function get_product_post_columns() {
			return include plugin_dir_path(__FILE__) . 'data/data-product-post-columns.php';
		}

		public function exporter_alter_mapping_enabled_fields($mapping_enabled_fields, $base, $form_data_mapping_enabled_fields) {
			if ($base == $this->module_base) {
				$mapping_enabled_fields = array();
				$mapping_enabled_fields['availability_price'] = array(__('Availability & Price'), 1);
				$mapping_enabled_fields['unique_product_identifiers'] = array(__('Unique Product Identifiers'), 1);
				$mapping_enabled_fields['detailed_product_attributes'] = array(__('Detailed Product Attributes'), 1);
				$mapping_enabled_fields['tax_shipping'] = array(__('Tax & Shipping'), 1);;
				$mapping_enabled_fields['custom_label_identifiers'] = array(__('Custom Label Attributes'), 1);
				$mapping_enabled_fields['additional_attributes'] = array(__('Additional Attributes'), 1);				
				$mapping_enabled_fields['unit_prices_eu_ch'] = array(__('Unit Prices (EU Countries and Switzerland Only)'), 1);
				$mapping_enabled_fields['energy_labels'] = array(__('Energy Labels'), 1);				
			}
			return $mapping_enabled_fields;
		}

		public function exporter_alter_meta_mapping_fields($fields, $base, $step_page_form_data) {
			if ($base != $this->module_base) {
				return $fields;
			}
			foreach ($fields as $key => $value) {
				switch ($key) {
					case 'availability_price':
						$fields[$key]['fields']['availability'] = 'Stock Status[availability]';
						$fields[$key]['fields']['availability_date'] = 'Availability Date[availability_date]';
						$fields[$key]['fields']['inventory'] = 'Facebook Inventory[inventory]';
						$fields[$key]['fields']['override'] = 'Facebook Override[override]';
						$fields[$key]['fields']['cost_of_goods_sold'] = 'Cost of Goods Sold[cost_of_goods_sold]';
						$fields[$key]['fields']['price'] = 'Regular Price[price]';
						$fields[$key]['fields']['sale_price'] = 'Sale Price[sale_price]';
						$fields[$key]['fields']['sale_price_effective_date'] = 'Sale Price Effective Date[sale_price_effective_date]';
						break;

					case 'unique_product_identifiers':

						$fields[$key]['fields']['brand'] = 'Manufacturer[brand]';
						$fields[$key]['fields']['gtin'] = 'GTIN[gtin]';
						$fields[$key]['fields']['mpn'] = 'MPN[mpn]';
						$fields[$key]['fields']['identifier_exists'] = 'Identifier Exist[identifier_exists]';
						break;

					case 'detailed_product_attributes':

						$fields[$key]['fields']['item_group_id'] = 'Item Group Id[item_group_id]';
						$fields[$key]['fields']['external_seller_id'] = 'External seller ID[external_seller_id]';
						$fields[$key]['fields']['color'] = 'Color[color]';
						$fields[$key]['fields']['gender'] = 'Gender[gender]';
						$fields[$key]['fields']['age_group'] = 'Age Group[age_group]';
						$fields[$key]['fields']['material'] = 'Material[material]';
						$fields[$key]['fields']['pattern'] = 'Pattern[pattern]';
						$fields[$key]['fields']['size'] = 'Size of the item[size]';
						$fields[$key]['fields']['size_type'] = 'Size Type[size_type]';
						$fields[$key]['fields']['size_system'] = 'Size System[size_system]';
						$fields[$key]['fields']['product_length'] = 'Product Length[product_length]';
						$fields[$key]['fields']['product_width'] = 'Product Width[product_width]';
						$fields[$key]['fields']['product_height'] = 'Product Height[product_height]';
						$fields[$key]['fields']['product_weight'] = 'Product Weight[product_weight]';
						$fields[$key]['fields']['product_highlight_1'] = 'Product Highlight 1[product_highlight]';
						$fields[$key]['fields']['product_highlight_2'] = 'Product Highlight 2[product_highlight]';
						$fields[$key]['fields']['product_highlight_3'] = 'Product Highlight 3[product_highlight]';
						$fields[$key]['fields']['product_highlight_4'] = 'Product Highlight 4[product_highlight]';
						$fields[$key]['fields']['product_highlight_5'] = 'Product Highlight 5[product_highlight]';
						$fields[$key]['fields']['product_highlight_6'] = 'Product Highlight 6[product_highlight]';
						$fields[$key]['fields']['product_highlight_7'] = 'Product Highlight 7[product_highlight]';
						$fields[$key]['fields']['product_highlight_8'] = 'Product Highlight 8[product_highlight]';
						$fields[$key]['fields']['product_highlight_9'] = 'Product Highlight 9[product_highlight]';
						$fields[$key]['fields']['product_highlight_10'] = 'Product Highlight 10[product_highlight]';
						$fields[$key]['fields']['section_name'] = 'Section Name (Product Detail)[section_name]';
						$fields[$key]['fields']['attribute_name'] = 'Attribute Name  (Product Detail)[attribute_name]';
						$fields[$key]['fields']['attribute_value'] = 'Attribute Value  (Product Detail)[attribute_value]';
                                                $fields[$key]['fields']['quantity'] = 'Quantity [quantity]';
						break;

					case 'tax_shipping':
						$fields[$key]['fields']['tax'] = 'Tax[tax]';
						$fields[$key]['fields']['tax_country'] = 'Tax Country[tax_country]';
						$fields[$key]['fields']['tax_region'] = 'Tax Region[tax_region]';
						$fields[$key]['fields']['tax_rate'] = 'Tax Rate[tax_rate]';
						$fields[$key]['fields']['tax_ship'] = 'Tax Ship[tax_ship]';
						$fields[$key]['fields']['tax_category'] = 'Tax[tax_category]';
						$fields[$key]['fields']['shipping'] = 'Shipping';
						$fields[$key]['fields']['min_handling_time'] = 'Min Handling Time[min_handling_time]';
						$fields[$key]['fields']['max_handling_time'] = 'Max Handling Time[max_handling_time]';
						$fields[$key]['fields']['min_transit_time'] = 'Min Transit Time[min_transit_time]';
						$fields[$key]['fields']['max_transit_time'] = 'Max Transit Time[max_transit_time]';
						$fields[$key]['fields']['weight'] = 'Shipping Weight[shipping_weight]';
						$fields[$key]['fields']['length'] = 'Shipping Length[shipping_length]';
						$fields[$key]['fields']['width'] = 'Shipping Width[shipping_width]';
						$fields[$key]['fields']['height'] = 'Shipping Height[shipping_height]';
						$fields[$key]['fields']['ships_from_country'] = 'Shipping Country[ships_from_country]';
						$fields[$key]['fields']['shipping_label'] = 'Shipping Label[shipping_label]';
						$fields[$key]['fields']['material_1'] = 'The primary material';
						$fields[$key]['fields']['material_2'] = 'The secondary material';
						$fields[$key]['fields']['material_3'] = 'The tertiary material';

						break;

					case 'custom_label_identifiers':
						$fields[$key]['fields']['custom_label_0'] = 'Custom label 0 [custom_label_0]';
						$fields[$key]['fields']['custom_label_1'] = 'Custom label 1 [custom_label_1]';
						$fields[$key]['fields']['custom_label_2'] = 'Custom label 2 [custom_label_2]';
						$fields[$key]['fields']['custom_label_3'] = 'Custom label 3 [custom_label_3]';
						$fields[$key]['fields']['custom_label_4'] = 'Custom label 4 [custom_label_4]';
						break;

					case 'additional_attributes':
						$fields[$key]['fields']['excluded_destination'] = 'Excluded Destination[excluded_destination]';
						$fields[$key]['fields']['shopping_ads_excluded_country'] = 'Shopping Ads Excluded Country[shopping_ads_excluded_country]';
						$fields[$key]['fields']['included_destination'] = 'Included Destination[included_destination]';
						$fields[$key]['fields']['expiration_date'] = 'Expiration Date [expiration_date]';
						$fields[$key]['fields']['transit_time_label'] = 'Transit Time [transit_time_label]';
						break;
					case 'unit_prices_eu_ch':
						$fields[$key]['fields']['unit_pricing_measure'] = 'Unit Pricing Measure[unit_pricing_measure]';
						$fields[$key]['fields']['unit_pricing_base_measure'] = 'Unit Pricing Base Measure[unit_pricing_base_measure]';
						$fields[$key]['fields']['installment_months'] = 'Installment Months[months]';
						$fields[$key]['fields']['installment_amount'] = 'Installment Amount[amount]';
						$fields[$key]['fields']['subscription_period'] = 'Subscription Period[period]';
						$fields[$key]['fields']['subscription_period_length'] = 'Subscription Period Length[period_length]';
						$fields[$key]['fields']['subscription_amount'] = 'Subscription Amount[amount]';
						break;

					case 'energy_labels':

						$fields[$key]['fields']['energy_efficiency_class'] = 'Energy Efficiency Class[energy_efficiency_class]';
						$fields[$key]['fields']['min_energy_efficiency_class'] = 'Min Energy Efficiency Class[energy_efficiency_class]';
						$fields[$key]['fields']['max_energy_efficiency_class'] = 'Max Energy Efficiency Class[energy_efficiency_class]';
						break;

					default:
						break;
				}
			}

			return $fields;
		}

		public function set_selected_column_names($full_form_data) {

			if (is_null($this->selected_column_names)) {
				$this->selected_column_names = array();
				if (isset($full_form_data['mapping_form_data']['mapping_selected_fields']) && !empty($full_form_data['mapping_form_data']['mapping_selected_fields'])) {
					$selected_mapped_fields = array();
					foreach ($full_form_data['mapping_form_data']['mapping_selected_fields'] as $key => $value) {
						if ("" != $value) {
							$this->selected_column_names[$key] = $value;
						}
					}
				}
				if (isset($full_form_data['meta_step_form_data']['mapping_selected_fields']) && !empty($full_form_data['meta_step_form_data']['mapping_selected_fields'])) {
					$export_additional_columns = $full_form_data['meta_step_form_data']['mapping_selected_fields'];

					foreach ($export_additional_columns as $value) {
						foreach ($value as $key => $vl) {
							if ("" != $vl) {
								$this->selected_column_names[$key] = $vl;
							}
						}
					}
				}
				$this->selected_column_names = ($this->selected_column_names);
			}


			return $full_form_data;
		}

		public function get_selected_column_names() {

			return $this->selected_column_names;
		}

		public function exporter_alter_mapping_fields($fields, $base, $mapping_form_data) {
			if ($base == $this->module_base) {
				$fields = self::get_product_post_columns();
			}
			return $fields;
		}

		public function exporter_alter_advanced_fields($fields, $base, $advanced_form_data) {
			if ($this->module_base != $base) {
				return $fields;
			}
			$out = array();
			$out['header_empty_row'] = array(
				'tr_html' => '<tr id="header_empty_row"><th></th><td></td></tr>'
			);
			foreach ($fields as $fieldk => $fieldv) {
				$out[$fieldk] = $fieldv;
			}
                        $out['file_as']['sele_vals'] = array(
                            'txt' => __('TXT'),
                            'xml' => __('XML')
                        );
                        $out['delimiter']['sele_vals'] = array(
                            'tab' => array('value' => __('Tab'), 'val' => "\t"),
                            'comma' => array('value' => __('Comma'), 'val' => ",")
                        );


			return $out;
		}

		
		/**
		 *  Customize the items in filter export page
		 */
		public function exporter_alter_filter_fields($fields, $base, $filter_form_data) {
			if ($this->module_base != $base) {
				return $fields;
			}

			/* altering help text of default fields */
			$fields['limit']['label'] = __('Total number of products to export');
			$fields['limit']['help_text'] = __('Exports specified number of products. e.g. Entering 500 with a skip count of 10 will export products from 11th to 510th position.');
			$fields['offset']['label'] = __('Skip first <i>n</i> products');
			$fields['offset']['help_text'] = __('Skips specified number of products from the beginning of the database. e.g. Enter 10 to skip first 10 products from export.');

			$fields['product'] = array(
				'label' => __('Products'),
				'placeholder' => __('All products'),
				'attr' => array('data-exclude_type' => 'variable,variation'),
				'field_name' => 'product',
				'sele_vals' => array(),
				'help_text' => __('Export specific products. Keyin the product names to export multiple products.'),
				'type' => 'multi_select',
				'css_class' => 'wc-product-search',
				'validation_rule' => array('type' => 'text_arr')
			);
			$fields['stock_status'] = array(
				'label' => __('Stock status'),
				'placeholder' => __('All status'),
				'field_name' => 'stock_status',
				'sele_vals' => array('' => __('All status'), 'instock' => __('In Stock'), 'outofstock' => __('Out of Stock'), 'onbackorder' => __('On backorder')),
				'help_text' => __('Export products based on stock status.'),
				'type' => 'select',
				'validation_rule' => array('type' => 'text_arr')
			);
			$fields['exclude_product'] = array(
				'label' => __('Exclude products'),
				'placeholder' => __('Exclude products'),
				'attr' => array('data-exclude_type' => 'variable,variation'),
				'field_name' => 'exclude_product',
				'sele_vals' => array(),
				'help_text' => __('Use this if you need to exclude a specific or multiple products from your export list.'),
				'type' => 'multi_select',
				'css_class' => 'wc-product-search',
				'validation_rule' => array('type' => 'text_arr')
			);

			$fields['product_categories'] = array(
				'label' => __('Product categories'),
				'placeholder' => __('Any category'),
				'field_name' => 'product_categories',
				'sele_vals' => $this->get_product_categories(),
				'help_text' => __('Export products belonging to a particular or from multiple categories. Just select the respective categories.'),
				'type' => 'multi_select',
				'css_class' => 'wc-enhanced-select',
				'validation_rule' => array('type' => 'sanitize_title_with_dashes_arr')
			);

			$fields['product_tags'] = array(
				'label' => __('Product tags'),
				'placeholder' => __('Any tag'),
				'field_name' => 'product_tags',
				'sele_vals' => $this->get_product_tags(),
				'help_text' => __('Enter the product tags to export only the respective products that have been tagged accordingly.'),
				'type' => 'multi_select',
				'css_class' => 'wc-enhanced-select',
				'validation_rule' => array('type' => 'sanitize_title_with_dashes_arr')
			);

			$fields['product_status'] = array(
				'label' => __('Product status'),
				'placeholder' => __('Any status'),
				'field_name' => 'product_status',
				'sele_vals' => self::get_product_statuses(),
				'help_text' => __('Filter products by their status.'),
				'type' => 'multi_select',
				'css_class' => 'wc-enhanced-select',
				'validation_rule' => array('type' => 'text_arr')
			);

			return $fields;
		}		
		
		

public static function wt_feed_get_product_conditions() {
	$conditions = array(
		'new'           => _x( 'New', 'product condition', 'webtoffee-product-feed' ),
		'refurbished'   => _x( 'Refurbished', 'product condition', 'webtoffee-product-feed' ),
		'used'          => _x( 'Used', 'product condition', 'webtoffee-product-feed' ),
	);

	return apply_filters( 'wt_feed_google_product_conditions', $conditions );
}	

	public static function get_age_group() {
			$google_age_group = array(
				'adult' => __('Adult', 'webtoffee-product-feed'),
				'kids' => __('Kids', 'webtoffee-product-feed'),
				'toddler' => __('Toddler', 'webtoffee-product-feed'),
				'infant' => __('Infant', 'webtoffee-product-feed'),
				'newborn' => __('Newborn', 'webtoffee-product-feed')
			);
			return apply_filters( 'wt_feed_google_product_agegroup', $google_age_group );

	}
		
		

	}

}

new Webtoffee_Product_Feed_Sync_Leguide();

// Google Category dropdown caching
if (!function_exists('wt_google_category_dropdown')) {

        function wt_google_category_dropdown($selected = '') {

                $category_dropdown = wp_cache_get('wt_googlefeed_dropdown_product_categories');

                if (false === $category_dropdown) {
                        $categories = Webtoffee_Product_Feed_Sync_Google::get_category_array();

                        # Primary Attributes
                        $category_dropdown = '';

                        foreach ($categories as $key => $value) {
                                $category_dropdown .= sprintf('<option value="%s">%s</option>', $key, $value);
                        }

                        wp_cache_set('wt_googlefeed_dropdown_product_categories', $category_dropdown, '', WEEK_IN_SECONDS);
                }


                if ($selected && strpos($category_dropdown, 'value="' . $selected . '"') !== false) {
                        $category_dropdown = str_replace('value="' . $selected . '"', 'value="' . $selected . '"' . ' selected', $category_dropdown);
                }



                return $category_dropdown;
        }

}