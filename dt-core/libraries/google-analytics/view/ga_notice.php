<div id="googleanalytics_terms_notice"
     class="notice notice-warning <?php echo ( Ga_Helper::is_plugin_page() ) ? '' : 'is-dismissible' ?>">
    <p>
        Google Analytics <?php echo esc_html( GOOGLEANALYTICS_VERSION ); ?> plugin <a
                href="http://www.sharethis.com/news/2016/12/sharethis-adds-analytics-plugin-to-suite-of-tools/"
                target="_blank">has joined the ShareThis family.</a> <strong>A host of new features</strong> has been added in this version, including Google Analytics dashboards, Trending Content, and Alerts. The update requires agreeing to the <a href="http://www.sharethis.com/privacy/" target="_blank">privacy policy</a> and <a
                href="http://www.sharethis.com/publisher-terms-of-use/" target="_blank">terms of use</a> to enable them.
        <a href="<?php echo esc_url( $url ); ?>"><span class="button button-primary">I accept</span></a>
    </p>
</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('#googleanalytics_terms_notice .notice-dismiss').live('click', function (event) {
            event.preventDefault();
            jQuery.post(ajaxurl, {action: 'googleanalytics_hide_terms'});
        });
    });
</script>
