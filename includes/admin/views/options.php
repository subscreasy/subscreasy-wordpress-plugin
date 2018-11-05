<?php
/**
 * HTML output for the options page.
 */

// Get options.
$options = get_option( 'subscreasy', array() );

// All fields.
$site_name    = isset( $options['site_name'] )    ? $options['site_name'] : '';
$api_key      = isset( $options['api_key'] )      ? $options['api_key'] : '';
$callback_url = isset( $options['callback_url'] ) ? $options['callback_url'] : '';
$environment  = isset( $options['environment'] )  ? $options['environment'] : 'sandbox';
?>

<div class="wrap subscreasy-options">
	<h1><?php _e( 'subscrEASY Options', 'subscreasy' ); ?></h1>
	<form action="options.php" method="post" id="subscreasyOptions">
		<?php settings_fields( 'subscreasy', 'subscreasy' ); ?>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="site_name"><?php _e( 'Site Name', 'subscreasy' ); ?></label></th>
				<td><input name="subscreasy[site_name]" type="text" id="site_name" value="<?php echo $site_name; ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="api_key"><?php _e( 'API Key', 'subscreasy' ); ?></label></th>
				<td><input name="subscreasy[api_key]" type="text" id="api_key" value="<?php echo $api_key; ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="callback_url"><?php _e( 'Callback URL', 'subscreasy' ); ?></label></th>
				<td><input name="subscreasy[callback_url]" type="text" id="callback_url" value="<?php echo $callback_url; ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Environment', 'subscreasy' ); ?></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span><?php _e( 'Environment', 'subscreasy' ); ?></span></legend>
						<p>
							<label><input name="subscreasy[environment]" type="radio" value="sandbox" <?php checked( $environment, 'sandbox' ); ?>> <?php _e( 'Sandbox', 'subscreasy' ); ?></label><br>
							<label><input name="subscreasy[environment]" type="radio" value="production" <?php checked( $environment, 'production' ); ?>> <?php _e( 'Production', 'subscreasy' ); ?></label>
						</p>
					</fieldset>
				</td>
			</tr>
		</table>
		<p class="actions">
			<button id="testConnectivity" class="button test-connectivity"><?php _e( 'Test Connectivity', 'subscreasy' ); ?></button>
			<?php submit_button( 'Save', 'primary', 'submit', false ); ?>
		</p>
	</form>
	<div id="testData"></div>
</div>

<div id="subscreasy-overlay">
	<div class="loader">
		<img src="<?php echo SUBSCREASY_ROOT_URL . 'assets/images/loading.gif'; ?>" />
	</div>
</div>