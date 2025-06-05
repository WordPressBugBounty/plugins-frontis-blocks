<?php
$form_id = isset( $attributes['formId'] ) ? esc_attr( $attributes['formId'] ) : '';
$block_id = isset( $attributes['blockID'] ) ? esc_attr( $attributes['blockID'] ) : '';
$show_placeholder = isset( $attributes['showPlaceholder'] ) ? $attributes['showPlaceholder'] : true;
$show_error_message = isset( $attributes['showErrorMessage'] ) ? $attributes['showErrorMessage'] : true;

$contactFormGlobalWraperID = isset( $attributes['contactFormGlobalWraperID'] ) ? esc_attr( $attributes['contactFormGlobalWraperID'] ) : '';
$contactFormGlobalWraperClass = isset( $attributes['contactFormGlobalWraperClass'] ) ? esc_attr( $attributes['contactFormGlobalWraperClass'] ) : '';

$wrapper_classes = [
	'fb-parent-wrapper fb-contact-form-wrapper',
	$block_id,
	!$show_placeholder ? 'fb-contact-form-hide-placeholder' : '',
	!$show_error_message ? 'fb-contact-form-hide-error-message' : '',
];

$form_wrapper_classes = [
	'fb-contact-form',
	$contactFormGlobalWraperClass,
];

$shortcode = sprintf( '[contact-form-7 id="%s"]', $form_id );

?>
<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
	<div class="<?php echo esc_attr( implode( ' ', $form_wrapper_classes ) ); ?>" id="<?php echo esc_attr( $contactFormGlobalWraperID ); ?>">
		<?php echo do_shortcode( shortcode_unautop( wp_kses_post( $shortcode ) ) ); ?>
	</div>
</div>
