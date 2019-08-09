<?php global $adforest_theme; ?>
<?php
	$layout	= 'list_2';
	if( isset( $adforest_theme['search_layout'] ) && $adforest_theme['search_layout'] != "" )
	{
		$layout = $adforest_theme['search_layout'];
	}
	
	$mapType = adforest_mapType();
	
	if( isset( $type ) )
	{
		$layout = $type;
	}

	$out = '<div class="posts-masonry">
		   <div class="col-md-12 col-xs-12 col-sm-12">';
	

        // The Loop
		$marker_counter	=	1;
        while ( $results->have_posts() )
        {
            $results->the_post();
            $pid	=	get_the_ID();
			
			$out .=  ai_adforest_search_layout_list($pid);
			
			
			
			

        }

	$out .=	'</div></div>';

?>