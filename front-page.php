<?php

if (user_can(get_current_user_id(), 'read_contact')) {
    include ('dashboard.php');
} else {
    include ('archive-prayer.php');
}