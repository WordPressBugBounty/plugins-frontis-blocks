<?php

namespace FrontisBlocks\Frontend\Blocks;

use FrontisBlocks\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LoopBuilder {
	use Singleton;
	public static function render_block( $content, $parsed_block, $block_obj )
	{
		// Validate and sanitize query parameters
		$attributes = $block_obj->attributes;

		if(!isset($attributes['query'])) {
			return $content;
		}

		$query_args = array(
			'post_type'      => sanitize_text_field($attributes['postType'] ?? 'post'),
			'posts_per_page' => absint($attributes['postPerPage'] ?? 3),
			'offset'         => absint($attributes['postOffset'] ?? 0),
			'orderby'        => sanitize_text_field($attributes['orderBy'] ?? 'date'),
			'order'          => strtoupper(sanitize_text_field($attributes['sortOrder'] ?? 'DESC')),
			'post_status'    => 'publish',
		);

		// Validate order parameter
		if (!in_array($query_args['order'], ['ASC', 'DESC'])) {
			$query_args['order'] = 'DESC';
		}

		// Handle category filters
		if (!empty($attributes['selectedCatSwitcher']) && is_string($attributes['selectedCatSwitcher'])) {
			$selected_cats = array_map('absint', (array)($attributes['selectedCat'] ?? []));
			$deselected_cats = array_map('absint', (array)($attributes['deSelectedCat'] ?? []));

			if ($attributes['query']['selectedCatSwitcher'] === 'include' && !empty($selected_cats)) {
				$query_args['query']['category__in'] = $selected_cats;
			} elseif ($attributes['query']['selectedCatSwitcher'] === 'exclude' && !empty($deselected_cats)) {
				$query_args['query']['category__not_in'] = $deselected_cats;
			}
		}

		// Handle tag filters
		if (!empty($attributes['selectedTagSwitcher']) && is_string($attributes['selectedTagSwitcher'])) {
			$selected_tags = array_map('absint', (array)($attributes['selectedTag'] ?? []));
			$deselected_tags = array_map('absint', (array)($attributes['deSelectedTag'] ?? []));

			if ($attributes['selectedTagSwitcher'] === 'include' && !empty($selected_tags)) {
				$query_args['tag__in'] = $selected_tags;
			} elseif ($attributes['selectedTagSwitcher'] === 'exclude' && !empty($deselected_tags)) {
				$query_args['tag__not_in'] = $deselected_tags;
			}
		}

		// Handle author filters
		if (!empty($attributes['selectedAuthorSwitcher']) && is_string($attributes['selectedAuthorSwitcher'])) {
			$selected_authors = array_map('absint', (array)($attributes['selectedAuthor'] ?? []));
			$deselected_authors = array_map('absint', (array)($attributes['deSelectedAuthor'] ?? []));

			if ($attributes['selectedAuthorSwitcher'] === 'include' && !empty($selected_authors)) {
				$query_args['author__in'] = $selected_authors;
			} elseif ($attributes['selectedAuthorSwitcher'] === 'exclude' && !empty($deselected_authors)) {
				$query_args['author__not_in'] = $deselected_authors;
			}
		}

		// Create WP_Query
		$query = new \WP_Query($query_args);

		// Sanitize tag name
		$tag_name = esc_attr(sanitize_text_field($attributes['tagName'] ?? 'div'));
		$block_id = esc_attr(sanitize_text_field($attributes['blockId'] ?? ''));

		// Build classes array
		$classes = array('fb-query-content-container');
		if ($block_id) {
			$classes[] = $block_id;
		}

		// Return early if no posts
		if (!$query->have_posts()) {
			return sprintf(
				'<%s class="%s"><p>%s</p></%s>',
				$tag_name,
				esc_attr(implode(' ', $classes)),
				esc_html__('No posts found.', 'textdomain'),
				$tag_name
			);
		}

//
//		// Start output buffering
//		ob_start();
//
//		printf('<%s class="%s">', $tag_name, esc_attr(implode(' ', $classes)));

		// Process inner blocks with query context
		while ($query->have_posts()) {
			$query->the_post();

//			echo "<pre>";
//			var_dump(count($query->posts));
//			echo "</pre>";

			// Set up post context for inner blocks
			$post_context = array(
				'postId'    => get_the_ID(),
				'postType'  => get_post_type(),
				'classList' => get_post_class('fb-loop-builder-wrapper-block-context-container')
			);

			$content .= do_blocks($content);
		}

//		printf('</%s>', $tag_name);

		// Reset post data
		wp_reset_postdata();

		return $content;
	}

	public static function render_inner_blocks_with_context( $content, $post_context ) {
		// Validate content
		if (empty($content) || !is_string($content)) {
			return '';
		}

		global $post;
		$original_post = $post;

		// Setup post data if context has valid post ID
		if (!empty($context['postId']) && is_numeric($context['postId'])) {
			$post = get_post(absint($context['postId']));
			if ($post) {
				setup_postdata($post);
			}
		}

		// Process the inner block content
		$processed_content = do_blocks($content);

		// Restore original post
		if ($original_post) {
			$post = $original_post;
			setup_postdata($original_post);
		} else {
			wp_reset_postdata();
		}

		return $processed_content;
	}
}
