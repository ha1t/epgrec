<?php
// bootstrap
require_once dirname(__FILE__) . '/database.php';
require_once dirname(dirname(__FILE__)) . '/models/model.php';
require_once dirname(dirname(__FILE__)) . '/models/category.php';
require_once dirname(dirname(__FILE__)) . '/models/channel.php';
require_once dirname(dirname(__FILE__)) . '/models/program.php';
require_once dirname(dirname(__FILE__)) . '/models/reserve.php';
require_once dirname(dirname(__FILE__)) . '/models/keyword.php';
require_once dirname(dirname(__FILE__)) . '/models/recorder_service.php';

require_once dirname(dirname(__FILE__)) . '/models/channel_master.php';

require_once dirname(dirname(__FILE__)) . '/Smarty/Smarty.class.php';
