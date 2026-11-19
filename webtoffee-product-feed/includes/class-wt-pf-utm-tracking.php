<?php
/**
 * UTM / URL tracking parameters for product feeds.
 *
 * @package webtoffee-product-feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wt_Pf_Utm_Tracking' ) ) {

	/**
	 * Class Wt_Pf_Utm_Tracking
	 *
	 * Adds per-feed UTM / URL tracking parameter configuration to the feed
	 * creation wizard (advanced step), captures UTM settings before the export
	 * loop, and appends UTM params to product links via existing filter hooks.
	 *
	 * Supports a small set of dynamic merge tags so merchants can get
	 * per-product attribution: {product_id}, {sku}, {feed_name}, {channel}.
	 *
	 * UTM is appended only to:
	 *  - wt_feed_filter_product_link        (main clickable URL)
	 *  - wt_feed_filter_product_parent_link (variations' parent URL)
	 *
	 * UTM is NOT appended to canonical_link (SEO signal) or ex_link
	 * (third-party URL).
	 */
	class Wt_Pf_Utm_Tracking {

		/**
		 * Captured UTM settings for the current export run.
		 *
		 * Shape:
		 *   array(
		 *     'params'    => array( 'utm_source' => ..., ... ),
		 *     'feed_name' => string,
		 *     'channel'   => string,
		 *   )
		 *
		 * Null when UTM is disabled for the current run.
		 *
		 * @var array|null
		 */
		private static $current_utm_settings = null;

		/**
		 * Constructor. Registers all hooks.
		 */
		public function __construct() {
			// UI: inject UTM fields into the advanced step of the feed wizard.
			add_filter( 'wt_pf_exporter_alter_advanced_fields_basic', array( $this, 'add_utm_fields' ), 10, 3 );

			// Capture UTM settings before the export loop starts.
			add_filter( 'wt_pf_export_full_form_data_basic', array( $this, 'capture_utm_settings' ), 10, 4 );

			// Append UTM to the main product link and the variation parent link only.
			add_filter( 'wt_feed_filter_product_link', array( $this, 'maybe_append_utm' ), 100, 2 );
			add_filter( 'wt_feed_filter_product_parent_link', array( $this, 'maybe_append_utm' ), 100, 2 );

			// Channels that bypass the generic filter and use a channel-scoped filter name instead.
			// Emitters pass ( $link, $product, $form_data ); maybe_append_utm only uses the first two
			// but we declare 3 to match the emitter signature and avoid a silent arg drop.
			add_filter( 'wt_feed_leguide_product_link', array( $this, 'maybe_append_utm' ), 100, 3 );
			add_filter( 'wt_feed_pinterest_rss_product_link', array( $this, 'maybe_append_utm' ), 100, 3 );
			add_filter( 'wt_feed_shopmania_product_link', array( $this, 'maybe_append_utm' ), 100, 3 );
			add_filter( 'wt_feed_heureka_product_link', array( $this, 'maybe_append_utm' ), 100, 3 );
			add_filter( 'wt_feed_rakuten_product_parent_link', array( $this, 'maybe_append_utm' ), 100, 3 );

			// Merge-tag substitution runs inside the per-product UTM pipeline.
			add_filter( 'wt_pf_utm_params', array( $this, 'substitute_tokens' ), 20, 3 );

			// Allow the form-toggler attributes to survive wp_kses_post on the toggle switch markup.
			add_filter( 'wp_kses_allowed_html', array( $this, 'allow_toggle_attrs' ), 10, 2 );
		}

		/**
		 * Add form-toggler data attributes to the wp_kses allow-list for post context.
		 *
		 * The toggle switch for `utm_enabled` is rendered via `field_html`, which is
		 * filtered through `wp_kses_post`. Without this, the `wf_frm_tgl-target`
		 * attribute gets stripped and the child fields never show/hide.
		 *
		 * @param array  $tags    Allowed tags map.
		 * @param string $context Context ('post', 'strip', etc.).
		 * @return array
		 */
		public function allow_toggle_attrs( $tags, $context ) {
			if ( 'post' !== $context ) {
				return $tags;
			}

			// Only widen the allow-list on the plugin's own admin screen or its own AJAX
			// endpoint (the Advanced step is loaded via wp_ajax_pf_export_ajax_basic, where
			// $_GET['page'] is empty). This keeps wp_kses_post untouched elsewhere in wp-admin
			// and on the front end.
			if ( ! is_admin() ) {
				return $tags;
			}
			$page           = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$action         = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$is_plugin_page = defined( 'WEBTOFFEE_PRODUCT_FEED_ID' ) && WEBTOFFEE_PRODUCT_FEED_ID === $page;
			$is_plugin_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && 'pf_export_ajax_basic' === $action;
			if ( ! $is_plugin_page && ! $is_plugin_ajax ) {
				return $tags;
			}

			$input_attrs   = array(
				'type'              => true,
				'id'                => true,
				'name'              => true,
				'value'             => true,
				'class'             => true,
				'checked'           => true,
				'wf_frm_tgl-target' => true,
			);
			$tags['input'] = isset( $tags['input'] ) && is_array( $tags['input'] ) ? array_merge( $tags['input'], $input_attrs ) : $input_attrs;

			$label_attrs   = array(
				'class' => true,
				'for'   => true,
			);
			$tags['label'] = isset( $tags['label'] ) && is_array( $tags['label'] ) ? array_merge( $tags['label'], $label_attrs ) : $label_attrs;

			return $tags;
		}

		/**
		 * Default UTM source/medium per channel.
		 *
		 * @param string $channel_slug Channel slug (e.g. 'google', 'facebook').
		 * @return array { source: string, medium: string }
		 */
		public static function get_channel_defaults( $channel_slug ) {
			$defaults = array(
				'google'                         => array(
					'source' => 'google',
					'medium' => 'cpc',
				),
				'facebook'                       => array(
					'source' => 'facebook',
					'medium' => 'social',
				),
				'bing'                           => array(
					'source' => 'bing',
					'medium' => 'cpc',
				),
				'pinterest'                      => array(
					'source' => 'pinterest',
					'medium' => 'social',
				),
				'pinterest_rss'                  => array(
					'source' => 'pinterest',
					'medium' => 'social',
				),
				'tiktok'                         => array(
					'source' => 'tiktok',
					'medium' => 'social',
				),
				'tiktokshop'                     => array(
					'source' => 'tiktokshop',
					'medium' => 'social',
				),
				'snapchat'                       => array(
					'source' => 'snapchat',
					'medium' => 'social',
				),
				'twitter'                        => array(
					'source' => 'twitter',
					'medium' => 'social',
				),
				'buyon_google'                   => array(
					'source' => 'google',
					'medium' => 'cpc',
				),
				'google_local_inventory_ads'     => array(
					'source' => 'google',
					'medium' => 'cpc',
				),
				'google_local_product_inventory' => array(
					'source' => 'google',
					'medium' => 'cpc',
				),
				'google_product_reviews'         => array(
					'source' => 'google',
					'medium' => 'cpc',
				),
				'google_promotions'              => array(
					'source' => 'google',
					'medium' => 'cpc',
				),
			);

			$channel_slug = is_string( $channel_slug ) ? $channel_slug : '';
			$fallback     = array(
				'source' => '' !== $channel_slug ? $channel_slug : '',
				'medium' => 'cpc',
			);

			$channel_default = isset( $defaults[ $channel_slug ] ) ? $defaults[ $channel_slug ] : $fallback;

			/**
			 * Override default UTM values per channel.
			 *
			 * @param array  $channel_default { source: string, medium: string }
			 * @param string $channel_slug    Channel slug.
			 */
			$filtered = apply_filters( 'wt_pf_utm_channel_defaults', $channel_default, $channel_slug );

			// Guard against misbehaving third-party filters — callers expect source/medium keys.
			if ( ! is_array( $filtered ) ) {
				$filtered = $channel_default;
			}
			$filtered['source'] = isset( $filtered['source'] ) ? (string) $filtered['source'] : '';
			$filtered['medium'] = isset( $filtered['medium'] ) ? (string) $filtered['medium'] : '';

			return $filtered;
		}

		/**
		 * Inject UTM fields into the advanced step of the feed wizard.
		 *
		 * @param array  $fields              Existing advanced screen fields.
		 * @param string $to_export           Channel slug (e.g. 'google').
		 * @param array  $advanced_form_data  Saved advanced form data for re-runs.
		 * @return array
		 */
		public function add_utm_fields( $fields, $to_export, $advanced_form_data ) {
			if ( ! is_array( $fields ) ) {
				$fields = array();
			}
			if ( ! is_array( $advanced_form_data ) ) {
				$advanced_form_data = array();
			}

			$channel_defaults = self::get_channel_defaults( $to_export );

			$utm_enabled_val     = isset( $advanced_form_data['wt_pf_utm_enabled'] ) ? $advanced_form_data['wt_pf_utm_enabled'] : 'no';
			$utm_enabled_checked = ( 'yes' === $utm_enabled_val ) ? ' checked="checked"' : '';

			$fields['utm_enabled'] = array(
				'label'           => __( 'Enable URL tracking', 'webtoffee-product-feed' ),
				'field_name'      => 'utm_enabled',
				'field_html'      => '<label class="wt_pf_toggle_switch">'
									. '<input type="checkbox" id="wt_pf_utm_enabled" name="wt_pf_utm_enabled" value="yes" class="wt_pf_form_toggler" wf_frm_tgl-target="wt_pf_utm_enabled"' . $utm_enabled_checked . ' />'
									. '<span class="wt_pf_toggle_slider"></span>'
									. '</label>',
				'help_text'       => __( 'Append UTM parameters to every product link in this feed. Useful for attributing sales back to the feed/channel in Google Analytics.', 'webtoffee-product-feed' ),
				'validation_rule' => array( 'type' => 'skip' ),
			);

			$fields['utm_source'] = array(
				'label'           => __( 'UTM Source', 'webtoffee-product-feed' ),
				'type'            => 'text',
				'value'           => isset( $advanced_form_data['wt_pf_utm_source'] ) ? $advanced_form_data['wt_pf_utm_source'] : '',
				'field_name'      => 'utm_source',
				'html_id'         => 'wt_pf_utm_source',
				'placeholder'     => $channel_defaults['source'],
				'form_toggler'    => array(
					'type' => 'child',
					'id'   => 'wt_pf_utm_enabled',
					'val'  => 'yes',
					'chk'  => 'true',
				),
				'help_text'       => __( 'Campaign source. e.g. google, facebook, bing.', 'webtoffee-product-feed' ),
				// Same reason as utm_campaign below — preserve internal whitespace so the
				// feed and live preview agree.
				'validation_rule' => array( 'type' => 'skip' ),
			);

			$fields['utm_medium'] = array(
				'label'           => __( 'UTM Medium', 'webtoffee-product-feed' ),
				'type'            => 'text',
				'value'           => isset( $advanced_form_data['wt_pf_utm_medium'] ) ? $advanced_form_data['wt_pf_utm_medium'] : '',
				'field_name'      => 'utm_medium',
				'html_id'         => 'wt_pf_utm_medium',
				'placeholder'     => $channel_defaults['medium'],
				'form_toggler'    => array(
					'type' => 'child',
					'id'   => 'wt_pf_utm_enabled',
					'val'  => 'yes',
					'chk'  => 'true',
				),
				'help_text'       => __( 'Marketing medium. e.g. cpc, social, email.', 'webtoffee-product-feed' ),
				// Same reason as utm_campaign below — preserve internal whitespace so the
				// feed and live preview agree.
				'validation_rule' => array( 'type' => 'skip' ),
			);

			$token_button = static function ( $target_id ) {
				return '<span class="wt_pf_utm_token_wrap">'
					. '<button type="button" class="wt_pf_utm_token_button button" data-wt-pf-utm-target="' . esc_attr( $target_id ) . '">+ ' . esc_html__( 'token', 'webtoffee-product-feed' ) . '</button>'
					. '<ul class="wt_pf_utm_token_menu" data-wt-pf-utm-target="' . esc_attr( $target_id ) . '" hidden>'
						. '<li data-wt-pf-utm-token="{feed_name}">{feed_name}<span>' . esc_html__( 'Feed name', 'webtoffee-product-feed' ) . '</span></li>'
						. '<li data-wt-pf-utm-token="{channel}">{channel}<span>' . esc_html__( 'Channel slug', 'webtoffee-product-feed' ) . '</span></li>'
						. '<li data-wt-pf-utm-token="{product_id}">{product_id}<span>' . esc_html__( 'Product ID', 'webtoffee-product-feed' ) . '</span></li>'
						. '<li data-wt-pf-utm-token="{sku}">{sku}<span>' . esc_html__( 'Product SKU', 'webtoffee-product-feed' ) . '</span></li>'
					. '</ul>'
				. '</span>';
			};

			$fields['utm_campaign'] = array(
				'label'            => __( 'UTM Campaign', 'webtoffee-product-feed' ),
				'type'             => 'text',
				'value'            => isset( $advanced_form_data['wt_pf_utm_campaign'] ) ? $advanced_form_data['wt_pf_utm_campaign'] : '',
				'field_name'       => 'utm_campaign',
				'html_id'          => 'wt_pf_utm_campaign',
				'placeholder'      => 'my-campaign',
				'form_toggler'     => array(
					'type' => 'child',
					'id'   => 'wt_pf_utm_enabled',
					'val'  => 'yes',
					'chk'  => 'true',
				),
				'help_text'        => __( 'Campaign name. Tokens supported: {feed_name} {channel} {product_id} {sku}', 'webtoffee-product-feed' ),
				// Skip the generic sanitize_text_field pass — it collapses runs of whitespace
				// to a single space, which breaks UTM values like "{channel} {product_id}".
				// capture_utm_settings() does its own wp_strip_all_tags + trim instead.
				'validation_rule'  => array( 'type' => 'skip' ),
				'after_form_field' => $token_button( 'wt_pf_utm_campaign' ),
			);

			$fields['utm_content'] = array(
				'label'            => __( 'UTM Content', 'webtoffee-product-feed' ),
				'type'             => 'text',
				'value'            => isset( $advanced_form_data['wt_pf_utm_content'] ) ? $advanced_form_data['wt_pf_utm_content'] : '',
				'field_name'       => 'utm_content',
				'html_id'          => 'wt_pf_utm_content',
				'form_toggler'     => array(
					'type' => 'child',
					'id'   => 'wt_pf_utm_enabled',
					'val'  => 'yes',
					'chk'  => 'true',
				),
				// Same reason as utm_campaign above — preserve internal whitespace.
				'validation_rule'  => array( 'type' => 'skip' ),
				'after_form_field' => $token_button( 'wt_pf_utm_content' ),
			);

			// Channel slug for {channel} token substitution — add_utm_fields receives the
			// channel here, but the script was enqueued on page load when $this->to_export
			// was still empty, so wt_pf_utm_preview_params.channel is ''. Emit the slug on
			// the preview element so the JS can pick it up at render time.
			$channel_slug = is_string( $to_export ) ? $to_export : '';

			$fields['utm_preview'] = array(
				'label'           => __( 'Preview', 'webtoffee-product-feed' ),
				'field_name'      => 'utm_preview',
				'merge_right'     => true,
				'field_html'      => '<div class="wt_pf_utm_full_preview" id="wt_pf_utm_full_preview" data-wt-pf-utm-channel="' . esc_attr( $channel_slug ) . '">'
									. '<div class="wt_pf_utm_full_preview_note">' . esc_html__( 'Live preview of a product link using a sample product from your store.', 'webtoffee-product-feed' ) . '</div>'
									. '<div class="wt_pf_utm_full_preview_url"></div>'
									. '</div>',
				'form_toggler'    => array(
					'type' => 'child',
					'id'   => 'wt_pf_utm_enabled',
					'val'  => 'yes',
					'chk'  => 'true',
				),
				'validation_rule' => array( 'type' => 'skip' ),
			);

			return $fields;
		}

		/**
		 * Capture UTM settings from the submitted form_data before the export loop.
		 *
		 * Hooked to `wt_pf_export_full_form_data_basic` which fires once per export run.
		 *
		 * @param array  $form_data              Full form data.
		 * @param string $to_process             Channel/post-type being exported.
		 * @param string $step                   Current step identifier.
		 * @param array  $selected_template_data Selected template data.
		 * @return array Unchanged form_data (filter contract).
		 */
		public function capture_utm_settings( $form_data, $to_process, $step, $selected_template_data ) {
			self::$current_utm_settings = null;

			if ( ! is_array( $form_data ) ) {
				return $form_data;
			}

			$advanced = isset( $form_data['advanced_form_data'] ) && is_array( $form_data['advanced_form_data'] ) ? $form_data['advanced_form_data'] : array();
			$enabled  = isset( $advanced['wt_pf_utm_enabled'] ) ? $advanced['wt_pf_utm_enabled'] : 'no';

			if ( 'yes' !== $enabled ) {
				return $form_data;
			}

			// All four fields preserve internal whitespace so user intent (incl. tokens
			// separated by multiple spaces) matches the live preview. sanitize_text_field
			// collapses runs of whitespace to a single space, which would make the feed
			// disagree with the preview.
			$raw_params = array(
				'utm_source'   => isset( $advanced['wt_pf_utm_source'] ) ? trim( wp_strip_all_tags( $advanced['wt_pf_utm_source'] ) ) : '',
				'utm_medium'   => isset( $advanced['wt_pf_utm_medium'] ) ? trim( wp_strip_all_tags( $advanced['wt_pf_utm_medium'] ) ) : '',
				'utm_campaign' => isset( $advanced['wt_pf_utm_campaign'] ) ? trim( wp_strip_all_tags( $advanced['wt_pf_utm_campaign'] ) ) : '',
				'utm_content'  => isset( $advanced['wt_pf_utm_content'] ) ? trim( wp_strip_all_tags( $advanced['wt_pf_utm_content'] ) ) : '',
			);

			// Drop empty params so we don't emit &utm_medium=&utm_campaign=.
			$params = array_filter(
				$raw_params,
				static function ( $value ) {
					return '' !== $value && null !== $value;
				}
			);

			if ( empty( $params ) ) {
				return $form_data;
			}

			$post_type = isset( $form_data['post_type_form_data'] ) && is_array( $form_data['post_type_form_data'] ) ? $form_data['post_type_form_data'] : array();
			$feed_name = isset( $post_type['item_filename'] ) ? sanitize_text_field( $post_type['item_filename'] ) : '';

			self::$current_utm_settings = array(
				'params'    => $params,
				'feed_name' => $feed_name,
				'channel'   => is_string( $to_process ) ? $to_process : '',
			);

			return $form_data;
		}

		/**
		 * Append UTM params to a product link.
		 *
		 * Hooked at priority 100 on `wt_feed_filter_product_link` and
		 * `wt_feed_filter_product_parent_link` only. NOT on canonical_link or ex_link.
		 *
		 * @param string     $link    Product link.
		 * @param WC_Product $product Product object.
		 * @return string
		 */
		public function maybe_append_utm( $link, $product ) {
			if ( ! is_string( $link ) || '' === $link ) {
				return $link;
			}

			if ( null === self::$current_utm_settings || empty( self::$current_utm_settings['params'] ) ) {
				return $link;
			}

			/**
			 * Modify the UTM params for a single product before appending to its link.
			 *
			 * Merge-tag substitution hooks here at priority 20.
			 *
			 * @param array      $utm_params UTM params ( key => value ).
			 * @param WC_Product $product    Product object.
			 * @param string     $link       The product link before UTM is appended.
			 */
			$utm_params = apply_filters( 'wt_pf_utm_params', self::$current_utm_settings['params'], $product, $link );

			if ( ! is_array( $utm_params ) || empty( $utm_params ) ) {
				return $link;
			}

			// RFC3986 encodes spaces as %20, matching the JS live preview (encodeURIComponent).
			// Default RFC1738 would emit '+', creating a preview-vs-feed mismatch.
			$utm_query = http_build_query( $utm_params, '', '&', PHP_QUERY_RFC3986 );
			if ( '' === $utm_query ) {
				return $link;
			}

			$separator = ( false !== strpos( $link, '?' ) ) ? '&' : '?';

			return $link . $separator . $utm_query;
		}

		/**
		 * Substitute merge tags inside UTM values.
		 *
		 * Hooked to `wt_pf_utm_params` at priority 20. Runs per product during feed export.
		 * Supported tokens: {product_id}, {sku}, {feed_name}, {channel}.
		 * Unknown tokens pass through literally so typos are visible in output.
		 *
		 * @param array      $utm_params UTM params ( key => value ).
		 * @param WC_Product $product    Product object.
		 * @param string     $link       The product link before UTM is appended.
		 * @return array
		 */
		public function substitute_tokens( $utm_params, $product, $link ) {
			if ( ! is_array( $utm_params ) || empty( $utm_params ) ) {
				return $utm_params;
			}

			$feed_name = isset( self::$current_utm_settings['feed_name'] ) ? (string) self::$current_utm_settings['feed_name'] : '';
			$channel   = isset( self::$current_utm_settings['channel'] ) ? (string) self::$current_utm_settings['channel'] : '';

			$product_id = '';
			$sku        = '';

			// Some channels (e.g. google_product_reviews) pass the product ID, not the object.
			$product_obj = $product;
			if ( ! ( $product_obj instanceof WC_Product ) && is_numeric( $product ) ) {
				$product_obj = wc_get_product( (int) $product );
			}

			if ( $product_obj instanceof WC_Product ) {
				$product_id = (string) $product_obj->get_id();
				$sku        = (string) $product_obj->get_sku();
				if ( '' === $sku ) {
					$sku = $product_id; // Fallback — merchants using {sku} shouldn't get an empty string.
				}
			}

			$token_map = array(
				'{feed_name}'  => $feed_name,
				'{channel}'    => $channel,
				'{product_id}' => $product_id,
				'{sku}'        => $sku,
			);

			/**
			 * Add or override UTM merge-tag tokens.
			 *
			 * Example: a 3rd-party plugin could add {category} resolved via wc_get_product_terms().
			 *
			 * @param array      $token_map Map of literal token (e.g. '{foo}') => replacement value.
			 * @param WC_Product $product   Product object.
			 * @param string     $link      The product link before UTM is appended.
			 */
			$token_map = apply_filters( 'wt_pf_utm_merge_tags', $token_map, $product, $link );

			if ( ! is_array( $token_map ) || empty( $token_map ) ) {
				return $utm_params;
			}

			foreach ( $utm_params as $key => $value ) {
				if ( is_string( $value ) && '' !== $value && false !== strpos( $value, '{' ) ) {
					$utm_params[ $key ] = strtr( $value, $token_map );
				}
			}

			return $utm_params;
		}
	}

	new Wt_Pf_Utm_Tracking();
}
