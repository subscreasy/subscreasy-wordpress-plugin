<?php
/**
 * HTML output after a successful subscription
 */

get_header(); ?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">

            <h1><?php _e('Thank you for your subscription!', 'subscreasy'); ?></h1>

        </main><!-- #main -->
    </div><!-- #primary -->

<?php
get_sidebar();
get_footer();
