<?php
/**
 * "Other Solutions" admin tab — sidebar categories + card grid layout.
 *
 * Ported from wt-woocommerce-related-products' "You May Also Need" template.
 * Class prefix renamed wt-crp-os-* → wtpf-os-* to keep both plugins collision-free.
 *
 * @package WebToffee_Product_Feed
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template file; variables are template-scoped, not plugin globals.
defined( 'WPINC' ) || die;

$wtpf_img_base = esc_url( WT_PRODUCT_FEED_PLUGIN_URL . 'assets/images/other_solutions' );

$wtpf_categories = array(
	'ecommerce-promotions' => array(
		'label'      => __( 'E-commerce Promotions', 'webtoffee-product-feed' ),
		'subtitle'   => __( 'Create and run successful promotional campaigns with the best marketing tools for WooCommerce', 'webtoffee-product-feed' ),
		'icon'       => 'sidebar-ecommerce-promotions.svg',
		'hero'       => null,
		'plugins'    => array(
			array(
				'type'     => 'standard',
				'name'     => __( 'Smart Coupons for WooCommerce', 'webtoffee-product-feed' ),
				'icon'     => 'smart-coupons-plugin.png',
				'rating'   => '4.9',
				'features' => array(
					__( 'Advanced BOGO Coupons', 'webtoffee-product-feed' ),
					__( 'Offer store credits', 'webtoffee-product-feed' ),
					__( 'Create attractive gift cards', 'webtoffee-product-feed' ),
					__( 'Give away product coupons', 'webtoffee-product-feed' ),
					__( 'Coupons based on past purchases', 'webtoffee-product-feed' ),
					__( 'Restrict coupons by country', 'webtoffee-product-feed' ),
					__( 'Create and offer sign-up discount coupons', 'webtoffee-product-feed' ),
					__( 'Cart abandonment coupons', 'webtoffee-product-feed' ),
					__( 'Customizable countdown sales banner', 'webtoffee-product-feed' ),
					__( 'Bulk generate coupons', 'webtoffee-product-feed' ),
					__( 'Import and export coupons', 'webtoffee-product-feed' ),
					__( 'Coupon embeds', 'webtoffee-product-feed' ),
					__( 'Allow coupon combinations', 'webtoffee-product-feed' ),
				),
				'url'      => 'https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=smart_coupons',
			),
			array(
				'type'     => 'standard',
				'name'     => __( 'URL Coupons for WooCommerce', 'webtoffee-product-feed' ),
				'icon'     => 'url-coupons-plugin.png',
				'rating'   => '5.0',
				'features' => array(
					__( 'Generate custom coupon URLs', 'webtoffee-product-feed' ),
					__( 'Set up a redirect page', 'webtoffee-product-feed' ),
					__( 'Automatically add products', 'webtoffee-product-feed' ),
					__( 'Create QR code coupons', 'webtoffee-product-feed' ),
				),
				'url'      => 'https://www.webtoffee.com/product/url-coupons-for-woocommerce/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=URL_Coupons',
			),
			array(
				'type'     => 'standard',
				'name'     => __( 'WooCommerce Product Recommendations', 'webtoffee-product-feed' ),
				'icon'     => 'product-recommendation-plugin.png',
				'rating'   => '5.0',
				'features' => array(
					__( 'Automatically generate suggestions based on order history', 'webtoffee-product-feed' ),
					__( 'Display recommended products on the product pages', 'webtoffee-product-feed' ),
					__( 'Quick setup page to add & edit recommendations', 'webtoffee-product-feed' ),
					__( 'Multiple product recommendation layouts', 'webtoffee-product-feed' ),
					__( 'Set up discounts on the recommended product bundle', 'webtoffee-product-feed' ),
					__( 'Manually create a bought-together list', 'webtoffee-product-feed' ),
					__( 'Use upsells, cross-sells, & related products as frequently bought products', 'webtoffee-product-feed' ),
					__( 'Customize the title, button, and label texts', 'webtoffee-product-feed' ),
					__( 'Customize the display of the recommended products', 'webtoffee-product-feed' ),
				),
				'url'      => 'https://www.webtoffee.com/product/woocommerce-product-recommendations/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=Product_Recommendations',
			),
			array(
				'type'     => 'standard',
				'name'     => __( 'WooCommerce Coupon Generator', 'webtoffee-product-feed' ),
				'icon'     => 'coupon-generator-plugin.png',
				'rating'   => '5.0',
				'features' => array(
					__( 'Bulk generate WooCommerce coupons', 'webtoffee-product-feed' ),
					__( 'Bulk export WooCommerce coupons to CSV', 'webtoffee-product-feed' ),
					__( 'Add usage restrictions to coupons', 'webtoffee-product-feed' ),
				),
				'url'      => 'https://www.webtoffee.com/product/woocommerce-coupon-generator/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=Coupon_Generator',
			),
			array(
				'type'        => 'standard-with-image',
				'name'        => __( 'WooCommerce Gift Cards', 'webtoffee-product-feed' ),
				'icon'        => 'gift-card-plugin.png',
				'rating'      => 'stars',
				'features'    => array(
					__( 'Create unlimited gift cards', 'webtoffee-product-feed' ),
					__( 'Email gift cards to customers', 'webtoffee-product-feed' ),
					__( 'Provide refunds to store credit', 'webtoffee-product-feed' ),
					__( '20+ predefined gift card templates', 'webtoffee-product-feed' ),
					__( 'Category wise template listing', 'webtoffee-product-feed' ),
					__( 'Add custom templates for gift cards', 'webtoffee-product-feed' ),
					__( 'Generate gift cards based on order status', 'webtoffee-product-feed' ),
					__( 'Manage user credit balance', 'webtoffee-product-feed' ),
					__( 'Fixed and custom gift card amounts', 'webtoffee-product-feed' ),
					__( 'Add usage restrictions for gift cards', 'webtoffee-product-feed' ),
				),
				'url'         => 'https://www.webtoffee.com/product/woocommerce-gift-cards/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=WooCommerce_Gift_Cards',
				'image_src'   => 'woocommerce-giftcard-hero.svg',
				'card_class'  => 'wtpf-os-card--gift-cards',
				'plugin_file' => 'wt-woocommerce-gift-cards/wt-woocommerce-gift-cards.php',
			),
		),
		'standalone' => array(
			'name'        => __( 'ECommerce Marketing Automation App', 'webtoffee-product-feed' ),
			'icon'        => 'ema-app-plugin.png',
			'desc'        => __( 'Create signup forms, popups, and automated email campaigns with pre-built workflow templates to capture leads, recover abandoned carts, and grow sales.', 'webtoffee-product-feed' ),
			'screenshot'  => 'ema-screenshot.svg',
			'url'         => 'https://www.webtoffee.com/product/ecommerce-marketing-automation/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=EMA',
			'plugin_file' => 'ecommerce-marketing-automation/ecommerce-marketing-automation.php',
		),
		'bundle'     => array(
			'tag_emoji'    => '📣',
			'tag_color'    => 'yellow',
			'tag'          => __( 'Promotion Bundle', 'webtoffee-product-feed' ),
			'title'        => __( 'WooCommerce Promotion Bundle', 'webtoffee-product-feed' ),
			'url'          => 'https://www.webtoffee.com/woocommerce-promotions/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=Promotion_Bundle',
			'desc'         => __( 'Make powerful promotional campaigns with our WooCommerce promotion bundle. Create coupon promotions, set up gift cards, and implement popular product recommendation strategies.', 'webtoffee-product-feed' ),
			'pills'        => array(
				__( 'Smart Coupons', 'webtoffee-product-feed' ),
				__( 'Product recommendation', 'webtoffee-product-feed' ),
				__( 'Gift cards', 'webtoffee-product-feed' ),
			),
			'price_orig'   => '$277',
			'price_sale'   => '$194',
			'savings'      => __( 'Save up to 30% off', 'webtoffee-product-feed' ),
			'illustration' => 'woocommerce-promotion-bundle-hero.svg',
		),
	),
	'privacy-compliance'   => array(
		'label'      => __( 'Privacy Compliance', 'webtoffee-product-feed' ),
		'subtitle'   => __( 'Ensure compliance with major cookie laws, including, GDPR, CCPA, LGPD, CNIL, and more', 'webtoffee-product-feed' ),
		'icon'       => 'sidebar-privacy-compliance.svg',
		'hero'       => array(
			'name'        => __( 'GDPR Cookie Consent Plugin (CCPA Ready)', 'webtoffee-product-feed' ),
			'icon'        => 'gdpr-plugin.png',
			'rating'      => 'stars',
			'image'       => 'cookie-consent.svg',
			'desc'        => __( 'This Google-certified CMP lets you create a customizable cookie banner, manage user consent, and ensure global privacy compliance with automatic script blocking.', 'webtoffee-product-feed' ),
			'features'    => array(),
			'url'         => 'https://www.webtoffee.com/product/gdpr-cookie-consent/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=GDPR',
			'plugin_file' => 'webtoffee-cookie-consent/webtoffee-cookie-consent.php',
		),
		'plugins'    => array(
			array(
				'type'        => 'standard-with-image',
				'name'        => __( 'EU Order Withdrawal Button Plugin for WooCommerce', 'webtoffee-product-feed' ),
				'icon'        => 'eu-withdrawal-plugin-icon.svg',
				'rating'      => 'stars',
				'features'    => array(
					__( 'Add "Request Withdrawal" button to WooCommerce', 'webtoffee-product-feed' ),
					__( 'Supports guest withdrawal option', 'webtoffee-product-feed' ),
					__( 'Two-step confirmation to prevent errors', 'webtoffee-product-feed' ),
					__( 'Full or partial order withdrawal support', 'webtoffee-product-feed' ),
					__( 'Dedicated admin dashboard for all requests', 'webtoffee-product-feed' ),
					__( 'Send email confirmation to customers', 'webtoffee-product-feed' ),
				),
				'url'         => 'https://www.webtoffee.com/product/eu-withdrawal-button/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=EU_Withdarawal_Button',
				'image_src'   => 'eu-withdrawal-hero.svg',
				'card_class'  => 'wtpf-os-card--full-width',
				'plugin_file' => 'wt-eu-withdrawal-button/wt-eu-withdrawal-button.php',
			),
		),
		'standalone' => null,
		'bundle'     => null,
	),
	'data-import-export'   => array(
		'label'      => __( 'Data Import & Export', 'webtoffee-product-feed' ),
		'subtitle'   => __( 'The best-in-class import, export, and migration solutions for your WooCommerce data', 'webtoffee-product-feed' ),
		'icon'       => 'sidebar-data-import-export.svg',
		'hero'       => null,

		// Product Feed & Sync Manager is intentionally omitted here — this is the free
		// version of that product; we don't cross-sell ourselves on our own page.
		'plugins'    => array(
			array(
				'type'        => 'standard',
				'name'        => __( 'Product Import Export Plugin', 'webtoffee-product-feed' ),
				'icon'        => 'product-ie-plugin.png',
				'rating'      => '4.9',
				'features'    => array(
					__( 'Supports Excel, XML, CSV, and TSV file formats', 'webtoffee-product-feed' ),
					__( 'Schedule automated import and export', 'webtoffee-product-feed' ),
					__( 'Support for multiple product types', 'webtoffee-product-feed' ),
					__( 'Export product images in a separate zip file', 'webtoffee-product-feed' ),
					__( 'Import from URL, Google Sheets, FTP/SFTP', 'webtoffee-product-feed' ),
					__( 'Export to FTP/SFTP', 'webtoffee-product-feed' ),
					__( 'Advanced filters and customizations for import and export', 'webtoffee-product-feed' ),
					__( 'Add and update data while importing', 'webtoffee-product-feed' ),
					__( 'Maintains action history and debug logs', 'webtoffee-product-feed' ),
					__( 'Compatible with major 3rd-party plugins', 'webtoffee-product-feed' ),
				),
				'url'         => 'https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=Product_Import_Export',
				'plugin_file' => 'product-import-export-for-woo/product-import-export-for-woo.php',
			),
			array(
				'type'        => 'standard',
				'name'        => __( 'Order, Coupon, Subscription Export Import', 'webtoffee-product-feed' ),
				'icon'        => 'order-ie-plugin.png',
				'rating'      => '4.6',
				'features'    => array(
					__( 'Supports Excel, XML, CSV, and TSV file formats', 'webtoffee-product-feed' ),
					__( 'Schedule automated import & export', 'webtoffee-product-feed' ),
					__( 'Email customers on order status change', 'webtoffee-product-feed' ),
					__( 'Create users on order import', 'webtoffee-product-feed' ),
					__( 'Filter export by products, order status, email, date, etc', 'webtoffee-product-feed' ),
					__( 'Import from URL, Google Sheets, FTP/SFTP', 'webtoffee-product-feed' ),
					__( 'Export to FTP/SFTP', 'webtoffee-product-feed' ),
					__( 'Advanced filters and customizations for import & export', 'webtoffee-product-feed' ),
					__( 'Add & update data while importing', 'webtoffee-product-feed' ),
					__( 'Maintains action history and debug logs', 'webtoffee-product-feed' ),
					__( 'Compatible with major 3rd-party plugins', 'webtoffee-product-feed' ),
				),
				'url'         => 'https://www.webtoffee.com/product/order-import-export-plugin-for-woocommerce/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=Order_Import_Export',
				'plugin_file' => 'order-import-export-for-woocommerce/order-import-export-for-woocommerce.php',
			),
			array(
				'type'        => 'standard',
				'name'        => __( 'User Import Export Plugin', 'webtoffee-product-feed' ),
				'icon'        => 'user-ie-plugin.png',
				'rating'      => '5.0',
				'features'    => array(
					__( 'Supports Excel, XML, CSV, and TSV file formats', 'webtoffee-product-feed' ),
					__( 'Schedule automated import and export', 'webtoffee-product-feed' ),
					__( 'Customize and send emails to new users on import', 'webtoffee-product-feed' ),
					__( 'Retain user passwords on import/export', 'webtoffee-product-feed' ),
					__( 'Export and import custom fields and third-party plugin fields', 'webtoffee-product-feed' ),
					__( 'Filter by user role, email, date, etc', 'webtoffee-product-feed' ),
					__( 'Import from URL, Google Sheets, FTP/SFTP', 'webtoffee-product-feed' ),
					__( 'Export to FTP/SFTP', 'webtoffee-product-feed' ),
					__( 'Advanced filters and customizations for import & export', 'webtoffee-product-feed' ),
					__( 'Add & update data while importing', 'webtoffee-product-feed' ),
					__( 'Maintains action history and debug logs', 'webtoffee-product-feed' ),
					__( 'Compatible with major 3rd-party plugins', 'webtoffee-product-feed' ),
				),
				'url'         => 'https://www.webtoffee.com/product/wordpress-users-woocommerce-customers-import-export/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=User_Import_Export',
				'plugin_file' => 'users-customers-import-export-for-wp-woocommerce/users-customers-import-export-for-wp-woocommerce.php',
			),
			array(
				'type'       => 'standard-with-image',
				'name'       => __( 'Import Export Suite for WooCommerce', 'webtoffee-product-feed' ),
				'icon'       => 'ie-suite-plugin.png',
				'rating'     => 'stars',
				'features'   => array(
					__( 'Import/export Products, Orders, Subscriptions, Coupons, Customers, WordPress Users, Categories & Tags, Reviews', 'webtoffee-product-feed' ),
					__( 'Supports Excel, XML, CSV, and TSV file formats', 'webtoffee-product-feed' ),
					__( 'Schedule automated import & export', 'webtoffee-product-feed' ),
					__( 'Import from URL, Google Sheets, FTP/SFTP', 'webtoffee-product-feed' ),
					__( 'Export to FTP/SFTP', 'webtoffee-product-feed' ),
					__( 'Import & export custom fields and values', 'webtoffee-product-feed' ),
					__( 'Advanced filters and customizations for import & export', 'webtoffee-product-feed' ),
					__( 'Add and update data while importing', 'webtoffee-product-feed' ),
					__( 'Maintains action history and debug logs', 'webtoffee-product-feed' ),
					__( 'Compatible with major 3rd-party plugins', 'webtoffee-product-feed' ),
				),
				'url'        => 'https://www.webtoffee.com/product/woocommerce-import-export-suite/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=Import_Export_Suite',
				'image_src'  => 'data-io-illustration.svg',
				'card_class' => 'wtpf-os-card--ie-suite',
			),
		),
		'standalone' => null,
		'bundle'     => null,
	),
	'accounting-invoicing' => array(
		'label'      => __( 'Accounting & Invoicing', 'webtoffee-product-feed' ),
		'subtitle'   => __( 'Automatically generate professional WooCommerce invoices and documents for all your orders', 'webtoffee-product-feed' ),
		'icon'       => 'sidebar-accounting-invoicing.svg',
		'hero'       => array(
			'name'        => __( 'PDF Invoices, Packing Slips, & Credit Notes', 'webtoffee-product-feed' ),
			'icon'        => 'pdf-invoices-plugin.png',
			'rating'      => 'stars',
			'pdf_cluster' => true,
			'desc'        => __( 'Automatically generate, customize, and manage professional WooCommerce PDF invoices, packing slips, and credit notes with advanced automation and tax compliance features.', 'webtoffee-product-feed' ),
			'features'    => array(),
			'url'         => 'https://www.webtoffee.com/product/woocommerce-pdf-invoices-packing-slips/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=PDF_invoice',
		),
		'plugins'    => array(
			array(
				'type'     => 'standard',
				'name'     => __( 'Shipping Labels, Dispatch Labels, & Delivery Notes', 'webtoffee-product-feed' ),
				'icon'     => 'shipping-labels-plugin.png',
				'rating'   => '5.0',
				'features' => array(
					__( 'Create delivery notes, shipping & dispatch labels', 'webtoffee-product-feed' ),
					__( 'Enable customers to print the documents from order emails', 'webtoffee-product-feed' ),
					__( 'Customize shipping label size', 'webtoffee-product-feed' ),
					__( 'Add multiple shipping labels on one page', 'webtoffee-product-feed' ),
					__( 'Show product variation data', 'webtoffee-product-feed' ),
					__( 'Add extra product & order data fields', 'webtoffee-product-feed' ),
					__( 'Pre-built layouts & customizable templates', 'webtoffee-product-feed' ),
					__( 'Group products by \'Category\'', 'webtoffee-product-feed' ),
					__( 'Sort products based on Name or SKU', 'webtoffee-product-feed' ),
					__( 'Multilingual support', 'webtoffee-product-feed' ),
				),
				'url'      => 'https://www.webtoffee.com/product/woocommerce-shipping-labels-delivery-notes/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=Shipping_Label',
			),
			array(
				'type'     => 'standard',
				'name'     => __( 'WooCommerce Picklists plugin', 'webtoffee-product-feed' ),
				'icon'     => 'picklists-plugin.png',
				'rating'   => '4.0',
				'features' => array(
					__( 'Bulk print picklists from the admin order page', 'webtoffee-product-feed' ),
					__( 'Automatically email picklists based on order status', 'webtoffee-product-feed' ),
					__( 'Create or customize picklist templates', 'webtoffee-product-feed' ),
					__( 'Show product variation data', 'webtoffee-product-feed' ),
					__( 'Group products in picklist by order/category', 'webtoffee-product-feed' ),
					__( 'Add product meta fields & attributes', 'webtoffee-product-feed' ),
					__( 'Exclude virtual products from picklists', 'webtoffee-product-feed' ),
					__( 'Multilingual support', 'webtoffee-product-feed' ),
				),
				'url'      => 'https://www.webtoffee.com/product/woocommerce-picklist/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=Picklist',
			),
			array(
				'type'     => 'standard',
				'name'     => __( 'Customizer for WooCommerce PDF Invoices', 'webtoffee-product-feed' ),
				'icon'     => 'pdf-customizer-plugin.png',
				'rating'   => '5.0',
				'features' => array(
					__( 'Drag-and-drop easy customization', 'webtoffee-product-feed' ),
					__( 'Advanced visual and code editor', 'webtoffee-product-feed' ),
					__( 'Easy invoice layout customization', 'webtoffee-product-feed' ),
					__( 'Customize individual elements using block editors', 'webtoffee-product-feed' ),
					__( 'View live preview of customization', 'webtoffee-product-feed' ),
					__( 'Change color, text, background, border & more', 'webtoffee-product-feed' ),
				),
				'url'      => 'https://www.webtoffee.com/product/customizer-for-woocommerce-pdf-invoice/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=PDF_Customizer',
			),
			array(
				'type'     => 'standard',
				'name'     => __( 'WooCommerce Address Labels plugin', 'webtoffee-product-feed' ),
				'icon'     => 'address-labels-plugin.png',
				'rating'   => '5.0',
				'features' => array(
					__( 'Generate \'Shipping Address\', \'Billing Address\', \'From Address\', and \'Return Address\' labels', 'webtoffee-product-feed' ),
					__( 'Customize label sizes', 'webtoffee-product-feed' ),
					__( 'Bulk print address labels', 'webtoffee-product-feed' ),
					__( 'Offers built-in label templates', 'webtoffee-product-feed' ),
					__( 'Change address label layout', 'webtoffee-product-feed' ),
					__( 'Multilingual support', 'webtoffee-product-feed' ),
				),
				'url'      => 'https://www.webtoffee.com/product/woocommerce-address-label/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=Address_Label',
			),
			array(
				'type'     => 'standard',
				'name'     => __( 'Proforma Invoice', 'webtoffee-product-feed' ),
				'icon'     => 'proforma-invoice-plugin.png',
				'rating'   => '5.0',
				'features' => array(
					__( 'Create proforma invoices automatically', 'webtoffee-product-feed' ),
					__( 'Pre-built proforma invoice layouts', 'webtoffee-product-feed' ),
					__( 'Easy invoice layout customization', 'webtoffee-product-feed' ),
					__( 'Attach proforma invoice PDF to order emails', 'webtoffee-product-feed' ),
					__( 'Allow customers to print invoices', 'webtoffee-product-feed' ),
					__( 'Set custom proforma invoice number', 'webtoffee-product-feed' ),
					__( 'Add additional product & order data fields', 'webtoffee-product-feed' ),
					__( 'Attach special notes with proforma invoices', 'webtoffee-product-feed' ),
					__( 'Attach transport & sales terms', 'webtoffee-product-feed' ),
					__( 'Multilingual support', 'webtoffee-product-feed' ),
				),
				'url'      => 'https://www.webtoffee.com/product/woocommerce-proforma-invoice/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=Proforma_Invoice',
			),
			array(
				'type'     => 'standard',
				'name'     => __( 'QR Code Add-on for WooCommerce PDF Invoices', 'webtoffee-product-feed' ),
				'icon'     => 'qr-code-plugin.png',
				'rating'   => '5.0',
				'features' => array(
					__( 'Assign QR codes to all generated invoices', 'webtoffee-product-feed' ),
					__( 'Create QR code that reads order or invoice number', 'webtoffee-product-feed' ),
					__( 'Add custom data to invoices', 'webtoffee-product-feed' ),
					__( 'Compatible with WooCommerce PDF Invoice, Packing Slip & Credit Note (Premium)', 'webtoffee-product-feed' ),
					__( 'Compatible with WooCommerce PDF Invoices, Packing Slips, Delivery Notes, and Shipping Labels (Free)', 'webtoffee-product-feed' ),
				),
				'url'      => 'https://www.webtoffee.com/product/qr-code-addon-for-woocommerce-pdf-invoices/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=QR_Code',
			),
			array(
				'type'        => 'standard',
				'name'        => __( 'WooCommerce Request a Quote', 'webtoffee-product-feed' ),
				'icon'        => 'request-quote-plugin.png',
				'rating'      => '5.0',
				'features'    => array(
					__( 'Add quote button to the product & shop pages', 'webtoffee-product-feed' ),
					__( 'Enable quotation request for selected products', 'webtoffee-product-feed' ),
					__( 'Automatically send quotes to users', 'webtoffee-product-feed' ),
					__( 'Disable guest users from asking for quote', 'webtoffee-product-feed' ),
					__( 'Hide prices and \'add to cart\' button', 'webtoffee-product-feed' ),
					__( 'Automatic email alerts for admin & users', 'webtoffee-product-feed' ),
					__( 'Easy button and form customization', 'webtoffee-product-feed' ),
					__( 'Set quote expiry period', 'webtoffee-product-feed' ),
					__( 'Limit spams with reCAPTCHA', 'webtoffee-product-feed' ),
				),
				'url'         => 'https://www.webtoffee.com/product/woocommerce-request-a-quote/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=Request_Quote',
				'plugin_file' => 'wt-woo-request-quote/wt-woo-request-quote.php',
			),
			array(
				'type'       => 'standard-with-image',
				'name'       => __( 'Sequential Order Numbers', 'webtoffee-product-feed' ),
				'icon'       => 'sequential-orders-plugin.png',
				'rating'     => 'stars',
				'features'   => array(
					__( 'Auto reset sequence per month/year etc', 'webtoffee-product-feed' ),
					__( 'Add a custom suffix for order numbers', 'webtoffee-product-feed' ),
					__( 'Date suffix in order numbers', 'webtoffee-product-feed' ),
					__( 'Custom sequence for free orders', 'webtoffee-product-feed' ),
					__( 'Increment sequence in custom series', 'webtoffee-product-feed' ),
					__( 'More order number templates', 'webtoffee-product-feed' ),
				),
				'url'        => 'https://www.webtoffee.com/product/woocommerce-sequential-order-numbers/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=Sequential_Order_Numbers',
				'image_src'  => 'seq-orders-illustration.svg',
				'card_class' => 'wtpf-os-card--seq-orders',
			),
		),
		'standalone' => null,
		'bundle'     => array(
			'tag_emoji'    => '📄',
			'tag_color'    => 'green',
			'tag'          => __( 'Invoice Bundle', 'webtoffee-product-feed' ),
			'title'        => __( 'All in one Invoice bundle', 'webtoffee-product-feed' ),
			'url'          => 'https://www.webtoffee.com/pdf-invoices-packing-slips-suite-woocommerce/?utm_source=other_solution_page&utm_medium=free_plugin_product_feed&utm_campaign=Invoice_bundle',
			'desc'         => __( 'A complete suite of invoices and shipping documents bundle to create and print PDF invoices, packing slips, shipping and delivery documents in WooCommerce.', 'webtoffee-product-feed' ),
			'pills'        => array(
				__( 'Invoice', 'webtoffee-product-feed' ),
				__( 'Packing Slip', 'webtoffee-product-feed' ),
				__( 'Address Labels', 'webtoffee-product-feed' ),
				__( 'Dispatch Labels', 'webtoffee-product-feed' ),
				__( 'Shipping Labels', 'webtoffee-product-feed' ),
				__( 'Delivery Notes', 'webtoffee-product-feed' ),
				__( 'Picklists', 'webtoffee-product-feed' ),
				__( 'Proforma Invoice', 'webtoffee-product-feed' ),
			),
			'price_orig'   => '$279',
			'price_sale'   => '$179',
			'savings'      => __( 'Save up to 30% off', 'webtoffee-product-feed' ),
			'illustration' => 'invoice-bundle.png',
		),
	),
);

/*
 * This is the Product Feed plugin — Data Import & Export is the most relevant
 * category for our audience, so it leads the sidebar. Any category listed here
 * but missing from the array is silently skipped; any category present in the
 * array but not listed here is appended at the end.
 */
$wtpf_category_order = array(
	'data-import-export',
	'ecommerce-promotions',
	'privacy-compliance',
	'accounting-invoicing',
);
$wtpf_categories     = array_replace( array_fill_keys( $wtpf_category_order, null ), $wtpf_categories );
$wtpf_categories     = array_filter(
	$wtpf_categories,
	static function ( $wtpf_c ) {
		return null !== $wtpf_c;
	}
);

/*
 * Hide categories whose entire content is empty — i.e. no hero, no bundle, no
 * visible standalone (either missing or its plugin is active), and every plugin
 * card in the grid has its plugin_file set AND that plugin is active. Both the
 * sidebar link AND the panel body are skipped for such categories.
 */
if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
$wtpf_categories = array_filter(
	$wtpf_categories,
	static function ( $wtpf_c ) {
		if ( ! empty( $wtpf_c['hero'] ) ) {
			$wtpf_hf = isset( $wtpf_c['hero']['plugin_file'] ) ? $wtpf_c['hero']['plugin_file'] : '';
			if ( '' === $wtpf_hf || ! is_plugin_active( $wtpf_hf ) ) {
				return true;
			}
		}
		if ( ! empty( $wtpf_c['bundle'] ) ) {
			return true;
		}
		if ( ! empty( $wtpf_c['standalone'] ) ) {
			$wtpf_sf = isset( $wtpf_c['standalone']['plugin_file'] ) ? $wtpf_c['standalone']['plugin_file'] : '';
			if ( '' === $wtpf_sf || ! is_plugin_active( $wtpf_sf ) ) {
				return true;
			}
		}
		if ( ! empty( $wtpf_c['plugins'] ) ) {
			foreach ( $wtpf_c['plugins'] as $wtpf_p ) {
				if ( empty( $wtpf_p['plugin_file'] ) || ! is_plugin_active( $wtpf_p['plugin_file'] ) ) {
					return true;
				}
			}
		}
		return false;
	}
);
?>
<?php if ( empty( $wtpf_categories ) ) : ?>
	<div class="wt-pfd-tab-content" data-id="<?php echo esc_attr( $target_id ); ?>">
		<div class="wtpf-os-page">
			<div class="wtpf-os-header">
				<h1 class="wtpf-os-page-title"><?php esc_html_e( 'You\'re all set!', 'webtoffee-product-feed' ); ?></h1>
				<p class="wtpf-os-page-subtitle"><?php esc_html_e( 'All recommended plugins are already active on your store.', 'webtoffee-product-feed' ); ?></p>
			</div>
		</div>
	</div>
	<?php return; ?>
<?php endif; ?>
<?php
$wtpf_first_category = array_key_first( $wtpf_categories );
$wtpf_first_cat      = $wtpf_categories[ $wtpf_first_category ];
?>
<div class="wt-pfd-tab-content" data-id="<?php echo esc_attr( $target_id ); ?>">
	<div class="wtpf-os-page">

		<div class="wtpf-os-header">
			<h1 class="wtpf-os-page-title" id="wtpf-os-cat-title"><?php echo esc_html( $wtpf_first_cat['label'] ); ?></h1>
			<p class="wtpf-os-page-subtitle" id="wtpf-os-cat-subtitle"><?php echo esc_html( $wtpf_first_cat['subtitle'] ); ?></p>
		</div>

		<div class="wtpf-os-layout">

			<?php /* ---- Sidebar ---- */ ?>
			<div class="wtpf-os-sidebar">
				<ul class="wtpf-os-sidebar-nav">
					<?php foreach ( $wtpf_categories as $wtpf_cat_id => $wtpf_cat ) : ?>
						<li>
							<a href="#"
								class="wtpf-os-cat-link<?php echo ( $wtpf_cat_id === $wtpf_first_category ) ? ' active' : ''; ?>"
								data-category="<?php echo esc_attr( $wtpf_cat_id ); ?>">
								<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
								<img class="wtpf-os-cat-icon"
									src="<?php echo esc_url( $wtpf_img_base . '/' . $wtpf_cat['icon'] ); ?>"
									alt="<?php echo esc_attr( $wtpf_cat['label'] ); ?>">
								<?php echo esc_html( $wtpf_cat['label'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>

				<div class="wtpf-os-trust-badges">
					<div class="wtpf-os-trust-badge">
						<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						<img src="<?php echo esc_url( $wtpf_img_base . '/thirty-day-guarantee.png' ); ?>"
							alt="<?php esc_attr_e( '30 Day Money Back Guarantee', 'webtoffee-product-feed' ); ?>">
						<span><?php esc_html_e( '30 Day No Risk Money Back Guarantee', 'webtoffee-product-feed' ); ?></span>
					</div>
					<div class="wtpf-os-trust-badge">
						<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						<img src="<?php echo esc_url( $wtpf_img_base . '/satisfaction-badge.png' ); ?>"
							alt="<?php esc_attr_e( '99% Satisfaction Rating', 'webtoffee-product-feed' ); ?>">
						<span><?php esc_html_e( 'Fast Support with 99% Satisfaction Rating', 'webtoffee-product-feed' ); ?></span>
					</div>
				</div>
			</div>

			<?php /* ---- Main content ---- */ ?>
			<div class="wtpf-os-main">

				<?php foreach ( $wtpf_categories as $wtpf_cat_id => $wtpf_cat ) : ?>
					<div id="wtpf-os-panel-<?php echo esc_attr( $wtpf_cat_id ); ?>"
						class="wtpf-os-category-panel<?php echo ( $wtpf_cat_id === $wtpf_first_category ) ? ' active' : ''; ?>"
						data-title="<?php echo esc_attr( $wtpf_cat['label'] ); ?>"
						data-subtitle="<?php echo esc_attr( $wtpf_cat['subtitle'] ); ?>">

						<?php /* -- Hero card -- */ ?>
						<?php
						if ( ! empty( $wtpf_cat['hero'] ) ) :
							$wtpf_hero              = $wtpf_cat['hero'];
							$wtpf_hero_plugin_file  = isset( $wtpf_hero['plugin_file'] ) ? $wtpf_hero['plugin_file'] : '';
							$wtpf_hero_is_active    = $wtpf_hero_plugin_file && is_plugin_active( $wtpf_hero_plugin_file );
							$wtpf_hero_is_installed = $wtpf_hero_plugin_file && file_exists( WP_PLUGIN_DIR . '/' . $wtpf_hero_plugin_file );

							if ( ! $wtpf_hero_is_active ) :
								?>
							<div class="wtpf-os-hero-card">
								<div class="wtpf-os-hero-left">
									<div class="wtpf-os-hero-title-row">
										<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
										<img class="wtpf-os-hero-icon"
											src="<?php echo esc_url( $wtpf_img_base . '/' . $wtpf_hero['icon'] ); ?>"
											alt="<?php echo esc_attr( $wtpf_hero['name'] ); ?>">
										<div class="wtpf-os-hero-title-block">
											<h3 class="wtpf-os-hero-name"><?php echo esc_html( $wtpf_hero['name'] ); ?></h3>
											<div class="wtpf-os-hero-stars" aria-label="<?php esc_attr_e( '5 out of 5 stars', 'webtoffee-product-feed' ); ?>">
												<?php for ( $i = 0; $i < 5; $i++ ) : ?>
													<span class="wtpf-os-star">&#9733;</span>
												<?php endfor; ?>
											</div>
										</div>
									</div>
									<div class="wtpf-os-hero-divider"></div>
									<p class="wtpf-os-hero-desc"><?php echo esc_html( $wtpf_hero['desc'] ); ?></p>
									<?php if ( $wtpf_hero_is_installed && current_user_can( 'activate_plugins' ) ) : ?>
										<?php
										$wtpf_hero_activate_url = wp_nonce_url(
											self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $wtpf_hero_plugin_file ) ),
											'activate-plugin_' . $wtpf_hero_plugin_file
										);
										?>
										<a href="<?php echo esc_url( $wtpf_hero_activate_url ); ?>"
											class="wtpf-os-btn-premium wtpf-os-btn-premium--block">
											<?php esc_html_e( 'Activate', 'webtoffee-product-feed' ); ?>
										</a>
									<?php else : ?>
										<a href="<?php echo esc_url( $wtpf_hero['url'] ); ?>"
											target="_blank"
											rel="noopener noreferrer"
											class="wtpf-os-btn-premium wtpf-os-btn-premium--block">
											<span class="dashicons dashicons-star-filled"></span>
											<?php esc_html_e( 'Get premium', 'webtoffee-product-feed' ); ?>
										</a>
									<?php endif; ?>
								</div>
								<?php if ( ! empty( $wtpf_hero['pdf_cluster'] ) ) : ?>
									<div class="wtpf-os-hero-right wtpf-os-hero-right--pdf-cluster">
										<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
										<img class="wtpf-os-pdf wtpf-os-pdf--left"
											src="<?php echo esc_url( $wtpf_img_base . '/pdf-invoice-left.svg' ); ?>"
											alt="">
										<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
										<img class="wtpf-os-pdf wtpf-os-pdf--center"
											src="<?php echo esc_url( $wtpf_img_base . '/pdf-invoice-center.svg' ); ?>"
											alt="">
										<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
										<img class="wtpf-os-pdf wtpf-os-pdf--right"
											src="<?php echo esc_url( $wtpf_img_base . '/pdf-invoice-right.svg' ); ?>"
											alt="">
									</div>
								<?php elseif ( ! empty( $wtpf_hero['image'] ) ) : ?>
									<div class="wtpf-os-hero-right">
										<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
										<img src="<?php echo esc_url( $wtpf_img_base . '/' . $wtpf_hero['image'] ); ?>"
											alt="<?php echo esc_attr( $wtpf_hero['name'] ); ?>">
									</div>
								<?php endif; ?>
							</div>
								<?php
							endif;
						endif;
						?>

						<?php /* -- Plugin card grid -- */ ?>
						<?php if ( ! empty( $wtpf_cat['plugins'] ) ) : ?>
							<?php
							// Filter out plugins that are already active — the card is only useful when the plugin is missing or inactive.
							// is_plugin_active() is guaranteed available here — required at the top of the file.
							$wtpf_visible_plugins = array_values(
								array_filter(
									$wtpf_cat['plugins'],
									static function ( $wtpf_p ) {
										if ( empty( $wtpf_p['plugin_file'] ) ) {
											return true;
										}
										return ! is_plugin_active( $wtpf_p['plugin_file'] );
									}
								)
							);
							$wtpf_chunks          = array_chunk( $wtpf_visible_plugins, 3 );
							foreach ( $wtpf_chunks as $wtpf_row ) :
								?>
								<div class="wtpf-os-card-grid">
									<?php foreach ( $wtpf_row as $wtpf_plugin ) : ?>

										<?php if ( 'image' === $wtpf_plugin['type'] ) : ?>

											<div class="wtpf-os-card-image">
												<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
												<img src="<?php echo esc_url( $wtpf_img_base . '/' . $wtpf_plugin['src'] ); ?>"
													alt="">
											</div>

											<?php
										else :
											$wtpf_with_image = ( 'standard-with-image' === $wtpf_plugin['type'] && ! empty( $wtpf_plugin['image_src'] ) );
											$wtpf_card_class = 'wtpf-os-card';
											if ( $wtpf_with_image ) {
												$wtpf_card_class .= ' wtpf-os-card--with-image';
											}
											if ( ! empty( $wtpf_plugin['card_class'] ) ) {
												$wtpf_card_class .= ' ' . sanitize_html_class( $wtpf_plugin['card_class'] );
											}
											?>

											<div class="<?php echo esc_attr( $wtpf_card_class ); ?>">
												<div class="wtpf-os-card-body">
													<?php if ( $wtpf_with_image ) : ?>
														<div class="wtpf-os-card-header wtpf-os-card-header--stacked">
															<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
															<img class="wtpf-os-card-icon"
																src="<?php echo esc_url( $wtpf_img_base . '/' . $wtpf_plugin['icon'] ); ?>"
																alt="<?php echo esc_attr( $wtpf_plugin['name'] ); ?>">
															<div class="wtpf-os-card-title-block">
																<span class="wtpf-os-card-name"><?php echo esc_html( $wtpf_plugin['name'] ); ?></span>
																<?php if ( 'stars' === $wtpf_plugin['rating'] ) : ?>
																	<span class="wtpf-os-card-rating wtpf-os-card-rating--stars" aria-label="<?php esc_attr_e( '5 out of 5 stars', 'webtoffee-product-feed' ); ?>">
																		<?php for ( $i = 0; $i < 5; $i++ ) : ?>
																			<span class="wtpf-os-star">&#9733;</span>
																		<?php endfor; ?>
																	</span>
																<?php else : ?>
																	<span class="wtpf-os-card-rating">
																		<?php echo esc_html( $wtpf_plugin['rating'] ); ?>
																		<span class="wtpf-os-star">&#9733;</span>
																	</span>
																<?php endif; ?>
															</div>
														</div>
													<?php else : ?>
														<div class="wtpf-os-card-header">
															<div class="wtpf-os-card-icon-name">
																<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
																<img class="wtpf-os-card-icon"
																	src="<?php echo esc_url( $wtpf_img_base . '/' . $wtpf_plugin['icon'] ); ?>"
																	alt="<?php echo esc_attr( $wtpf_plugin['name'] ); ?>">
																<span class="wtpf-os-card-name"><?php echo esc_html( $wtpf_plugin['name'] ); ?></span>
															</div>
															<?php if ( 'stars' === $wtpf_plugin['rating'] ) : ?>
																<span class="wtpf-os-card-rating wtpf-os-card-rating--stars">
																	<span class="wtpf-os-star">&#9733;</span>
																	<span class="wtpf-os-star">&#9733;</span>
																	<span class="wtpf-os-star">&#9733;</span>
																	<span class="wtpf-os-star">&#9733;</span>
																	<span class="wtpf-os-star">&#9733;</span>
																</span>
															<?php else : ?>
																<span class="wtpf-os-card-rating">
																	<?php echo esc_html( $wtpf_plugin['rating'] ); ?>
																	<span class="wtpf-os-star">&#9733;</span>
																</span>
															<?php endif; ?>
														</div>
													<?php endif; ?>
													<ul class="wtpf-os-card-features<?php echo ( count( $wtpf_plugin['features'] ) > 3 ) ? ' wtpf-os-card-features--collapsible' : ''; ?>">
														<?php foreach ( $wtpf_plugin['features'] as $wtpf_feature ) : ?>
															<li>
																<span class="dashicons dashicons-yes-alt"></span>
																<?php echo esc_html( $wtpf_feature ); ?>
															</li>
														<?php endforeach; ?>
													</ul>
													<?php if ( count( $wtpf_plugin['features'] ) > 3 ) : ?>
														<div class="wtpf-os-show-more-less">
															<a href="#" class="wtpf-os-show-more"><?php esc_html_e( 'Show More', 'webtoffee-product-feed' ); ?></a>
															<a href="#" class="wtpf-os-show-less"><?php esc_html_e( 'Show Less', 'webtoffee-product-feed' ); ?></a>
														</div>
													<?php endif; ?>
													<?php
													$wtpf_plugin_file      = ! empty( $wtpf_plugin['plugin_file'] ) ? $wtpf_plugin['plugin_file'] : '';
													$wtpf_plugin_installed = $wtpf_plugin_file && file_exists( WP_PLUGIN_DIR . '/' . $wtpf_plugin_file );
													if ( $wtpf_plugin_installed && current_user_can( 'activate_plugins' ) ) :
														$wtpf_activate_url = wp_nonce_url(
															self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $wtpf_plugin_file ) ),
															'activate-plugin_' . $wtpf_plugin_file
														);
														?>
														<a href="<?php echo esc_url( $wtpf_activate_url ); ?>"
															class="wtpf-os-btn-premium">
															<?php esc_html_e( 'Activate', 'webtoffee-product-feed' ); ?>
														</a>
													<?php else : ?>
														<a href="<?php echo esc_url( $wtpf_plugin['url'] ); ?>"
															target="_blank"
															rel="noopener noreferrer"
															class="wtpf-os-btn-premium">
															<span class="dashicons dashicons-star-filled"></span>
															<?php esc_html_e( 'Get premium', 'webtoffee-product-feed' ); ?>
														</a>
													<?php endif; ?>
												</div>
												<?php if ( $wtpf_with_image ) : ?>
													<div class="wtpf-os-card-image-side">
														<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
														<img src="<?php echo esc_url( $wtpf_img_base . '/' . $wtpf_plugin['image_src'] ); ?>"
															alt="">
													</div>
												<?php endif; ?>
											</div>

										<?php endif; ?>

									<?php endforeach; ?>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>

						<?php /* -- Bundle section (renders BEFORE the standalone, per Figma order) -- */ ?>
						<?php
						if ( ! empty( $wtpf_cat['bundle'] ) ) :
							$wtpf_bundle    = $wtpf_cat['bundle'];
							$wtpf_tag_color = ! empty( $wtpf_bundle['tag_color'] ) ? $wtpf_bundle['tag_color'] : 'green';
							?>
							<div class="wtpf-os-bundle">
								<div class="wtpf-os-bundle-content">
									<span class="wtpf-os-bundle-tag wtpf-os-bundle-tag--<?php echo esc_attr( $wtpf_tag_color ); ?>">
										<?php if ( ! empty( $wtpf_bundle['tag_emoji'] ) ) : ?>
											<span class="wtpf-os-bundle-tag-emoji"><?php echo esc_html( $wtpf_bundle['tag_emoji'] ); ?></span>
										<?php endif; ?>
										<?php echo esc_html( $wtpf_bundle['tag'] ); ?>
									</span>
									<div class="wtpf-os-bundle-title">
										<a href="<?php echo esc_url( $wtpf_bundle['url'] ); ?>"
											target="_blank"
											rel="noopener noreferrer">
											<?php echo esc_html( $wtpf_bundle['title'] ); ?>
										</a>
										<span class="dashicons dashicons-external"></span>
									</div>
									<p class="wtpf-os-bundle-desc"><?php echo esc_html( $wtpf_bundle['desc'] ); ?></p>
									<div class="wtpf-os-bundle-pills">
										<?php foreach ( $wtpf_bundle['pills'] as $wtpf_pill ) : ?>
											<span class="wtpf-os-bundle-pill">
												<span class="dashicons dashicons-yes-alt"></span>
												<?php echo esc_html( $wtpf_pill ); ?>
											</span>
										<?php endforeach; ?>
									</div>
									<p class="wtpf-os-bundle-pricing">
										<?php
										printf(
											wp_kses(
												/* translators: 1: strikethrough original price, 2: bold sale price, 3: green savings text */
												__( 'Total: <s>%1$s</s> <strong>%2$s</strong> <span class="wtpf-os-savings">(%3$s)</span>', 'webtoffee-product-feed' ),
												array(
													's'    => array(),
													'strong' => array(),
													'span' => array( 'class' => array() ),
												)
											),
											esc_html( $wtpf_bundle['price_orig'] ),
											esc_html( $wtpf_bundle['price_sale'] ),
											esc_html( $wtpf_bundle['savings'] )
										);
										?>
									</p>
									<a href="<?php echo esc_url( $wtpf_bundle['url'] ); ?>"
										target="_blank"
										rel="noopener noreferrer"
										class="wtpf-os-btn-bundle">
										<?php esc_html_e( 'View Bundle', 'webtoffee-product-feed' ); ?>
										<span class="dashicons dashicons-external"></span>
									</a>
								</div>
								<?php if ( ! empty( $wtpf_bundle['illustration'] ) ) : ?>
									<div class="wtpf-os-bundle-illustration">
										<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
										<img src="<?php echo esc_url( $wtpf_img_base . '/' . $wtpf_bundle['illustration'] ); ?>"
											alt="<?php echo esc_attr( $wtpf_bundle['title'] ); ?>">
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php /* -- Standalone card (e.g. EMA App) — renders AFTER the bundle, per Figma order -- */ ?>
						<?php
						if ( ! empty( $wtpf_cat['standalone'] ) ) :
							$wtpf_solo = $wtpf_cat['standalone'];

							/*
							 * Tri-state install/active check:
							 *   active         → hide banner
							 *   installed only → show "Activate" button (nonce-protected activate URL)
							 *   not installed  → show default "Try Now" button
							 *
							 * is_plugin_active() is guaranteed available here — required at the top of the file.
							 */
							$wtpf_solo_plugin_file  = isset( $wtpf_solo['plugin_file'] ) ? $wtpf_solo['plugin_file'] : '';
							$wtpf_solo_is_active    = $wtpf_solo_plugin_file && is_plugin_active( $wtpf_solo_plugin_file );
							$wtpf_solo_is_installed = $wtpf_solo_plugin_file && file_exists( WP_PLUGIN_DIR . '/' . $wtpf_solo_plugin_file );

							if ( ! $wtpf_solo_is_active ) :
								?>
							<div class="wtpf-os-standalone">
								<div class="wtpf-os-standalone-content">
									<div class="wtpf-os-standalone-header">
										<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
										<img class="wtpf-os-standalone-icon"
											src="<?php echo esc_url( $wtpf_img_base . '/' . $wtpf_solo['icon'] ); ?>"
											alt="<?php echo esc_attr( $wtpf_solo['name'] ); ?>">
										<h3 class="wtpf-os-standalone-name"><?php echo esc_html( $wtpf_solo['name'] ); ?></h3>
									</div>
									<p class="wtpf-os-standalone-desc"><?php echo esc_html( $wtpf_solo['desc'] ); ?></p>
									<?php if ( $wtpf_solo_is_installed && current_user_can( 'activate_plugins' ) ) : ?>
										<?php
										$wtpf_solo_activate_url = wp_nonce_url(
											self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $wtpf_solo_plugin_file ) ),
											'activate-plugin_' . $wtpf_solo_plugin_file
										);
										?>
										<a href="<?php echo esc_url( $wtpf_solo_activate_url ); ?>"
											class="wtpf-os-btn-premium wtpf-os-btn-premium--block">
											<?php esc_html_e( 'Activate', 'webtoffee-product-feed' ); ?>
										</a>
									<?php else : ?>
										<a href="<?php echo esc_url( $wtpf_solo['url'] ); ?>"
											target="_blank"
											rel="noopener noreferrer"
											class="wtpf-os-btn-premium wtpf-os-btn-premium--block">
											<?php esc_html_e( 'Try Now', 'webtoffee-product-feed' ); ?>
										</a>
									<?php endif; ?>
								</div>
								<?php if ( ! empty( $wtpf_solo['screenshot'] ) ) : ?>
									<div class="wtpf-os-standalone-screenshot">
										<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
										<img src="<?php echo esc_url( $wtpf_img_base . '/' . $wtpf_solo['screenshot'] ); ?>"
											alt="<?php echo esc_attr( $wtpf_solo['name'] ); ?>">
									</div>
								<?php endif; ?>
							</div>
								<?php
							endif;
						endif;
						?>

					</div>
				<?php endforeach; ?>

			</div>
		</div>
	</div>
</div>
