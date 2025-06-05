<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

// Get block attributes
$blockID                        = isset( $attributes['blockID'] ) ? sanitize_text_field( $attributes['blockID'] ) : '';
$deviceType                     = isset( $attributes['deviceType'] ) ? sanitize_text_field( $attributes['deviceType'] ) : 'Desktop';
$formId                         = isset( $attributes['formId'] ) ? sanitize_text_field( $attributes['formId'] ) : '';

$showLabels                     = isset( $attributes['showLabels'] ) ? (bool) $attributes['showLabels'] : true;
$showPlaceholder                = isset( $attributes['showPlaceholder'] ) ? (bool) $attributes['showPlaceholder'] : true;
$showErrorMessage               = isset( $attributes['showErrorMessage'] ) ? (bool) $attributes['showErrorMessage'] : true;
$fluentFormGlobalZIndex         = isset( $attributes['fluentFormGlobalZIndex'] ) ? intval( $attributes['fluentFormGlobalZIndex'] ) : '';

$fluentFormSubmitWidthSelect 	= isset($attributes['fluentFormSubmitWidthSelect']) ? sanitize_text_field($attributes['fluentFormSubmitWidthSelect']) : 'custom';
$fluentFormSubmitWidth 			= isset($attributes['fluentFormSubmitWidth']) ? array_map('intval', $attributes['fluentFormSubmitWidth']) : ['Desktop' => '', 'Tablet' => '', 'Mobile' => ''];
$fluentFormSubmitWidthUnits     = isset($attributes['fluentFormSubmitWidthUnits']) ? array_map('sanitize_text_field', $attributes['fluentFormSubmitWidthUnits']) : ['Desktop' => 'px', 'Tablet' => 'px', 'Mobile' => 'px'];

$fluentFormGlobalWraperID       = isset( $attributes['fluentFormGlobalWraperID'] ) ? sanitize_text_field( $attributes['fluentFormGlobalWraperID'] ) : '';
$fluentFormGlobalWraperClass   = isset( $attributes['fluentFormGlobalWraperClass'] ) ? sanitize_text_field( $attributes['fluentFormGlobalWraperClass'] ) : '';

$_wrapper_classes = [
    "fb-parent-$blockID",
];


$_form_wrapper_classes = [
    $blockID,
    'fb-parent-wrapper fb-fluent-form-wrapper',
    $fluentFormGlobalWraperClass,
    !$showLabels ? 'fb-fluent-form-hide-label' : '',
    !$showPlaceholder ? 'fb-fluent-form-hide-placeholder' : '',
    !$showErrorMessage ? 'fb-fluent-form-hide-error-message' : '',
];

// Remove any empty classes
$_form_wrapper_classes = array_filter($_form_wrapper_classes);

$wrapper_attributes = get_block_wrapper_attributes(
    [
        'class' => $blockID
    ]
);


?>

<style>

    .<?php echo esc_attr( $blockID ); ?>.fb-fluent-form-wrapper .ff_submit_btn_wrapper .ff-btn-submit {
        <?php if ( $fluentFormSubmitWidthSelect === 'full-width' ) : ?>
            width: 100%;
        <?php elseif ( $fluentFormSubmitWidthSelect === 'custom' && isset( $fluentFormSubmitWidth['Desktop']) ) : ?>
            width: <?php echo esc_attr( $fluentFormSubmitWidth['Desktop'] . $fluentFormSubmitWidthUnits['Desktop']); ?>;
        <?php endif; ?>
    }

</style>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<div class="<?php echo esc_attr( implode( ' ', $_wrapper_classes ) ); ?> fb-parent-wrapper">
		<div class="<?php echo esc_attr( implode( ' ', $_form_wrapper_classes ) ); ?>" id="<?php echo esc_attr( $fluentFormGlobalWraperID ); ?>">
			<?php
                $shortcode = sprintf( '[fluentform id="%s"]', esc_attr( $formId ) );
                echo do_shortcode( shortcode_unautop( wp_kses_post( $shortcode ) ) );
            ?>
		</div>
	</div>
</div>