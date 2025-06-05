<?php
// Extract attributes with fallbacks
$block_id = isset($attributes['blockId']) ? $attributes['blockId'] : '';
$heading_text = isset($attributes['headingText']) ? $attributes['headingText'] : 'Advanced Heading';
$heading_tag = isset($attributes['headingTag']) ? $attributes['headingTag'] : 'h2';
$heading_alignment = isset($attributes['headingAlignment']) ? $attributes['headingAlignment'] : '';
$sub_heading_switcher = isset($attributes['subHeadingSwitcher']) ? $attributes['subHeadingSwitcher'] : false;
$sub_heading_text = isset($attributes['subHeadingText']) ? $attributes['subHeadingText'] : 'Advanced Sub Heading';
$sub_heading_tag = isset($attributes['subHeadingTag']) ? $attributes['subHeadingTag'] : 'p';
$sub_heading_position = isset($attributes['subHeadingPosition']) ? $attributes['subHeadingPosition'] : 'bottom';
$active_line_switcher = isset($attributes['activeLineSwitcher']) ? $attributes['activeLineSwitcher'] : false;
$heading_icon_switcher = isset($attributes['headingIconSwitcher']) ? $attributes['headingIconSwitcher'] : false;
$heading_icon = isset($attributes['headingIcon']) ? $attributes['headingIcon'] : 'bookmark';
$heading_icon_align = isset($attributes['headingIconAlign']) ? $attributes['headingIconAlign'] : 'icon';
$show_separator_switcher = isset($attributes['showSeparatorSwitcher']) ? $attributes['showSeparatorSwitcher'] : false;
$separator = isset($attributes['separator']) ? $attributes['separator'] : '';
$separator_position = isset($attributes['separatorPosition']) ? $attributes['separatorPosition'] : 'bottom';
$separator_color = isset($attributes['separatorColor']) ? $attributes['separatorColor'] : '#000000';
$dynamic_content = isset($attributes['dynamicContent']) ? $attributes['dynamicContent'] : '';
$heading_global_wraper_id = isset($attributes['headingGlobalWraperID']) ? $attributes['headingGlobalWraperID'] : '';
$heading_global_wraper_class = isset($attributes['headingGlobalWraperClass']) ? $attributes['headingGlobalWraperClass'] : '';

$advanced_heading_desktop_switcher = isset($attributes['advancedHeadingDesktopSwitcher']) ? $attributes['advancedHeadingDesktopSwitcher'] : false;
$advanced_heading_tablet_switcher = isset($attributes['advancedHeadingTabletSwitcher']) ? $attributes['advancedHeadingTabletSwitcher'] : false;
$advanced_heading_mobile_switcher = isset($attributes['advancedHeadingMobileSwitcher']) ? $attributes['advancedHeadingMobileSwitcher'] : false;

// Handle dynamic content
$dynamic_value = '';
if (!empty($dynamic_content)) {
    // If we're in a query loop context
    if (isset($block->context['postId'])) {
        $post_id = $block->context['postId'];

        switch ($dynamic_content) {
            case 'post_title':
                $dynamic_value = get_the_title($post_id);
                break;
            case 'post_date':
                $dynamic_value = get_the_date('', $post_id);
                break;
            default:
                $dynamic_value = '';
        }
    } else {
        // Regular context (not in a query loop)
        global $post;

        switch ($dynamic_content) {
            case 'post_title':
                $dynamic_value = get_the_title();
                break;
            case 'post_date':
                $dynamic_value = get_the_date();
                break;
            default:
                $dynamic_value = '';
        }
    }
}

// Determine the heading content (dynamic or static)
$heading_content = !empty($dynamic_value) ? $dynamic_value : $heading_text;

// Get responsive classes
$responsive_classes = [];
if ($advanced_heading_desktop_switcher) {
    $responsive_classes[] = 'fb-desktop-responsive';
}
if ($advanced_heading_tablet_switcher) {
    $responsive_classes[] = 'fb-tablet-responsive';
}
if ($advanced_heading_mobile_switcher) {
    $responsive_classes[] = 'fb-mobile-responsive';
}

$classes = array_merge(['frontis-block', 'fb-advanced-heading-main-wrapper', $block_id], $responsive_classes);
$wrapper_attributes = get_block_wrapper_attributes(['class' => implode(' ', $classes)]);

// Helper function for rendering SVG icons (inline definition)
$render_icon_svg = function($icon) {
    if (empty($icon)) {
        return '';
    }
    
    // Assuming icon has 'svg' property with the SVG string
    if (isset($icon['svg'])) {
        return $icon['svg'];
    }
    
    return '';
};


var_dump($heading_icon);

// Start building the HTML output
$output = '<div ' . $wrapper_attributes . '>';
$output .= '<div class="fb-parent-wrapper fb-advanced-wrapper ' . esc_attr($heading_global_wraper_class) . '" id="' . esc_attr($heading_global_wraper_id) . '" style="text-align: ' . esc_attr($heading_alignment) . ';">';


// For the icon rendering part, replace the current code with this
if ($heading_icon_switcher && $heading_icon_align === 'icon') {
    $output .= '<div class="fb-advanced-icon-wrapper" style="justify-content: ' . esc_attr($heading_alignment) . ';">';
    $output .= '<div class="fb-advanced-icon">';
    
    // Check if heading_icon is a string (icon identifier)
    if (is_string($heading_icon)) {
        // Get the SVG HTML for this icon from WordPress database or a predefined array
        // $icon_svg = fb_get_icon_svg($heading_icon);
        // if ($icon_svg) {
        //     $output .= $icon_svg;
        // } else {
        //     // Fallback - display a generic icon or nothing
        //     $output .= '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M19 5v14H5V5h14m0-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"></path></svg>';
        // }
    }
    // If it's an object with direct SVG data
    elseif (is_array($heading_icon) && isset($heading_icon['svg'])) {
        $output .= $heading_icon['svg'];
    }
    
    $output .= '</div>';
    $output .= '</div>';
}

if ($show_separator_switcher && $separator_position === 'top') {
    $output .= '<div class="fb-advanced-title-separator">';
    if ($separator === 'zigzag') {
        $output .= '<div class="fb-advanced-title-separator-zigzag">';
        $output .= '<svg class="fb-advanced-title-separator-zigzag-svg" width="322" height="7" viewBox="0 0 322 7" fill="none" xmlns="http://www.w3.org/2000/svg" style="stroke: ' . esc_attr($separator_color) . ';">';
        $output .= '<path d="M0.349609 5.71094L5.34961 0.710938L10.3496 5.71094L15.3496 0.710938L20.3496 5.71094L25.3496 0.710938L30.3496 5.71094L35.3496 0.710938L40.3496 5.71094L45.3496 0.710938L50.3496 5.71094L55.3496 0.710938L60.3496 5.71094L65.3496 0.710938L70.3496 5.71094L75.3496 0.710938L80.3496 5.71094L85.3496 0.710938L90.3496 5.71094L95.3496 0.710938L100.35 5.71094L105.35 0.710938L110.35 5.71094L115.35 0.710938L120.35 5.71094L125.35 0.710938L130.35 5.71094L135.35 0.710938L140.35 5.71094L145.35 0.710938L150.35 5.71094L155.35 0.710938L160.35 5.71094L165.35 0.710938L170.35 5.71094L175.35 0.710938L180.35 5.71094L185.35 0.710938L190.35 5.71094L195.35 0.710938L200.35 5.71094L205.35 0.710938L210.35 5.71094L215.35 0.710938L220.35 5.71094L225.35 0.710938L230.35 5.71094L235.35 0.710938L240.35 5.71094L245.35 0.710938L250.35 5.71094L255.35 0.710938L260.35 5.71094L265.35 0.710938L270.35 5.71094L275.35 0.710938L280.35 5.71094L285.35 0.710938L290.35 5.71094L295.35 0.710938L300.35 5.71094L305.35 0.710938L310.35 5.71094L315.35 0.710938L320.35 5.71094" strokeWidth="2" stroke-miterlimit="10" />';
        $output .= '</svg>';
        $output .= '</div>';
    }
    $output .= '</div>';
}

// Add sub-heading if enabled and positioned at top
if ($sub_heading_switcher && $sub_heading_position === 'top') {
    $output .= '<div class="fb-advanced-sub-heading-wrapper">';
    $output .= '<' . esc_attr($sub_heading_tag) . ' class="fb-advanced-sub-heading">' . wp_kses_post($sub_heading_text) . '</' . esc_attr($sub_heading_tag) . '>';
    $output .= '</div>';
}

// Add main heading
$heading_class = 'fb-advanced-heading' . ($active_line_switcher ? ' active-line' : '');
$output .= '<div class="fb-advanced-heading-wrapper">';
$output .= '<' . esc_attr($heading_tag) . ' class="' . esc_attr($heading_class) . '">' . wp_kses_post($heading_content) . '</' . esc_attr($heading_tag) . '>';
$output .= '</div>';

// Add sub-heading if enabled and positioned at bottom
if ($sub_heading_switcher && $sub_heading_position === 'bottom') {
    $output .= '<div class="fb-advanced-sub-heading-wrapper">';
    $output .= '<' . esc_attr($sub_heading_tag) . ' class="fb-advanced-sub-heading">' . wp_kses_post($sub_heading_text) . '</' . esc_attr($sub_heading_tag) . '>';
    $output .= '</div>';
}

// Add bottom separator if enabled
if ($show_separator_switcher && $separator_position === 'bottom') {
    $output .= '<div class="fb-advanced-title-separator">';
    if ($separator === 'zigzag') {
        $output .= '<div class="fb-advanced-title-separator-zigzag">';
        $output .= '<svg class="fb-advanced-title-separator-zigzag-svg" width="322" height="7" viewBox="0 0 322 7" fill="none" xmlns="http://www.w3.org/2000/svg" style="stroke: ' . esc_attr($separator_color) . ';">';
        $output .= '<path d="M0.349609 5.71094L5.34961 0.710938L10.3496 5.71094L15.3496 0.710938L20.3496 5.71094L25.3496 0.710938L30.3496 5.71094L35.3496 0.710938L40.3496 5.71094L45.3496 0.710938L50.3496 5.71094L55.3496 0.710938L60.3496 5.71094L65.3496 0.710938L70.3496 5.71094L75.3496 0.710938L80.3496 5.71094L85.3496 0.710938L90.3496 5.71094L95.3496 0.710938L100.35 5.71094L105.35 0.710938L110.35 5.71094L115.35 0.710938L120.35 5.71094L125.35 0.710938L130.35 5.71094L135.35 0.710938L140.35 5.71094L145.35 0.710938L150.35 5.71094L155.35 0.710938L160.35 5.71094L165.35 0.710938L170.35 5.71094L175.35 0.710938L180.35 5.71094L185.35 0.710938L190.35 5.71094L195.35 0.710938L200.35 5.71094L205.35 0.710938L210.35 5.71094L215.35 0.710938L220.35 5.71094L225.35 0.710938L230.35 5.71094L235.35 0.710938L240.35 5.71094L245.35 0.710938L250.35 5.71094L255.35 0.710938L260.35 5.71094L265.35 0.710938L270.35 5.71094L275.35 0.710938L280.35 5.71094L285.35 0.710938L290.35 5.71094L295.35 0.710938L300.35 5.71094L305.35 0.710938L310.35 5.71094L315.35 0.710938L320.35 5.71094" strokeWidth="2" stroke-miterlimit="10" />';
        $output .= '</svg>';
        $output .= '</div>';
    }
    $output .= '</div>';
}

$output .= '</div>';
$output .= '</div>';

// Echo the final output
echo $output;