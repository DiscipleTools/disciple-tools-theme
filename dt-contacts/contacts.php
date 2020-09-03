<?php

require_once 'contacts-endpoints.php';
DT_Contacts_Endpoints::instance();
require_once 'base-setup.php';
DT_Contacts_Base::instance();
require_once 'dmm-module.php';
DT_Contacts_DMM::instance();
require_once 'access-module.php';
DT_Contacts_Access::instance();
require_once 'user-module.php';
DT_Contacts_User::instance();
