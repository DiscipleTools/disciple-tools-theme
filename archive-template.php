<?php
declare(strict_types=1);

( function () {
    //@todo permissions
    get_header();
    ?>

    <div id="content">
        <div id="inner-content" class="grid-x grid-margin-x">
            <aside class="large-3 cell padding-bottom show-for-large">
                <div class="bordered-box js-pane-filters">
                    <?php /* Javascript may move .js-filters-modal-content to this location. */ ?>
                </div>
            </aside>
            <main id="main" class="large-9 cell padding-bottom" role="main">
                <div class="bordered-box">
                    You are awesome
                </div>
            </main>
        </div>
    </div>

    <?php
    get_footer();
} )();
