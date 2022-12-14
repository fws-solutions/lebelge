<?php
/**
 * The template for displaying Tracking Form 
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/tracking/tracking-form.php
 * 
 */
?> 
<div class="track-order-section">
	<form method="post" class="order_track_form">			
		<p><?php esc_html_e( apply_filters( 'ast_tracking_page_front_text', __( 'To track your order please enter your Order ID in the box below and press the "Track" button. This was given to you on your receipt and in the confirmation email you should have received.', 'woo-advanced-shipment-tracking' ) ) ); ?></p>
		<p class="form-row form-row-first"><label for="order_id"><?php esc_html_e( apply_filters( 'ast_tracking_page_front_order_label', __( 'Order ID', 'woocommerce' ) ) ); ?></label> <input class="input-text" type="text" name="order_id" id="order_id" value="" placeholder="<?php esc_html_e( 'Found in your order confirmation email.', 'woo-advanced-shipment-tracking' ); ?>"></p>
		<p class="form-row form-row-last"><label for="order_email"><?php esc_html_e( apply_filters( 'ast_tracking_page_front_order_email_label', __( 'Order Email', 'woo-advanced-shipment-tracking' ) ) ); ?></label> <input class="input-text" type="text" name="order_email" id="order_email" value="" placeholder="<?php esc_html_e( 'Found in your order confirmation email.', 'woo-advanced-shipment-tracking' ); ?>"></p>				
		<div class="clear"></div>
		<input type="hidden" name="action" value="get_tracking_info">
		<p class="form-row"><button type="submit" class="button" name="track" value="Track"><?php echo esc_html( apply_filters( 'ast_tracking_page_front_track_label', __( 'Track', 'woo-advanced-shipment-tracking' ) ) ); ?></button></p>
		<div class="track_fail_msg" style="display:none;color: red;"></div>	
		<?php wp_nonce_field( 'tracking_form', 'tracking_form_nonce' ); ?>
	</form>
</div>
