<?php
/**
 * HTML output after a successful subscription
 */

$user = wp_get_current_user();

$secureID = $_REQUEST['subscriberSecureId'];

$updated = update_user_meta($user->ID, 'user_secureID', $secureID);


get_header(); ?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
            <article class="post hentry entry">
                <header class="entry-header">
                    <h1><?php _e('Thank you for your subscription!', 'subscreasy'); ?></h1>
                </header>
            </article>

        </main><!-- #main -->
    </div><!-- #primary -->

<?php
get_footer();
