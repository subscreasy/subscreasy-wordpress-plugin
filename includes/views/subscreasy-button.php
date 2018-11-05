<?php
/**
 * HTML output for the [subscreasy_button] shortcode.
 */
?>
<button class="subscreasy-button <?php echo $atts['class']; ?>" data-offer-id="<?php echo $atts['offer_id']; ?>">
<?php echo $atts['title']; ?>
</button>