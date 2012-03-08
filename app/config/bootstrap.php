<?php
// bootstrap
require_once dirname(__FILE__) . '/database.php';

/*
require_once dirname(dirname(__FILE__)) . '/models/category.php';
require_once dirname(dirname(__FILE__)) . '/models/channel.php';
require_once dirname(dirname(__FILE__)) . '/models/program.php';
require_once dirname(dirname(__FILE__)) . '/models/reserve.php';
require_once dirname(dirname(__FILE__)) . '/models/keyword.php';
require_once dirname(dirname(__FILE__)) . '/models/recorder_service.php';

require_once dirname(dirname(__FILE__)) . '/models/channel_master.php';
 */

require_once APP_DIR.'app_controller.php';
require_once APP_DIR.'app_model.php';
require_once APP_DIR.'app_exception.php';

require_once ROOT_DIR . '/Smarty/Smarty.class.php';

define('MASTER_DIR', APP_DIR . 'master/'); 

// autoload
spl_autoload_register(function($name) {
    $filename = Inflector::underscore($name) . '.php';
    if (strpos($name, 'Controller') !== false) {
        require CONTROLLERS_DIR . $filename;
    } elseif (strpos($name, 'Master') !== false) {
        require MASTER_DIR . $filename;
    } else {
        if (file_exists(MODELS_DIR . $filename)) {
            require MODELS_DIR . $filename;
        }
    }
});
