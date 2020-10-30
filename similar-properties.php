<?php
global $inspiry_options;
global $inspiry_single_property;

$number_of_similar_properties = intval( $inspiry_options[ 'inspiry_similar_properties_number' ] );
if ( empty( $number_of_similar_properties ) ) {
    $number_of_similar_properties = 3;
}

$similar_properties_args = array(
	
    'post_type' => 'property',
    'posts_per_page' => $number_of_similar_properties,
//added there parameter lang	
	'lang' => 'lv',
    'post__not_in' => array( $inspiry_single_property->get_post_ID() ),
    'post_parent__not_in' => array( $inspiry_single_property->get_post_ID() ),    // to avoid child posts from appearing in similar properties
);

$similar_properties_sorty_by = $inspiry_options['inspiry_similar_properties_sorting'];
if ( ! empty( $similar_properties_sorty_by ) ) {
    if ( 'property-lth' == $similar_properties_sorty_by ) {
        $similar_properties_args['orderby']   = 'meta_value_num';
        $similar_properties_args['meta_key']  = 'REAL_HOMES_property_price';
        $similar_properties_args['order']     = 'ASC';
    } elseif ( 'price-htl' == $similar_properties_sorty_by ) {
        $similar_properties_args['orderby']   = 'meta_value_num';
        $similar_properties_args['meta_key']  = 'REAL_HOMES_property_price';
        $similar_properties_args['order']     = 'DESC';
    } elseif ( 'random' == $similar_properties_sorty_by ) {
        $similar_properties_args['orderby']   = 'rand';
    }
}


$tax_query = array();

// Main Post Property Type

if ( '1' == $inspiry_options['inspiry_similar_properties_filter']['property-type'] ) {
    $type_terms = get_the_terms( $inspiry_single_property->get_post_ID(), "property-type" );
    if ( ! empty( $type_terms ) && is_array( $type_terms ) ) {
        $types_array = array();
        foreach ( $type_terms as $type_term ) {
            $types_array[] = $type_term->term_id;
        }
        $tax_query[] = array(
            'taxonomy' => 'property-type',
            'field'    => 'id',
            'terms'    => $types_array
        );
    }
}

if ( '1' == $inspiry_options['inspiry_similar_properties_filter']['property-feature'] ) {
    $type_terms = get_the_terms( $inspiry_single_property->get_post_ID(), "property-feature" );
    if ( ! empty( $type_terms ) && is_array( $type_terms ) ) {
        $types_array = array();
        foreach ( $type_terms as $type_term ) {
            $types_array[] = $type_term->term_id;
        }
        $tax_query[] = array(
            'taxonomy' => 'property-feature',
            'field'    => 'id',
            'terms'    => $types_array
        );
    }
}

if ( '1' == $inspiry_options['inspiry_similar_properties_filter']['property-city'] ) {
    $type_terms = get_the_terms( $inspiry_single_property->get_post_ID(), "property-city" );
    if ( ! empty( $type_terms ) && is_array( $type_terms ) ) {
        $types_array = array();
        foreach ( $type_terms as $type_term ) {
            $types_array[] = $type_term->term_id;
        }
        $tax_query[] = array(
            'taxonomy' => 'property-city',
            'field'    => 'id',
            'terms'    => $types_array
        );
    }
}

if ( '1' == $inspiry_options['inspiry_similar_properties_filter']['property-status'] ) {
    $status_terms = get_the_terms( $inspiry_single_property->get_post_ID(), "property-status" );
    if ( ! empty( $status_terms ) && is_array( $status_terms ) ) {
        $statuses_array = array();
        foreach ( $status_terms as $status_term ) {
            $statuses_array[] = $status_term->term_id;
        }
        $tax_query[] = array(
            'taxonomy' => 'property-status',
            'field'    => 'id',
            'terms'    => $statuses_array
        );
    }
}



$tax_count = count( $tax_query );   // count number of taxonomies
if ( $tax_count > 1 ) {
    $tax_query['relation'] = 'OR';  // add OR relation if more than one
}

if ( $tax_count > 0 ) {
    $similar_properties_args['tax_query'] = $tax_query;   // add taxonomies query
}
 
 $tax_query[] = array(
            'taxonomy' => 'language',
            'field'    => 'id',
            'terms'    => '2'
			);
 //echo "<script>console.log(".$GLOBALS['wp_query']->request.");</script>";


//the query

$similar_properties_query = new WP_Query( $similar_properties_args );




//echo "<script>console.log(".$similar_properties_query->request.");</script>";

if ( $similar_properties_query->have_posts() ) :
    ?>
    <section class="similar-properties meta-item-half clearfix">
        <div class="nav-and-title clearfix">
            <?php
            if ( !empty( $inspiry_options['inspiry_similar_properties_title'] ) ) {
                ?><h3 class="title"><?php echo wp_kses( $inspiry_options['inspiry_similar_properties_title'], array( 'span' => array() ) ); ?></h3><?php
            }
            ?>
            <div class="similar-properties-carousel-nav carousel-nav">
                <a class="carousel-prev-item prev"><?php include( get_template_directory() . '/images/svg/arrow-left.svg' ); ?></a>
                <a class="carousel-next-item next"><?php include( get_template_directory() . '/images/svg/arrow-right.svg' ); ?></a>
            </div>
        </div>

        <div class="similar-properties-carousel">
            <div class="owl-carousel">
                <?php
                while ( $similar_properties_query->have_posts() ) :
                    $similar_properties_query->the_post();
                    $similar_property = new Inspiry_Property( $post->ID );
                    ?>
                    <article class="hentry clearfix">

                        <figure class="property-thumbnail">
                            <?php inspiry_thumbnail( 'post-thumbnail' ); ?>
                        </figure>

                        <div class="property-description">
                            <div class="arrow"></div>
                            <header class="entry-header">
                                <h3 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h3>
                                <div class="price-and-status">
                                    <span class="price"><?php $similar_property->price(); ?></span><?php
                                    $first_status_term = $similar_property->get_taxonomy_first_term( 'property-status', 'all' );
                                    if ( $first_status_term ) {
                                        ?><a href="<?php echo esc_url( get_term_link( $first_status_term ) ); ?>"><span class="property-status-tag"><?php echo esc_html( $first_status_term->name ); ?></span></a><?php
                                    }
                                    ?>
                                </div>
                            </header>
                            <?php inspiry_property_meta( $similar_property, array( 'meta' => array( 'area', 'rooms', 'beds', 'baths', 'garages', 'type' ) ) ); ?>
                        </div>

                    </article>
                    <?php
                endwhile;
                ?>
            </div>
        </div>
    </section>
    <?php
endif;

wp_reset_postdata();
