<ul class="tabs" data-tabs id="my-activity-tabs" data-deep-link="true" data-update-history="true" data-deep-link-smudge="true" data-deep-link-smudge="500">

    <li class="tabs-title is-active"><a href="#panel1" aria-selected="true">Activity</a></li>
    <li class="tabs-title"><a href="#panel2">Comments <?php if (get_comments_number() > 0) { echo '(' . get_comments_number() . ')'; } ?></a></li>

</ul>

<div class="tabs-content" data-tabs-content="my-activity-tabs">

    <div class="tabs-panel is-active" id="panel1">

        <?php dt_activity_metabox()->activity_meta_box(get_the_ID()); ?>

    </div>
    <div class="tabs-panel" id="panel2">

        <?php comments_template(); ?>

    </div>

</div>