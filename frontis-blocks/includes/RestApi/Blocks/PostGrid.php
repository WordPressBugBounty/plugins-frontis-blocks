<?php
namespace FrontisBlocks\RestApi\Blocks;

use WP_REST_Response;
use WP_Error;
use WP_REST_Request;
use FrontisBlocks\Traits\Singleton;
use FrontisBlocks\Utils\Helper;


class PostGrid {
    use Singleton;

    /**
     * Constructor
     */
    public function __construct() {
        add_action('rest_api_init', [$this, 'fb_register_routes']);
    }

    /**
     * Register routes for the post grid block
     */
    public function fb_register_routes() {

        register_rest_route('wp/v2/frontis-blocks', '/post', [
			'methods' => 'POST',
			'callback' => [$this, 'get_posts'],
			'permission_callback' => function () {
				return true;
			},
		]);

        $post_type = Helper::get_post_types();

		foreach ( $post_type as $key => $value ) {
			register_rest_field(
				$value['value'],
				'fb_featured_image_src',
				array(
					'get_callback'    => array( $this, 'get_post_img_src' ),
					'update_callback' => null,
					'schema'          => null,
				)
			);

			// Add author info.
			register_rest_field(
				$value['value'],
				'fb_author_info',
				array(
					'get_callback'    => array( $this, 'get_post_author_info' ),
					'update_callback' => null,
					'schema'          => null,
				)
			);

			// Add category info.
			register_rest_field(
				$value['value'],
				'fb_category_info',
				array(
					'get_callback'    => array( $this, 'get_post_category_info' ),
					'update_callback' => null,
					'schema'          => null,
				)
			);

			// Add post_tag info.
			register_rest_field(
				$value['value'],
				'fb_post_tag_info',
				array(
					'get_callback'    => array( $this, 'get_post_post_tag_info' ),
					'update_callback' => null,
					'schema'          => null,
				)
			);

			// Add excerpt info.
			register_rest_field(
				$value['value'],
				'fb_post_excerpt',
				array(
					'get_callback'    => array( $this, 'get_post_excerpt' ),
					'update_callback' => null,
					'schema'          => null,
				)
			);
		}
    }

    /**
     * Get featured image source for the rest field as per size.
     */
	public function get_post_img_src( $object, $field_name, $request ) {
		$image_sizes = Helper::get_image_sizes();

		$featured_images = array();

		if ( ! isset( $object['featured_media'] ) ) {
			return $featured_images;
		}

		foreach ( $image_sizes as $key => $value ) {
			$size = $value['value'];

			$featured_images[ $size ] = wp_get_attachment_image_src(
				$object['featured_media'],
				$size,
				false
			);
		}

		return $featured_images;
	}

    /**
     * Get author info for the rest field.
     */
    public function get_post_author_info($object, $field_name, $request) {
        $author_id = $object['author'];

        $author_has_posts = count_user_posts($author_id, 'post', true) > 0;

        if (!$author_has_posts) {
            return null;
        }

        $latest_post = get_posts(array(
            'author' => $author_id,
            'post_type' => 'post',
            'posts_per_page' => 1,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ));

        $latest_post_date = $latest_post ? get_the_date('j M Y', $latest_post[0]) : null;

        $author_name = get_the_author_meta('display_name', $author_id);

        $author_avatar = get_avatar_url($author_id);

        $author_bio = get_the_author_meta('description', $author_id);

        $author_archive_url = get_author_posts_url($author_id);

        $author_email = get_the_author_meta('email', $author_id);

        return array(
            'name'        => $author_name,
            'avatar'      => $author_avatar,
            'bio'         => $author_bio,
            'archive_url' => $author_archive_url,
            'email'       => $author_email,
            'latest_post_date' => $latest_post_date,
        );
    }

    /**
     * Get category info for the rest field.
     */
    public function get_post_category_info( $object, $field_name, $request ) {
        $post_id   = $object['id'];
        $post_type = get_post_type( $post_id );
    
        $tax_objects = get_object_taxonomies( $post_type, 'objects' );
    
        $category_tax = null;
        foreach ( $tax_objects as $tax ) {
            if ( $tax->name === 'category' ) {
                $category_tax = 'category';
                break;
            }
        }

        if ( ! $category_tax ) {
            foreach ( $tax_objects as $tax ) {
                if ( strpos( $tax->name, 'category' ) !== false || ! empty( $tax->hierarchical ) ) {
                    $category_tax = $tax->name;
                    break;
                }
            }
        }
    
        if ( ! $category_tax ) {
            return [];
        }
    
        $terms = get_the_terms( $post_id, $category_tax );
        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            return [];
        }
    
        return array_map( function ( $term ) use ( $category_tax ) {
            return [
                'id'       => $term->term_id,
                'name'     => $term->name,
                'slug'     => $term->slug,
                'url'      => get_term_link( $term, $category_tax ),
                'taxonomy' => $term->taxonomy,
            ];
        }, $terms );
    }

    /**
     * Get post tag info for the rest field.
     */
    public function get_post_post_tag_info( $object, $field_name, $request ) {
        $post_id   = $object['id'];
        $post_type = get_post_type( $post_id );
    
        $tax_objects = get_object_taxonomies( $post_type, 'objects' );
    
        $tag_tax = null;
        foreach ( $tax_objects as $tax ) {
            if ( $tax->name === 'post_tag' ) {
                $tag_tax = 'post_tag';
                break;
            }
        }
    
        if ( ! $tag_tax ) {
            foreach ( $tax_objects as $tax ) {
                if ( strpos( $tax->name, 'tag' ) !== false || empty( $tax->hierarchical ) ) {
                    $tag_tax = $tax->name;
                    break;
                }
            }
        }
    
        if ( ! $tag_tax ) {
            return [];
        }
    
        $terms = get_the_terms( $post_id, $tag_tax );
        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            return [];
        }
    
        return array_map( function( $term ) use ( $tag_tax ) {
            $url = get_term_link( $term, $tag_tax );
            if ( is_wp_error( $url ) ) {
                $url = '';
            }
            return [
                'id'       => $term->term_id,
                'name'     => $term->name,
                'slug'     => $term->slug,
                'url'      => $url,
                'taxonomy' => $term->taxonomy,
            ];
        }, $terms );
    }

    /**
	 * Get excerpt for the rest field.
	 */
	public function get_post_excerpt( $object, $field_name, $request ) {
		$excerpt = wp_trim_words( get_the_excerpt( $object['id'] ) );
		if ( ! $excerpt ) {
			$excerpt = null;
		}
		return $excerpt;
	}

    /**
     * Resolve category tax for the post
     */
    public function fb_resolve_category_tax_for_post( $post_id ) {
        $post_type   = get_post_type( $post_id );
        $tax_objects = get_object_taxonomies( $post_type, 'objects' );

        foreach ( $tax_objects as $tax ) {
            if ( $tax->name === 'category' ) return 'category';
        }
        foreach ( $tax_objects as $tax ) {
            if ( strpos( $tax->name, 'category' ) !== false || ! empty( $tax->hierarchical ) ) {
                return $tax->name;
            }
        }
        return null;
    }

    /**
     * Resolve tag tax for the post
     */
    public function fb_resolve_tag_tax_for_post( $post_id ) {
        $post_type   = get_post_type( $post_id );
        $tax_objects = get_object_taxonomies( $post_type, 'objects' );

        foreach ( $tax_objects as $tax ) {
            if ( $tax->name === 'post_tag' ) return 'post_tag';
        }
        foreach ( $tax_objects as $tax ) {
            if ( strpos( $tax->name, 'tag' ) !== false || empty( $tax->hierarchical ) ) {
                return $tax->name;
            }
        }
        return null;
    }

    /**
     * Get posts for the rest field
     */
    public function get_posts() {
    	global $wpdb;

        // Get parameters from AJAX request
        $post_type = isset($_POST['post_type']) ? $_POST['post_type'] : 'post';
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 12;
        $category = isset($_POST['category']) ? $_POST['category'] : '';
        $post_tag = isset($_POST['post_tag']) ? $_POST['post_tag'] : '';
        $post_author = isset($_POST['post_author']) ? $_POST['post_author'] : '';
        $deselected_post_author = isset($_POST['deauthor']) ? $_POST['deauthor'] : '';
        $deselected_post_tag = isset($_POST['detag']) ? $_POST['detag'] : '';
        $deselected_post_category = isset($_POST['decategory']) ? $_POST['decategory'] : '';
        $current_page = isset($_POST['currentPage']) ? intval($_POST['currentPage']) : 1;
        $orderby = isset($_POST['orderby']) ? $_POST['orderby'] : 'date';
        $sortorder = isset($_POST['sortorder']) ? $_POST['sortorder'] : 'desc';

        $post_title_tag = isset($_POST['postTitleTag']) ? $_POST['postTitleTag'] : 'h3';
        $post_description_tag = isset($_POST['postDescriptionTag']) ? $_POST['postDescriptionTag'] : 'p';
        $post_description_excerpt_length = isset($_POST['postDescriptionExcerptLength']) ? intval($_POST['postDescriptionExcerptLength']) : 10;
        $show_excerpt_switcher = isset($_POST['showExcerptSwitcher']) ? $_POST['showExcerptSwitcher'] : true;

        $show_author_switcher = isset($_POST['showAuthorSwitcher']) ? $_POST['showAuthorSwitcher'] : false;
        $show_category_switcher = isset($_POST['showCategorySwitcher']) ? $_POST['showCategorySwitcher'] : false;
        $show_tag_switcher = isset($_POST['showTagSwitcher']) ? $_POST['showTagSwitcher'] : false;

        $post_author_prefix_switcher = isset($_POST['postAuthorPrefixSwitcher']) ? $_POST['postAuthorPrefixSwitcher'] : true;
        $post_author_prefix = isset($_POST['postAuthorPrefix']) ? $_POST['postAuthorPrefix'] : 'Posted By';

        $show_date_switcher = isset($_POST['showDateSwitcher']) ? $_POST['showDateSwitcher'] : true;

        $post_author_avatar_switcher = isset($_POST['postAuthorAvatarSwitcher']) ? $_POST['postAuthorAvatarSwitcher'] : true;

        $post_image_size = isset($_POST['postImageSize']) ? $_POST['postImageSize'] : 'full';

        $show_load_more_switcher = isset($_POST['showLoadMoreSwitcher']) ? $_POST['showLoadMoreSwitcher'] : true;

        $default_image_url = isset($_POST['defaultImageUrl']) ? $_POST['defaultImageUrl'] : '';

        $load_more_type = isset($_POST['loadMoreType']) ? $_POST['loadMoreType'] : 'button';

        $pagination_prev_next_type = isset($_POST['paginationPrevNextType']) ? $_POST['paginationPrevNextType'] : 'icon';
        $pagination_prev_text = isset($_POST['paginationPrevText']) ? $_POST['paginationPrevText'] : 'Prev';
        $pagination_next_text = isset($_POST['paginationNextText']) ? $_POST['paginationNextText'] : 'Next';

        $premade_style = isset($_POST['premade_style']) ? $_POST['premade_style'] : 'style-1';

        $read_more_switcher = isset($_POST['readMoreSwitcher']) ? $_POST['readMoreSwitcher'] : false;
        $read_more_text = isset($_POST['readMoreText']) ? $_POST['readMoreText'] : '';
        $read_more_icon_align = isset($_POST['readMoreIconAlign']) ? $_POST['readMoreIconAlign'] : 'icon';
        $read_more_icon = isset($_POST['readMoreIcon']) ? $_POST['readMoreIcon'] : 'angle-right';
        $read_more_icon_image_url = isset($_POST['readMoreIconImageUrl']) ? $_POST['readMoreIconImageUrl'] : '';
        $read_more_icon_image_alt = isset($_POST['readMoreIconImageAlt']) ? $_POST['readMoreIconImageAlt'] : '';

        $custom_permalink_switcher = isset($_POST['customPermalinkSwitcher']) ? $_POST['customPermalinkSwitcher'] : false;
        $custom_permalink = isset($_POST['customPermalink']) ? $_POST['customPermalink'] : '';

        $thumbnail_link_switcher = isset($_POST['thumbnailLinkSwitcher']) ? $_POST['thumbnailLinkSwitcher'] : true;
        $category_link_switcher = isset($_POST['categoryLinkSwitcher']) ? $_POST['categoryLinkSwitcher'] : true;
        $tag_link_switcher = isset($_POST['tagLinkSwitcher']) ? $_POST['tagLinkSwitcher'] : true;
        $author_link_switcher = isset($_POST['authorLinkSwitcher']) ? $_POST['authorLinkSwitcher'] : true;
        $title_link_switcher = isset($_POST['titleLinkSwitcher']) ? $_POST['titleLinkSwitcher'] : true;

        $category_key = isset($_POST['categoryKey']) ? $_POST['categoryKey'] : 'category';
        $tag_key = isset($_POST['tagKey']) ? $_POST['tagKey'] : 'post_tag';


        ob_start();

        /**
         * Initial query to select posts based on type and status
         */
        $query = $wpdb->prepare("
            SELECT *
            FROM {$wpdb->posts}
            WHERE post_type = %s
            AND post_status = 'publish'
        ", $post_type);

        /**
         * Selected post category
         */
        if (!empty($category)) {
            $category_ids = explode(',', $category);
            $placeholders = implode(',', array_fill(0, count($category_ids), '%d'));
            $query .= $wpdb->prepare(" AND ID IN (
                SELECT object_id
                FROM {$wpdb->term_relationships} AS tr
                INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                WHERE tt.taxonomy = '{$category_key}' AND tt.term_id IN ($placeholders)
            )", $category_ids);
        }

        /**
         * Deselected post category
         */
        if (!empty($deselected_post_category)) {
            $deselected_post_category_ids = explode(',', $deselected_post_category);
            $placeholders = implode(',', array_fill(0, count($deselected_post_category_ids), '%d'));
            $query .= $wpdb->prepare(" AND ID NOT IN (
                SELECT object_id
                FROM {$wpdb->term_relationships} AS tr
                INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                WHERE tt.taxonomy = '{$category_key}' AND tt.term_id IN ($placeholders)
            )", $deselected_post_category_ids);
        }

        /**
         * Selected post tag
         */
        if (!empty($post_tag)) {
            $post_tag_ids = explode(',', $post_tag);
            $placeholders = implode(',', array_fill(0, count($post_tag_ids), '%d'));
            $query .= $wpdb->prepare(" AND ID IN (
                SELECT object_id
                FROM {$wpdb->term_relationships} AS tr
                INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                WHERE tt.taxonomy = '{$tag_key}' AND tt.term_id IN ($placeholders)
            )", $post_tag_ids);
        }

        /**
         * Deselected post tag
         */
        if (!empty($deselected_post_tag)) {
            $deselected_post_tag_ids = explode(',', $deselected_post_tag);
            $placeholders = implode(',', array_fill(0, count($deselected_post_tag_ids), '%d'));
            $query .= $wpdb->prepare(" AND ID NOT IN (
                SELECT object_id
                FROM {$wpdb->term_relationships} AS tr
                INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                WHERE tt.taxonomy = '{$tag_key}' AND tt.term_id IN ($placeholders)
            )", $deselected_post_tag_ids);
        }

        /**
         * Selected post author
         */
        if (!empty($post_author)) {
            $post_author_ids = explode(',', $post_author);
            $placeholders = implode(',', array_fill(0, count($post_author_ids), '%d'));
            $query .= $wpdb->prepare(" AND post_author IN ($placeholders)", $post_author_ids);
        }

        /**
         * Deselected post author
         */
        if (!empty($deselected_post_author)) {
            $deselected_post_author_ids = explode(',', $deselected_post_author);
            $placeholders = implode(',', array_fill(0, count($deselected_post_author_ids), '%d'));
            $query .= $wpdb->prepare(" AND post_author NOT IN ($placeholders)", $deselected_post_author_ids);
        }

        /**
         * Orderby and Sortorder
         */
        $valid_orderby_values = ['date', 'author', 'title', 'modified'];
        if (!in_array($orderby, $valid_orderby_values)) {
            $orderby = 'date';
        }

        /**
         * Sortorder
         */
        $valid_sortorder_values = ['asc', 'desc'];
        if (!in_array(strtolower($sortorder), $valid_sortorder_values)) {
            $sortorder = 'desc';
        }

        $sortorder = strtoupper($sortorder);

        $orderby = sanitize_key($orderby);

        switch ($orderby) {
            case 'author':
                $query .= " ORDER BY post_author $sortorder";
                break;
            case 'title':
                $query .= " ORDER BY post_title COLLATE utf8mb4_unicode_ci $sortorder";
                break;
            case 'modified':
                $query .= " ORDER BY post_modified $sortorder";
                break;
            case 'date':
            default:
                $query .= " ORDER BY post_date $sortorder";
                break;
        }

        /** Start Count total number of records */
        $count_query = "SELECT COUNT(*) FROM ($query) AS CountQuery";
        $total_records = $wpdb->get_var($count_query);

        /** Start Calculate total number of pages */
        $total_pages = ceil($total_records / $limit);

        $query_with_limit = $query . " LIMIT %d OFFSET %d";

        /** Start Prepare the final query */
        $final_query = $wpdb->prepare($query_with_limit, $limit, $offset);

        /** Start Execute the query to get results */
        $results = $wpdb->get_results($final_query);


        $get_custom_permalink = '';

        if ($results) {
            foreach($results as $key => $single_post):

                $permalink = get_permalink($single_post->ID);

                if ($custom_permalink_switcher === 'true') {
                    $relative_path = str_replace(home_url('/'), '', $permalink);
                    $get_custom_permalink = trailingslashit($custom_permalink) . $relative_path;
                } else {
                    $get_custom_permalink = $permalink;
                }

            ?>

                <?php if($premade_style == 'style-1'): ?>
                    <div class="fb_post_grid_wrapper fb_post_grid_<?php echo esc_attr($premade_style); ?>">
                        <div class="fb_post_grid_image_wrapper">
                            <div class="fb_post_grid_image">
                                <?php
                                    if (has_post_thumbnail($single_post->ID)) :
                                        $image = wp_get_attachment_image_src(get_post_thumbnail_id($single_post->ID), $post_image_size);
                                        $image_srcset = wp_get_attachment_image_srcset(get_post_thumbnail_id($single_post->ID), $post_image_size);
                                        $image_url = ($image && isset($image[0])) ? $image[0] : $default_image_url;
                                    else :
                                        $image_url = $default_image_url;
                                        $image_srcset = '';
                                    endif;
                                ?>

                                <?php if ($thumbnail_link_switcher == 'true'): ?>
                                    <a href="<?php echo esc_url($get_custom_permalink); ?>" aria-label="<?php esc_attr_e('Read more about', 'frontis-blocks'); ?> <?php echo esc_attr($single_post->post_title); ?>">
                                        <img src="<?php echo esc_url($image_url); ?>"
                                            srcset="<?php echo esc_attr($image_srcset); ?>"
                                            alt="<?php echo esc_attr(get_the_title($single_post->ID)); ?>"
                                        />
                                    </a>
                                <?php else: ?>
                                    <img src="<?php echo esc_url($image_url); ?>"
                                        srcset="<?php echo esc_attr($image_srcset); ?>"
                                        alt="<?php echo esc_attr(get_the_title($single_post->ID)); ?>"
                                    />
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="fb_post_grid_content_wrapper">
                            <?php if ($show_author_switcher == 'true'): ?>
                                <div class="fb_post_grid_author">
                                    <?php if ($post_author_avatar_switcher == 'true'): ?>
                                        <div class="fb_post_grid_author_avatar">
                                            <?php if ($author_link_switcher == 'true'): ?>
                                                <a class="fb_post_grid_author_avatar_url" href="<?php echo esc_url(get_author_posts_url($single_post->post_author)); ?>"
                                                    aria-label="<?php esc_attr_e('View all posts by', 'frontis-blocks'); ?> <?php echo esc_attr(get_the_author_meta('display_name', $single_post->post_author)); ?>">
                                                    <?php
                                                echo wp_kses_post(
                                                    get_avatar(
                                                        $single_post->post_author,
                                                        96,
                                                        '',
                                                        esc_attr(get_the_author_meta('display_name', $single_post->post_author)),
                                                        array('class' => 'avatar')
                                                    )
                                                ); ?>
                                            </a>
                                            <?php else: ?>
                                                <img src="<?php echo esc_url(get_avatar_url($single_post->post_author)); ?>" alt="<?php echo esc_attr(get_the_author_meta('display_name', $single_post->post_author)); ?>" />
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="fb_post_grid_author_info">
                                        <div class="fb_post_grid_author_info_name">
                                            <?php if ($post_author_prefix_switcher == 'true'): ?>
                                                <span class="fb_post_grid_author_posted_by"><?php echo esc_html($post_author_prefix); ?></span>
                                            <?php endif; ?>
                                            <div class="fb_post_grid_author_name">
                                                <?php if ($author_link_switcher == 'true'): ?>
                                                    <a class="fb_post_grid_author_name_url" href="<?php echo esc_url(get_author_posts_url($single_post->post_author)); ?>"
                                                aria-label="View all posts by <?php echo esc_attr(get_the_author_meta('display_name', $single_post->post_author)); ?>">
                                                    <?php echo esc_html(get_the_author_meta('display_name', $single_post->post_author)); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <?php echo esc_html(get_the_author_meta('display_name', $single_post->post_author)); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if ($show_date_switcher == 'true'): ?>
                                            <div class="fb_post_grid_date">
                                                <?php echo esc_html(get_the_date('j M Y', $single_post->ID)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($show_category_switcher == 'true'): ?>
                                <div class="fb_post_grid_categories">
                                    <?php
                                        $cat_tax   = $this->fb_resolve_category_tax_for_post( $single_post->ID );
                                        $categories = $cat_tax ? get_the_terms( $single_post->ID, $cat_tax ) : [];

                                        if ($categories):
                                            foreach ($categories as $category): ?>
                                                <div class="fb_post_grid_category" key="<?php echo esc_attr($category->term_id); ?>">
                                                    <?php if ( $category_link_switcher == 'true' ): ?>
                                                        <a class="fb_post_grid_category_url"
                                                        href="<?php echo esc_url( get_term_link( $category, $category->taxonomy ) ); ?>"
                                                        aria-label="<?php echo esc_attr( sprintf( 'View posts in %s category', $category->name ) ); ?>">
                                                        <?php echo esc_html( $category->name ); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="fb_post_grid_category_name"><?php echo esc_html( $category->name ); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach;
                                        endif;
                                    ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($show_tag_switcher == 'true'): ?>
                                <div class="fb_post_grid_tags">
                                    <?php
                                        $tag_tax = $this->fb_resolve_tag_tax_for_post( $single_post->ID );
                                        $tags    = $tag_tax ? get_the_terms( $single_post->ID, $tag_tax ) : [];
                                        if ( $tags ):
                                            foreach ( $tags as $tag ): ?>
                                                <div class="fb_post_grid_tag" key="<?php echo esc_attr( $tag->term_id ); ?>">
                                                    <?php if ( $tag_link_switcher == 'true' ): ?>
                                                        <a class="fb_post_grid_tag_url"
                                                        href="<?php echo esc_url( get_term_link( $tag, $tag->taxonomy ) ); ?>"
                                                        aria-label="<?php echo esc_attr( sprintf( 'View posts tagged with %s', $tag->name ) ); ?>">
                                                            <?php echo esc_html( $tag->name ); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="fb_post_grid_tag_name"><?php echo esc_html( $tag->name ); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach;
                                        endif;
                                    ?>
                                </div>
                            <?php endif; ?>

                            <div class="fb_post_grid_content">
                                <?php if ($title_link_switcher == 'true'): ?>
                                    <a href="<?php echo esc_url($get_custom_permalink); ?>">
                                        <<?php echo esc_html($post_title_tag); ?> class="fb_post_title">
                                        <?php echo esc_html($single_post->post_title); ?>
                                        </<?php echo esc_html($post_title_tag); ?>>
                                    </a>
                                <?php else: ?>
                                    <<?php echo esc_html($post_title_tag); ?> class="fb_post_title">
                                        <?php echo esc_html($single_post->post_title); ?>
                                    </<?php echo esc_html($post_title_tag); ?>>
                                <?php endif; ?>

                                <?php if (!empty($single_post->post_excerpt) && $show_excerpt_switcher == 'true'): ?>
                                    <<?php echo esc_html($post_description_tag); ?> class="fb_post_desc">
                                        <?php echo esc_html(wp_trim_words($single_post->post_excerpt, $post_description_excerpt_length, '...')); ?>
                                    </<?php echo esc_html($post_description_tag); ?>>
                                <?php elseif (!empty($single_post->post_content) && $show_excerpt_switcher == 'true'): ?>
                                    <<?php echo esc_html($post_description_tag); ?> class="fb_post_desc">
                                        <?php echo esc_html(wp_trim_words($single_post->post_content, $post_description_excerpt_length, '...')); ?>
                                    </<?php echo esc_html($post_description_tag); ?>>
                                <?php endif; ?>
                            </div>

                            <?php if ($read_more_switcher == 'true'): ?>
                                <a href="<?php echo esc_url($get_custom_permalink); ?>" class="fb_post_grid_read_more_button">

                                    <?php if ($read_more_icon_align !== 'icon-only'): ?>
                                        <span class="fb_post_grid_read_more_text"><?php echo esc_html($read_more_text); ?></span>
                                    <?php endif; ?>

                                    <?php if ($read_more_icon_align === 'icon-text'): ?>
                                        <div class='fb_post_grid_read_more_icon_wrapper'>

                                        </div>
                                    <?php endif; ?>

                                    <?php if ($read_more_icon_align === 'image' && $read_more_icon_image_url): ?>
                                        <div class='fb_post_grid_read_more_icon_wrapper'>
                                            <div class="fb_post_grid_read_more_icon_image">
                                                <img src="<?php echo esc_url($read_more_icon_image_url); ?>" alt="<?php echo esc_attr($read_more_icon_image_alt); ?>" />
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($read_more_icon_align === 'icon-only'): ?>
                                        <div class='fb_post_grid_read_more_icon_wrapper'>

                                        </div>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endif; ?>

                <?php if($premade_style == 'style-2'): ?>
                    <div class="fb_post_grid_wrapper fb_post_grid_<?php echo esc_attr($premade_style); ?>">
                        <div class="fb_post_grid_image_wrapper">
                            <div class="fb_post_grid_image">
                                <?php
                                    if (has_post_thumbnail($single_post->ID)) :
                                        $image = wp_get_attachment_image_src(get_post_thumbnail_id($single_post->ID), $post_image_size);
                                        $image_srcset = wp_get_attachment_image_srcset(get_post_thumbnail_id($single_post->ID), $post_image_size);
                                        $image_url = ($image && isset($image[0])) ? $image[0] : $default_image_url;
                                    else :
                                        $image_url = $default_image_url;
                                        $image_srcset = '';
                                    endif;
                                ?>

                                <?php if ($thumbnail_link_switcher == 'true'): ?>
                                    <a href="<?php echo esc_url($get_custom_permalink); ?>" aria-label="<?php esc_attr_e('Read more about', 'frontis-blocks'); ?> <?php echo esc_attr($single_post->post_title); ?>">
                                        <img src="<?php echo esc_url($image_url); ?>"
                                            srcset="<?php echo esc_attr($image_srcset); ?>"
                                            alt="<?php echo esc_attr(get_the_title($single_post->ID)); ?>"
                                        />
                                    </a>
                                <?php else: ?>
                                    <img src="<?php echo esc_url($image_url); ?>"
                                        srcset="<?php echo esc_attr($image_srcset); ?>"
                                        alt="<?php echo esc_attr(get_the_title($single_post->ID)); ?>"
                                    />
                                <?php endif; ?>

                                <?php if ($show_category_switcher == 'true'): ?>
                                    <div class="fb_post_grid_categories">
                                        <?php
                                            $cat_tax   = $this->fb_resolve_category_tax_for_post( $single_post->ID );
                                            $categories = $cat_tax ? get_the_terms( $single_post->ID, $cat_tax ) : [];
                                            if ($categories):
                                                foreach ($categories as $category): ?>
                                                    <div class="fb_post_grid_category" key="<?php echo esc_attr($category->term_id); ?>">
                                                        <?php if ( $category_link_switcher == 'true' ): ?>
                                                            <a class="fb_post_grid_category_url"
                                                            href="<?php echo esc_url( get_term_link( $category, $category->taxonomy ) ); ?>"
                                                            aria-label="<?php echo esc_attr( sprintf( 'View posts in %s category', $category->name ) ); ?>">
                                                            <?php echo esc_html( $category->name ); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="fb_post_grid_category_name"><?php echo esc_html( $category->name ); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach;
                                            endif;
                                        ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($show_tag_switcher == 'true'): ?>
                                    <div class="fb_post_grid_tags">
                                        <?php
                                            $tag_tax = $this->fb_resolve_tag_tax_for_post( $single_post->ID );
                                            $tags    = $tag_tax ? get_the_terms( $single_post->ID, $tag_tax ) : [];
                                            if ( $tags ):
                                                foreach ( $tags as $tag ): ?>
                                                    <div class="fb_post_grid_tag" key="<?php echo esc_attr( $tag->term_id ); ?>">
                                                        <?php if ( $tag_link_switcher == 'true' ): ?>
                                                            <a class="fb_post_grid_tag_url"
                                                            href="<?php echo esc_url( get_term_link( $tag, $tag->taxonomy ) ); ?>"
                                                            aria-label="<?php echo esc_attr( sprintf( 'View posts tagged with %s', $tag->name ) ); ?>">
                                                                <?php echo esc_html( $tag->name ); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="fb_post_grid_tag_name"><?php echo esc_html( $tag->name ); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach;
                                            endif;
                                        ?>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>

                        <div class="fb_post_grid_content_wrapper">
                            <?php if ($show_author_switcher == 'true'): ?>
                                <div class="fb_post_grid_author">
                                    <?php if ($post_author_avatar_switcher == 'true'): ?>
                                        <div class="fb_post_grid_author_avatar">
                                            <?php if ($author_link_switcher == 'true'): ?>
                                                <a class="fb_post_grid_author_avatar_url" href="<?php echo esc_url(get_author_posts_url($single_post->post_author)); ?>"
                                                aria-label="<?php esc_attr_e('View all posts by', 'frontis-blocks'); ?> <?php echo esc_attr(get_the_author_meta('display_name', $single_post->post_author)); ?>">
                                                <?php
                                                echo wp_kses_post(
                                                    get_avatar(
                                                        $single_post->post_author,
                                                        96,
                                                        '',
                                                        esc_attr(get_the_author_meta('display_name', $single_post->post_author)),
                                                        array('class' => 'avatar')
                                                    )
                                                ); ?>
                                            </a>
                                            <?php else: ?>
                                                <img src="<?php echo esc_url(get_avatar_url($single_post->post_author)); ?>" alt="<?php echo esc_attr(get_the_author_meta('display_name', $single_post->post_author)); ?>" />
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="fb_post_grid_author_info">
                                        <div class="fb_post_grid_author_info_name">
                                            <?php if ($post_author_prefix_switcher == 'true'): ?>
                                                <span class="fb_post_grid_author_posted_by"><?php echo esc_html($post_author_prefix); ?></span>
                                            <?php endif; ?>
                                            <div class="fb_post_grid_author_name">
                                                <?php if ($author_link_switcher == 'true'): ?>
                                                    <a class="fb_post_grid_author_name_url" href="<?php echo esc_url(get_author_posts_url($single_post->post_author)); ?>"
                                                aria-label="View all posts by <?php echo esc_attr(get_the_author_meta('display_name', $single_post->post_author)); ?>">
                                                    <?php echo esc_html(get_the_author_meta('display_name', $single_post->post_author)); ?>
                                                </a>
                                                <?php else: ?>
                                                    <?php echo esc_html(get_the_author_meta('display_name', $single_post->post_author)); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if ($show_date_switcher == 'true'): ?>
                                            <div class="fb_post_grid_date">
                                                <?php echo esc_html(get_the_date('j M Y', $single_post->ID)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="fb_post_grid_content">

                                <?php if ($title_link_switcher == 'true'): ?>
                                    <a href="<?php echo esc_url($get_custom_permalink); ?>">
                                    <<?php echo esc_html($post_title_tag); ?> class="fb_post_title">
                                        <?php echo esc_html($single_post->post_title); ?>
                                        </<?php echo esc_html($post_title_tag); ?>>
                                    </a>
                                <?php else: ?>
                                    <<?php echo esc_html($post_title_tag); ?> class="fb_post_title">
                                        <?php echo esc_html($single_post->post_title); ?>
                                    </<?php echo esc_html($post_title_tag); ?>>
                                <?php endif; ?>

                                <?php if (!empty($single_post->post_excerpt) && $show_excerpt_switcher == 'true'): ?>
                                    <<?php echo esc_html($post_description_tag); ?> class="fb_post_desc">
                                        <?php echo esc_html(wp_trim_words($single_post->post_excerpt, $post_description_excerpt_length, '...')); ?>
                                    </<?php echo esc_html($post_description_tag); ?>>
                                <?php elseif (!empty($single_post->post_content) && $show_excerpt_switcher == 'true'): ?>
                                    <<?php echo esc_html($post_description_tag); ?> class="fb_post_desc">
                                        <?php echo esc_html(wp_trim_words($single_post->post_content, $post_description_excerpt_length, '...')); ?>
                                    </<?php echo esc_html($post_description_tag); ?>>
                                <?php endif; ?>
                            </div>

                            <?php if ($read_more_switcher == 'true'): ?>
                                <a href="<?php echo esc_url($get_custom_permalink); ?>" class="fb_post_grid_read_more_button">

                                    <?php if ($read_more_icon_align !== 'icon-only'): ?>
                                        <span class="fb_post_grid_read_more_text"><?php echo esc_html($read_more_text); ?></span>
                                    <?php endif; ?>

                                    <?php if ($read_more_icon_align === 'icon-text'): ?>
                                        <div class='fb_post_grid_read_more_icon_wrapper'>

                                        </div>
                                    <?php endif; ?>

                                    <?php if ($read_more_icon_align === 'image' && $read_more_icon_image_url): ?>
                                        <div class='fb_post_grid_read_more_icon_wrapper'>
                                            <div class="fb_post_grid_read_more_icon_image">
                                                <img src="<?php echo esc_url($read_more_icon_image_url); ?>" alt="<?php echo esc_attr($read_more_icon_image_alt); ?>" />
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($read_more_icon_align === 'icon-only'): ?>
                                        <div class='fb_post_grid_read_more_icon_wrapper'>

                                        </div>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach;

            $has_more = (int)$current_page < (int)$total_pages;

            // show load more button or pagination
            if ($show_load_more_switcher == 'true') :

                // 1) LOAD MORE BUTTON: show when there is more page
                if ($load_more_type === 'button' && $has_more) : ?>
                    <div class="fb_load_more_wrapper">
                        <div class="fb_load_more"
                            data-current_page="<?php echo esc_attr($current_page); ?>"
                            data-total_pages="<?php echo esc_attr($total_pages); ?>">
                        </div>
                    </div>
                <?php endif;

                // 2) PAGINATION: show when total_pages > 1 (also on last page)
                if ($load_more_type === 'pagination' && (int)$total_pages > 1) : ?>
                    <div class="fb_load_more_wrapper">
                        <div class="fb_pagination_wrapper"
                            data-current_page="<?php echo esc_attr($current_page); ?>"
                            data-total_pages="<?php echo esc_attr($total_pages); ?>">

                            <!-- Previous Button -->
                            <button class="fb_pagination_previous <?php echo $current_page==1 ? 'disabled' : ''; ?>">
                                <?php if ($pagination_prev_next_type === 'icon'): ?>
                                    <span class="fb_pagination_previous_icon_Wrapper"></span>
                                <?php else: ?>
                                    <span class="fb_pagination_previous_text"><?php echo esc_html( $pagination_prev_text ); ?></span>
                                <?php endif; ?>
                            </button>

                            <?php
                            $range = 2; // current page before and after how many pages
                            echo '<div class="fb_pagination">';

                            // if there is only one page, the list will not be shown, but the wrapper will still be there
                            if ($total_pages > 1) {

                                // start dots
                                if ($current_page > ($range + 2)) {
                                    echo '<button class="fb_pagination_item" data-pagenumber="1">1</button>';
                                    echo '<span class="fb_pagination_ellipsis">...</span>';
                                }

                                // main page numbers
                                $start = max(1, $current_page - $range);
                                $end   = min($total_pages, $current_page + $range);

                                for ($page = $start; $page <= $end; $page++) {
                                    $active = $page == $current_page ? 'active' : '';
                                    echo '<button class="fb_pagination_item ' . esc_attr($active) . '" data-pagenumber="' . esc_attr($page) . '">' . esc_html($page) . '</button>';
                                }

                                // end dots
                                if ($current_page < $total_pages - ($range + 2)) {
                                    echo '<span class="fb_pagination_ellipsis">...</span>';
                                    echo '<button class="fb_pagination_item" data-pagenumber="' . $total_pages . '">' . $total_pages . '</button>';
                                }
                            }
                            echo '</div>';
                            ?>

                            <!-- Next Button -->
                            <button class="fb_pagination_next <?php echo $current_page==$total_pages ? 'disabled' : ''; ?>">
                                <?php if ($pagination_prev_next_type === 'icon'): ?>
                                    <span class="fb_pagination_next_icon_Wrapper"></span>
                                <?php else: ?>
                                    <span class="fb_pagination_next_text"><?php echo esc_html( $pagination_next_text ); ?></span>
                                <?php endif; ?>
                            </button>

                        </div>
                    </div>
                <?php endif;

            endif; // $show_load_more_switcher
        } else {
            if ((int)$offset === 0) {
                echo '<div class="empty-post">Nothing found.</div>';
            }
        }

        $content = ob_get_contents();
        ob_end_clean();

        /**
         * Send response with content and total pages
         */
        wp_send_json_success(array(
            'content'      => $content,
            'total_pages'  => (int)$total_pages,
            'current_page' => (int)$current_page,
            'has_more'     => ((int)$current_page < (int)$total_pages),
        ));
    }
}