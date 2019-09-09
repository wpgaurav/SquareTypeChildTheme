<?php
/**
 * Post Meta Helper Functions
 *
 * These helper functions return post meta, if its enabled in WordPress Customizer.
 *
 * @package Squaretype
 */

if ( ! function_exists( 'csco_get_post_meta' ) ) {
	/**
	 * Post Meta
	 *
	 * A wrapper function that returns all post meta types either
	 * in an ordered list <ul> or as a single element <span>.
	 *
	 * @param mixed $meta    Contains post meta types.
	 * @param bool  $compact If compact version shall be displayed.
	 * @param bool  $echo    Echo or return.
	 * @param mixed $allowed Allowed meta types (array: list types, true: auto definition, option name: get value of option).
	 */
	function csco_get_post_meta( $meta, $compact = false, $echo = true, $allowed = null ) {

		// Return if no post meta types provided.
		if ( ! $meta ) {
			return;
		}

		if ( is_string( $allowed ) || true === $allowed ) {
			$option_default = null;

			$option_name = is_string( $allowed ) ? $allowed : csco_get_archive_option( 'post_meta' );

			if ( class_exists( 'Kirki' ) && isset( Kirki::$fields[ $option_name ]['default'] ) ) {
				$option_default = Kirki::$fields[ $option_name ]['default'];
			} elseif ( isset( CSCO_Kirki::$fields[ $option_name ]['default'] ) ) {
				$option_default = CSCO_Kirki::$fields[ $option_name ]['default'];
			}

			$allowed = get_theme_mod( $option_name, $option_default );
		}

		if ( ! is_array( $allowed ) && ! $allowed ) {
			// Set default allowed post meta types.
			$allowed = apply_filters( 'csco_post_meta', array( 'date', 'category', 'comments', 'shares', 'views', 'reading_time', 'author' ) );
		}

		if ( is_array( $meta ) ) {
			// Intersect provided and allowed meta types.
			$meta = array_intersect( $meta, $allowed );
		}

		$output = null;

		if ( $meta && is_array( $meta ) ) {

			$output .= '<ul class="post-meta">';

			// Add normal meta types to the list.
			foreach ( $meta as $type ) {
				$function = "csco_get_meta_$type";
				$output  .= $function( 'li', $compact );
			}

			$output .= '</ul>';

		} else {
			if ( in_array( $meta, $allowed, true ) ) {
				// Output single meta type.
				$function = "csco_get_meta_$meta";
				$output  .= $function( 'div', $compact );
			}
		}

		if ( $echo ) {
			echo (string) $output; // XSS ok.
		} else {
			return $output;
		}
	}
}

if ( ! function_exists( 'csco_get_top_category' ) ) {
	/**
	 * Gets the top level category
	 *
	 * @param int $post_id Post ID.
	 */
	function csco_get_top_category( $post_id ) {

		$terms = wp_get_object_terms( $post_id, 'category', array(
			'orderby' => 'term_id',
		) );

		if ( ! $terms || is_wp_error( $terms ) ) {
			return $terms;
		}

		$term = array_shift( $terms );

		// Yoast SEO primary category.
		if ( class_exists( 'WPSEO_Primary_Term' ) ) {

			$wpseo_primary_term = new WPSEO_Primary_Term( 'category', $post_id );

			$wpseo_object_term = get_term( $wpseo_primary_term->get_primary_term() );

			if ( ! is_wp_error( $wpseo_object_term ) ) {
				$term = $wpseo_object_term;
			}
		}

		return $term;
	}
}

if ( ! function_exists( 'csco_get_meta_category' ) ) {
	/**
	 * Post Сategory
	 *
	 * @param string $tag     Element tag, i.e. div or span.
	 * @param bool   $compact If compact version shall be displayed.
	 * @param int    $post_id Post ID.
	 */
	function csco_get_meta_category( $tag = 'span', $compact = false, $post_id = null ) {

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		$category = csco_get_top_category( $post_id );

		if ( $category ) {

			$output = '<' . esc_html( $tag ) . ' class="meta-category">';

			$link = get_category_link( $category->term_id );

			$color = get_term_meta( $category->term_id, 'csco_brand_color', true );

			// Get first char.
			$first_char = get_query_var( 'csco_category_first_char' );

			if ( ! $first_char ) {
				if ( function_exists( 'mb_substr' ) ) {
					$first_char = mb_substr( $category->name, 0, 1 );
				} else {
					$first_char = substr( $category->name, 0, 1 );
				}
			}

			$attrs = null;

			// Set attrs.
			if ( $color ) {
				$attrs .= sprintf( 'style="background:%s"', $color );
			}

			// Set scheme.
			$scheme = csco_light_or_dark( $color, null, 'cs-bg-dark' );

			// Set category caption.
			$caption = sprintf( '<span %s class="char %s">%s</span><span class="label">%s</span>', $attrs, $scheme, $first_char, $category->name );

			// Add category to output.
			$output .= sprintf( '<a class="category-style" href="%s">%s</a>', esc_url( $link ), wp_kses( $caption, 'post' ) );

			$output .= '</' . esc_html( $tag ) . '>';

			return $output;
		}
	}
}

if ( ! function_exists( 'csco_get_meta_date' ) ) {
	/**
	 * Post Date
	 *
	 * @param string $tag     Element tag, i.e. div or span.
	 * @param bool   $compact If compact version shall be displayed.
	 */
	function csco_get_meta_date( $tag = 'span', $compact = false ) {

		$output = '<' . esc_html( $tag ) . ' class="meta-date">';

		if ( false === $compact ) {
			$time_string = get_the_date();
		} else {
			$time_string = get_the_date( 'd.m.y' );
		}

		if ( get_the_time( 'd.m.Y H:i' ) !== get_the_modified_time( 'd.m.Y H:i' ) ) {
			if ( ! get_theme_mod( 'misc_published_date', true ) ) {
				$time_string = get_the_modified_date();
			}
		}

		$output .= apply_filters( 'csco_post_meta_date_output', $time_string );

		$output .= '</' . esc_html( $tag ) . '>';

		return $output;
	}
}

if ( ! function_exists( 'csco_get_meta_author' ) ) {
	/**
	 * Post Author
	 *
	 * @param string $tag     Element tag, i.e. div or span.
	 * @param bool   $compact If compact version shall be displayed.
	 */
	function csco_get_meta_author( $tag = 'span', $compact = true ) {

		$authors = array( get_the_author_meta( 'ID' ) );

		$output = '<' . esc_attr( $tag ) . ' class="meta-author"><span class="by">' . esc_html__( 'by ', 'squaretype' ) . '</span>';

		if ( csco_coauthors_enabled() ) {
			$authors = csco_get_coauthors();
		}

		if ( $authors ) {

			$counter = 0;

			foreach ( $authors as & $author ) {

				$output .= $counter > 0 ? sprintf( '<span class="sep">%s</span>', esc_html__( 'and', 'squaretype' ) ) : '';

				$author_id    = isset( $author->ID ) ? $author->ID : $author;
				$display_name = isset( $author->display_name ) ? $author->display_name : get_the_author_meta( 'display_name', $author_id );
				$posts_url    = get_author_posts_url( $author_id, isset( $author->user_nicename ) ? $author->user_nicename : '' );

				$output .= sprintf( '<span class="author"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
					esc_url( $posts_url ),
					/* translators: %s: author name */
					esc_attr( sprintf( __( 'View all posts by %s', 'squaretype' ), $display_name ) ),
					$display_name
				);

				$counter++;
			}
		}

		$output .= '</' . esc_html( $tag ) . '>';

		return $output;

	}
}

if ( ! function_exists( 'csco_get_meta_comments' ) ) {
	/**
	 * Post Comments
	 *
	 * @param string $tag     Element tag, i.e. div or span.
	 * @param bool   $compact If compact version shall be displayed.
	 */
	function csco_get_meta_comments( $tag = 'span', $compact = false ) {

		if ( ! comments_open( get_the_ID() ) ) {
			return;
		}

		$output  = '<' . esc_html( $tag ) . ' class="meta-comments">';
		$output .= '<i class="cs-icon cs-icon-message-square"></i>';

		if ( true === $compact ) {
			ob_start();
			comments_popup_link( '0', '1', '%', 'comments-link', '' );
			$output .= ob_get_clean();
		} else {
			ob_start();
			comments_popup_link( esc_html__( 'No comments', 'squaretype' ), esc_html__( 'One comment', 'squaretype' ), '% ' . esc_html__( 'comments', 'squaretype' ), 'comments-link', '' );
			$output .= ob_get_clean();
		}

		$output .= '</' . esc_html( $tag ) . '>';

		return $output;
	}
}

if ( ! function_exists( 'csco_get_meta_reading_time' ) ) {
	/**
	 * Post Reading Time
	 *
	 * @param string $tag     Element tag, i.e. div or span.
	 * @param bool   $compact If compact version shall be displayed.
	 */
	function csco_get_meta_reading_time( $tag = 'span', $compact = false ) {

		$reading_time = csco_get_post_reading_time();

		$output  = '<' . esc_html( $tag ) . ' class="meta-reading-time">';
		$output .= '<i class="cs-icon cs-icon-clock"></i>';

		if ( true === $compact ) {
			$output .= intval( $reading_time ) . ' ' . esc_html__( 'min', 'squaretype' );
		} else {
			/* translators: %s number of minutes */
			$output .= esc_html( sprintf( _n( '%s minute read', '%s minute read', $reading_time, 'squaretype' ), $reading_time ) );
		}

		$output .= '</' . esc_html( $tag ) . '>';

		return $output;
	}
}

if ( ! function_exists( 'csco_get_meta_views' ) ) {
	/**
	 * Post Views
	 *
	 * @param string $tag     Element tag, i.e. div or span.
	 * @param bool   $compact If compact version shall be displayed.
	 */
	function csco_get_meta_views( $tag = 'span', $compact = false ) {

		if ( ! class_exists( 'Post_Views_Counter' ) ) {
			return;
		}

		$views = pvc_get_post_views();

		// Don't display if minimum threshold is not met.
		if ( $views < apply_filters( 'csco_minimum_views', 1 ) ) {
			return;
		}

		$output  = '<' . esc_html( $tag ) . ' class="meta-views">';
		$output .= '<i class="cs-icon cs-icon-activity"></i>';

		$views_rounded = csco_get_round_number( $views );

		if ( true === $compact ) {
			$output .= esc_html( $views_rounded );
		} else {
			if ( $views > 1000 ) {
				$output .= $views_rounded . ' ' . esc_html__( 'views', 'squaretype' );
			} else {
				/* translators: %s number of post views */
				$output .= esc_html( sprintf( _n( '%s view', '%s views', $views, 'squaretype' ), $views ) );
			}
		}

		$output .= '</' . esc_html( $tag ) . '>';

		return $output;

	}
}

if ( ! function_exists( 'csco_get_meta_shares' ) ) {
	/**
	 * Post Shares
	 *
	 * @param string $tag     Element tag, i.e. div or span.
	 * @param bool   $compact If compact version shall be displayed.
	 */
	function csco_get_meta_shares( $tag = 'span', $compact = false ) {

		if ( ! csco_powerkit_module_enabled( 'share_buttons' ) ) {
			return;
		}

		if ( ! get_option( 'powerkit_share_buttons_post_meta_display' ) ) {
			return;
		}

		$output = '<' . esc_html( $tag ) . ' class="meta-shares">';

		$accounts = get_option( 'powerkit_share_buttons_post_meta_multiple_list', array( 'facebook', 'twitter', 'pinterest' ) );

		// Share Count.
		$shares = powerkit_share_buttons_get_total_count( $accounts, get_the_ID(), null, true );

		$shares_rounded = powerkit_share_buttons_count_format( $shares );

		// Don't display if minimum threshold is not met.
		if ( $shares < apply_filters( 'csco_minimum_shares', 1 ) ) {
			return;
		}

		ob_start();
		?>
			<span class="total">
				<i class="cs-icon cs-icon-share"></i>
				<span class="total-number">
					<?php
					if ( true === $compact ) {
						echo esc_html( $shares_rounded );
					} else {
						if ( $shares > 1000 ) {
							echo esc_html( $shares_rounded ) . ' ' . esc_html__( 'shares', 'squaretype' );
						} else {
							/* translators: %s number of post views */
							echo esc_html( sprintf( _n( '%s share', '%s shares', $shares, 'squaretype' ), $shares ) );
						}
					}
					?>
				</span>
			</span>
			<div class="meta-share-links">
				<?php
					powerkit_share_buttons_location( 'post_meta' );
				?>
			</div>
		<?php

		$output .= ob_get_clean();

		$output .= '</' . esc_html( $tag ) . '>';

		return $output;

	}
}

if ( ! function_exists( 'csco_calculate_post_reading_time' ) ) {
	/**
	 * Calculate Post Reading Time in Minutes
	 *
	 * @param int $post_id The post ID.
	 */
	function csco_calculate_post_reading_time( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		$post_content     = get_post_field( 'post_content', $post_id );
		$strip_shortcodes = strip_shortcodes( $post_content );
		$strip_tags       = strip_tags( $strip_shortcodes );
		$locale           = csco_get_locale();
		if ( 'ru_RU' === $locale ) {
			$word_count = count( preg_split( '/\s+/', $strip_tags ) );
		} else {
			$word_count = str_word_count( $strip_tags );
		}
		$reading_time = intval( ceil( $word_count / 250 ) );
		return $reading_time;
	}
}

/**
 * Update Post Reading Time on Post Save
 *
 * @param int  $post_id The post ID.
 * @param post $post    The post object.
 * @param bool $update  Whether this is an existing post being updated or not.
 */
function csco_update_post_reading_time( $post_id, $post, $update ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}
	$reading_time = csco_calculate_post_reading_time( $post_id );
	update_post_meta( $post_id, '_csco_reading_time', $reading_time );
}

add_action( 'save_post', 'csco_update_post_reading_time', 10, 3 );

/**
 * Get Post Reading Time from Post Meta
 *
 * @param int $post_id The post ID.
 */
function csco_get_post_reading_time( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}
	// Get existing post meta.
	$reading_time = get_post_meta( $post_id, '_csco_reading_time', true );
	// Calculate and save reading time, if there's no existing post meta.
	if ( ! $reading_time ) {
		$reading_time = csco_calculate_post_reading_time( $post_id );
		update_post_meta( $post_id, '_csco_reading_time', $reading_time );
	}
	return $reading_time;
}
