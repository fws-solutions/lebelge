<section id="content_trackship_dashboard" class="tab_section">
	<div class="tab_inner_container">	
		<?php 		
		if ( $completed_order_with_tracking > 0 || $completed_order_with_zero_balance > 0 || $completed_order_with_do_connection > 0 ) {
			$total_orders = $completed_order_with_tracking + $completed_order_with_zero_balance + $completed_order_with_do_connection; 
			?>
			<div class="trackship-notice">
				<p>
				<?php
				/* translators: %s: replace with total_orders */
				echo sprintf( esc_html__( 'We detected %s Shipped orders from the last 30 days that were not sent to TrackShip, you can bulk send them to TrackShip', 'woo-advanced-shipment-tracking' ), esc_html( $total_orders ) );
				?>
				</p>
				<button class="button-primary btn_green2 bulk_shipment_status_button" wp_nonce="<?php esc_html_e( wp_create_nonce( 'bulk_shipment_status' ) ); ?>" ><?php esc_html_e( 'Get Shipment Status', 'woo-advanced-shipment-tracking' ); ?></button>
			</div>
			<div class="trackship-notice bulk_shipment_status_success" style="display:none;">
				<p><?php esc_html_e('Tracking info sent to Trackship for all Orders.', 'woo-advanced-shipment-tracking'); ?></p>
			</div>
		<?php } ?>
		<div class="trackship_status_section first">
			<div class="outer_form_table">
				<table class="form-table heading-table">
					<tbody>				
						<tr valign="top">
							<td><h3 style=""><?php esc_html_e( 'TrackShip Connection Status', 'woo-advanced-shipment-tracking' ); ?></h3></td>					
						</tr>
					</tbody>
				</table>	
				<table class="form-table trackship_status_table">
					<tbody>
						<tr valign="top">
							<td scope="row" class="titledesc"><label><strong><?php esc_html_e( 'Connection Status', 'woo-advanced-shipment-tracking' ); ?></strong></label></td>
							<td class="forminp">
								<a href="https://trackship.info/my-account/?utm_source=wpadmin&utm_medium=sidebar&utm_campaign=upgrade" target="_blank" class="button-primary btn_green2 btn_icon api_connected"><span class="dashicons dashicons-yes"></span><?php esc_html_e( 'Connected', 'woo-advanced-shipment-tracking' ); ?></a>
							</td>					
						</tr>					
					</tbody>
				</table>							
			</div>
		</div>
		
		<div class="trackship_status_section last">
			<div class="outer_form_table">
				<table class="form-table heading-table">
					<tbody>				
						<tr valign="top">
							<td><h3 style=""><?php esc_html_e( 'TrackShip Account', 'woo-advanced-shipment-tracking' ); ?></h3></td>					
						</tr>
					</tbody>
				</table>	
				<table class="form-table trackship_status_table">
					<tbody>
						<tr valign="top">
							<td scope="row" class="titledesc">
								<label><?php esc_html_e( 'Subscription: ', 'woo-advanced-shipment-tracking' ); ?></label><strong><?php esc_html_e( isset( $plan_data->subscription_plan ) ? $plan_data->subscription_plan : '' ); ?></strong></br>
								<label><?php esc_html_e( 'Trackers Balance: ', 'woo-advanced-shipment-tracking' ); ?></label><strong><?php esc_html_e( get_option('trackers_balance') ); ?></strong>
							</td>
							<td class="forminp">
								<a href="https://trackship.info/my-account/?utm_source=wpadmin&utm_medium=ts_settings&utm_campaign=dashboard" class="button-primary btn_ts_transparent" target="blank"><?php esc_html_e( 'TrackShip Dashboard', 'woo-advanced-shipment-tracking' ); ?></a>						
							</td>					
						</tr>				
					</tbody>
				</table>							
			</div>
		</div>	
		
		<div class="clearfix"></div>	
	</div>	
	<div class="tab_inner_container">	
		<form method="post" id="wc_ast_trackship_form" action="" enctype="multipart/form-data">
			<div class="outer_form_table">
				<table class="form-table heading-table">
					<tbody>
						<tr valign="top">
							<td>
								<h3 style=""><?php esc_html_e( 'General Settings', 'woo-advanced-shipment-tracking' ); ?></h3>
								<?php wp_nonce_field( 'wc_ast_trackship_form', 'wc_ast_trackship_form_nonce' ); ?>
								<input type="hidden" name="action" value="wc_ast_trackship_form_update">
							</td>							
						</tr>
					</tbody>
				</table>	
				<?php $ast_admin->get_html_ul( $this->get_trackship_general_data() ); ?>															
			</div>													
		</form>			
		
		<form method="post" id="wc_ast_trackship_automation_form" action="" enctype="multipart/form-data">
			<div class="outer_form_table">	
				<table class="form-table heading-table">
					<tbody>
						<tr valign="top">
							<td>
								<h3 style=""><?php esc_html_e( 'Automation', 'woo-advanced-shipment-tracking' ); ?></h3>
								<?php wp_nonce_field( 'wc_ast_trackship_automation_form', 'wc_ast_trackship_automation_form_nonce' ); ?>
								<input type="hidden" name="action" value="wc_ast_trackship_automation_form_update">	
							</td>						
						</tr>
					</tbody>
				</table>
				<div class="custom_order_status_section">
					<table class="form-table order-status-table">
						<tbody>					
							<tr valign="top" class="delivered_row <?php esc_html_e( !get_option('wc_ast_status_delivered') ? 'disable_row' : '' ); ?>">
								<td class="forminp">
									<input type="hidden" name="wc_ast_status_delivered" value="0"/>
									<input class="ast-tgl ast-tgl-flat ts_order_status_toggle" id="wc_ast_status_delivered" name="wc_ast_status_delivered" type="checkbox" <?php esc_html_e( get_option('wc_ast_status_delivered') ? 'checked' : '' ); ?> value="1"/>
									<label class="ast-tgl-btn ast-tgl-btn-green" for="wc_ast_status_delivered"></label>		
								</td>
								<td class="forminp status-label-column">
									<span class="order-label wc-delivered">
										<?php 
										if ( get_option('wc_ast_status_delivered') ) {
											esc_html_e( wc_get_order_status_name( 'delivered' ), 'woo-advanced-shipment-tracking' );	
										} else {
											esc_html_e( 'Delivered', 'woo-advanced-shipment-tracking' );
										}
										?>
									</span>
								</td>								
								<td class="forminp">							
									<fieldset>
										<input class="input-text regular-input color_input" type="text" name="wc_ast_status_label_color" id="wc_ast_status_label_color" style="" value="<?php esc_html_e(  get_option('wc_ast_status_label_color', '#59c889' ) ); ?>" placeholder="">
										<select class="select ts_custom_order_color_select" id="wc_ast_status_label_font_color" name="wc_ast_status_label_font_color">	
											<option value="#fff" <?php esc_html_e( get_option('wc_ast_status_label_font_color', '#fff') == '#fff' ? 'selected' : '' ); ?>><?php esc_html_e( 'Light Font', 'woo-advanced-shipment-tracking' ); ?></option>
											<option value="#000" <?php esc_html_e( get_option('wc_ast_status_label_font_color', '#fff') == '#000' ? 'selected' : '' ); ?>><?php esc_html_e( 'Dark Font', 'woo-advanced-shipment-tracking' ); ?></option>
										</select>							
									</fieldset>
								</td>
							</tr>						
						</tbody>
					</table>	
				</div>	
			</div>	
		</form>
	</div>	
	
	<div class="tab_inner_container">
		<form method="post" id="trackship_tracking_page_form" action="" enctype="multipart/form-data">
			<div class="outer_form_table">
				<table class="form-table heading-table">
					<tbody>
						<tr valign="top">
							<td>
								<h3 style=""><?php esc_html_e( 'Tracking Page', 'woo-advanced-shipment-tracking' ); ?></h3>
								<?php wp_nonce_field( 'trackship_tracking_page_form', 'trackship_tracking_page_form_nonce' ); ?>
								<input type="hidden" name="action" value="trackship_tracking_page_form_update">
							</td>							
						</tr>
					</tbody>
				</table>	
				<?php $ast_admin->get_html_ul( $this->get_tracking_page_data() ); ?>															
			</div>	
		</form>	
	</div>	
</section>
