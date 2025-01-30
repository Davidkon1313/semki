<?php
/*
Plugin Name: Custom SEO Title and Description
Plugin URI: https://example.com
Description: Adds custom title and description fields to pages and posts for SEO.
Version: 1.0
Author: David
Author URI: https://example.com
*/

// Add custom fields to the page/post editor
function custom_seo_fields()
{
    add_meta_box(
        'custom_seo_meta_box',           // ID of the meta box
        'Custom SEO Title & Description', // Title
        'custom_seo_meta_box_callback',   // Callback function
        ['post', 'page'],                // Post types (pages and posts)
        'normal',                        // Context
        'high'                           // Priority
    );
}
add_action('add_meta_boxes', 'custom_seo_fields');

// Callback function to render fields in the editor
function custom_seo_meta_box_callback($post)
{
    // Retrieve current SEO title and description
    $seo_title = get_post_meta($post->ID, '_custom_seo_title', true);
    $seo_description = get_post_meta($post->ID, '_custom_seo_description', true);

    // Display fields
?>
    <label for="custom_seo_title">SEO Title:</label><br>
    <input type="text" id="custom_seo_title" name="custom_seo_title" value="<?php echo esc_attr($seo_title); ?>" style="width:100%;" /><br><br>

    <label for="custom_seo_description">SEO Description:</label><br>
    <textarea id="custom_seo_description" name="custom_seo_description" rows="4" style="width:100%;"><?php echo esc_textarea($seo_description); ?></textarea>
<?php
}

// Save the custom SEO data when the post/page is saved
function save_custom_seo_data($post_id)
{
    // Check if our nonce is set (if you're using a nonce for security, but it's optional)
    if (isset($_POST['custom_seo_title'])) {
        update_post_meta($post_id, '_custom_seo_title', sanitize_text_field($_POST['custom_seo_title']));
    }
    if (isset($_POST['custom_seo_description'])) {
        update_post_meta($post_id, '_custom_seo_description', sanitize_textarea_field($_POST['custom_seo_description']));
    }
}
add_action('save_post', 'save_custom_seo_data');

// Output the custom title and description in the <head> of the site (for SEO purposes)
// Output the custom title and description in the <head> of the site (for SEO purposes)
function output_custom_seo_meta() {
    if (is_front_page()) {
        // If it's the front page, use a custom description
        echo '<meta name="description" content="EPIC Foods – постачальник смачних та якісних снеків гуртом. Насіння, горіхи, арахіс із різними смаками. Мінімальне замовлення від 5000 грн. Співпрацюйте з нами та отримуйте до 35% прибутку!" />' . "\n";
    } elseif (is_singular()) {
        // For other pages and posts, get the custom SEO title and description from the plugin fields
        global $post;
        $seo_title = get_post_meta($post->ID, '_custom_seo_title', true);
        $seo_description = get_post_meta($post->ID, '_custom_seo_description', true);

        if ($seo_title) {
            echo '<meta name="title" content="' . esc_attr($seo_title) . '" />' . "\n";
        }
        if ($seo_description) {
            echo '<meta name="description" content="' . esc_attr($seo_description) . '" />' . "\n";
        }
    }
}
add_action('wp_head', 'output_custom_seo_meta');

add_action('wp_head', 'output_custom_seo_meta');
?>