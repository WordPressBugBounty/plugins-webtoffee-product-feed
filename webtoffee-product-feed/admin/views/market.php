<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wt-profeed-upsell-wrapper market-box table-box-main">
    <div class="wt-profeed-premium-upgrade wt-profeed-sidebar">

		<div class="wt-profeed-header">
		    <?php
				// Check if Black Friday season is active and show the BFCM header strip.
				if ( ! isset( $fb_sync_tab ) && method_exists( 'Webtoffee_Product_Feed_Sync_Admin', 'is_bfcm_season' ) && Webtoffee_Product_Feed_Sync_Admin::is_bfcm_season() ) {
					?>
					<div class="wt-profeed-market-bfcm-strip">
						<img class="wt-profeed-market-bfcm-strip-ribbon" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../banner/assets/images/bfcm-30-off-coupon.svg' ); ?>" alt="<?php esc_attr_e( '30% OFF', 'webtoffee-product-feed' ); ?>" width="44" height="49" />
						<div class="wt-profeed-market-bfcm-strip-text">
							<strong><?php esc_html_e( 'BFCM Deal of the Year is Here!', 'webtoffee-product-feed' ); ?></strong>
							<span><?php esc_html_e( 'Grab huge offers on our premium plugins.', 'webtoffee-product-feed' ); ?></span>
						</div>
					</div>
					<?php
				}
			?>
                    <div class="wt-feed-why-premium">
                        <div class="why-premium-inner-box" style="padding-right: 0px;">
						<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						<img style="width:50px;" src="<?php echo esc_url( WT_PRODUCT_FEED_PLUGIN_URL ); ?>/images/new-crown.svg" alt="<?php esc_attr_e( 'Premium features', 'webtoffee-product-feed' ); ?>"/>
                        </div>
			<div class="why-premium-inner-box" style="padding-left: 0px;">
                            <?php if( isset( $fb_sync_tab ) ){ ?>
				<p style="margin:10px 0px;font-size: 16px;"><?php esc_html_e('What\'s in premium?', 'webtoffee-product-feed'); ?></p>
				<?php } else{ ?>
				<p class="wt-profeed-why-premium-title"><?php esc_html_e('Why use premium?', 'webtoffee-product-feed'); ?></p>
				<p class="wt-profeed-why-premium-subtitle"><?php esc_html_e('Unlock more control and faster syncs', 'webtoffee-product-feed'); ?></p>
				<?php } ?>
                        </div>
                        <div class="why-premium-inner-box">
                        </div>
			</div>
			<div class="wt-profeed wt-profeed_review wt-profeed_tags wt-profeed_categories wt-profeed-gopro-cta wt-profeed-features">
				<ul class="ticked-list wt-profeed-allfeat">
                                    <?php if( isset( $fb_sync_tab ) ): ?>
                                        <li><?php esc_html_e( 'Enable auto-sync for the Facebook catalog', 'webtoffee-product-feed' ); ?></li>
					<li><?php esc_html_e( 'Add fields like condition, color, gender, material, etc., to products.', 'webtoffee-product-feed' ); ?></li>
					<li><?php esc_html_e( 'Exclude out-of-stock products from syncing', 'webtoffee-product-feed' ); ?></li>
                                        <li><?php esc_html_e( 'Exclude specific products/variations from the sync.', 'webtoffee-product-feed' ); ?></li>
					<?php else: ?>
					<li><?php esc_html_e( 'Create custom product feeds for any platform', 'webtoffee-product-feed' ); ?></li>
					<li><?php esc_html_e( 'Schedule reliable feed updates with WordPress Cron or Server Cron', 'webtoffee-product-feed' ); ?></li>
					<li><?php esc_html_e( 'Filter unwanted products (out of stock, missing price, missing description, etc.) from feeds for better ad performance', 'webtoffee-product-feed' ); ?></li>
					<li><?php esc_html_e( 'Bulk update product prices in feeds without changing them in the store and apply channel-wise discounts', 'webtoffee-product-feed' ); ?></li>
					<li><?php esc_html_e( 'Support dynamic pricing and discount plugins, including Woo Discount Rules, YITH Dynamic Pricing, and Yay Pricing', 'webtoffee-product-feed' ); ?></li>
					<li><?php esc_html_e( 'Variations: Option to choose lowest priced/highest priced/default variant', 'webtoffee-product-feed' ); ?></li>
					<li class="wt-profeed-market-feat-hidden"><?php esc_html_e( 'WPML Multilingual & Multicurrency support', 'webtoffee-product-feed' ); ?></li>
					<li class="wt-profeed-market-feat-hidden"><?php esc_html_e( 'Aelia, Curcy, Fox currency switcher plugin compatibility', 'webtoffee-product-feed' ); ?></li>
					<li class="wt-profeed-market-feat-hidden"><?php esc_html_e( 'Multi-vendor compatibility', 'webtoffee-product-feed' ); ?></li>
					<li class="wt-profeed-market-feat-hidden"><?php esc_html_e( 'Supports WooCommerce Brands, YITH WooCommerce Brands Add-on & Perfect Brands for WooCommerce plugins', 'webtoffee-product-feed' ); ?></li>
					<li class="wt-profeed-market-feat-hidden"><?php esc_html_e( 'Priority support: Make feeds live faster', 'webtoffee-product-feed' ); ?></li>
                                        <?php endif; ?>
				</ul>
				<?php if ( ! isset( $fb_sync_tab ) ) : ?>
					<a href="#" class="wt-profeed-market-toggle" data-show-text="<?php esc_attr_e( 'Show more', 'webtoffee-product-feed' ); ?>" data-hide-text="<?php esc_attr_e( 'Show less', 'webtoffee-product-feed' ); ?>"><?php esc_html_e( 'Show more', 'webtoffee-product-feed' ); ?></a>
				<?php endif; ?>
			</div>
				<div class="wt-profeed-btn-wrapper">
					<?php if( isset( $utm_source ) ){ ?>
					<a href="<?php echo esc_url( 'https://www.webtoffee.com/product/woocommerce-product-feed/?utm_source='.$utm_source.'&utm_medium=feed_basic&utm_campaign=WooCommerce_Product_Feed&utm_content=' . WEBTOFFEE_PRODUCT_FEED_SYNC_VERSION); ?>" class="wt-profeed-blue-btn-new" target="_blank"><?php esc_html_e( 'Unlock Pro features', 'webtoffee-product-feed' ); ?> <span class="dashicons dashicons-arrow-right-alt"></span></a>
					<?php }else { ?>
						<a href="<?php echo esc_url("https://www.webtoffee.com/product/woocommerce-product-feed/?utm_source=free_plugin_new_feed&utm_medium=feed_basic&utm_campaign=WooCommerce_Product_Feed&utm_content=" . WEBTOFFEE_PRODUCT_FEED_SYNC_VERSION); ?>" class="wt-profeed-blue-btn-new" target="_blank"><?php esc_html_e( 'Unlock Pro features', 'webtoffee-product-feed' ); ?> <span class="dashicons dashicons-arrow-right-alt"></span></a>
					<?php } ?>                    
                </div>               			
		</div>	
		<div class="wt-feed-cs-rating-money-back">
			<div class="wt-feed-money-back">
				<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
				<img src="<?php echo esc_url( WT_PRODUCT_FEED_PLUGIN_URL ); ?>assets/images/gopro/new-money-back.svg" alt="<?php esc_attr_e( '30-day money back guarantee', 'webtoffee-product-feed' ); ?>"/>
				<p><?php 
				// translators: %1$s is the opening bold tag, %2$s is the closing bold tag
				echo sprintf(esc_html__('You are covered by our %1$s 30-day money back guarantee %2$s', 'webtoffee-product-feed'), '<b>', '</b>'); ?></p>
			</div>
			<div class="wt-feed-cs-rating">
				<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
				<img src="<?php echo esc_url( WT_PRODUCT_FEED_PLUGIN_URL ); ?>assets/images/gopro/new-wt-satisfaction-rating.svg" alt="<?php esc_attr_e( 'Customer satisfaction rating', 'webtoffee-product-feed' ); ?>"/>
				<p><?php 
				// translators: %1$s is the opening bold tag, %2$s is the percentage value, %3$s is the closing bold tag
				echo sprintf(esc_html__('Supported by a team with %1$s %2$s customer satisfaction score %3$s', 'webtoffee-product-feed'), '<b>', '99%', '</b>'); ?></p>
			</div>
		</div>
    </div>
</div>