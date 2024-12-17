<?php get_header(); ?>
<main>
    <h1><?php the_archive_title(); ?></h1>
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <?php endwhile; else: ?>
        <p><?php _e('No posts found.', 'my-elementor-theme'); ?></p>
    <?php endif; ?>
</main>
<?php get_footer(); ?>