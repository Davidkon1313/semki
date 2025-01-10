<?php get_header(); ?>

<main class="wrap">

  <section class="content-area content-full-width">

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <div class="post__section">
          <div class="post">
            <?php if (has_post_thumbnail()) : ?>
              <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>" alt="<?php the_title_attribute(); ?>">
            <?php endif; ?>
            <p><?php the_title(); ?></p>
            <span><?php the_content(); ?></span>
          </div>

        </div>
      <?php endwhile;
    else : ?>
      <article>
        <p>Sorry, no post was found!</p>
      </article>
    <?php endif; ?>
  </section>
</main>

<?php get_footer(); ?>