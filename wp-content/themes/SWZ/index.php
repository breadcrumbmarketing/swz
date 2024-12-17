<?php get_header(); ?>
<main>
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <h2><?php the_title(); ?></h2>
        <?php the_content(); ?>
    <?php endwhile; else: ?>
        <p><?php _e('Sorry, no posts matched your criteria.', 'my-elementor-theme'); ?></p>
    <?php endif; ?>
</main>
<?php get_footer(); ?>