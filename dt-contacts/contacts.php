<?php

require_once 'base-setup.php';
DT_Contacts_Base::instance();

require_once 'duplicates-merging.php';
new DT_Duplicate_Checker_And_Merging();

require_once 'user-module.php';
DT_Contacts_User::instance();

require_once 'dmm-module.php';
DT_Contacts_DMM::instance();

require_once 'access-module.php';
DT_Contacts_Access::instance();


