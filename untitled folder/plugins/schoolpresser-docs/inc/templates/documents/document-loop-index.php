<div class="media no-ajax" role="main">

	<ul id="activity-stream" class="item-list">

		<?php if ( have_posts() ) : ?>

			<?php while ( have_posts() ) :  the_post(); ?>
				<?php bp_docs_get_template_part( 'document-index' ); ?>
			<?php endwhile; ?>

		<?php else : ?>

		<p class="no-content">There have been no documents added or you have not added any documents.</p>

		<?php endif; ?>

	</ul>


</div>

<!-- <?php global $wp_query; ?>

<div id="pag-bottom" class="pagination">

	<div class="pag-count" id="group-dir-count-bottom">

		<?php bp_docs_pagination_count( $wp_query ); ?>

	</div>

	<div class="pag-links" id="group-dir-pag-bottom">

		<?php bp_docs_pagination_links( $wp_query ); ?>

	</div>

</div> -->

<script>
(function($) {
    $(function() {
		//FWP.reset();
    });
})(jQuery);
</script>
