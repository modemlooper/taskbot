<?php $query = new WP_Query( bp_docs_loop_filter() ); ?>

<div class="media no-ajax" role="main">

	<ul id="activity-stream" class="item-list">

		<?php if ( $query->have_posts() ) : ?>

			<?php while ( $query->have_posts() ) : $query->the_post(); ?>

				<?php bp_docs_get_template_part( 'document-index' ); ?>

			<?php endwhile; ?>


		<?php endif; ?>

	</ul>


</div>


<div id="pag-bottom" class="pagination">

	<div class="pag-count" id="group-dir-count-bottom">

		<?php bp_docs_pagination_count( $query ); ?>

	</div>

	<div class="pag-links" id="group-dir-pag-bottom">

		<?php bp_docs_pagination_links( $query ); ?>

	</div>

</div>
