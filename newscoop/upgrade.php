<?php
/**
 * @package Campsite
 *
 * @author Mugur Rus <mugur.rus@gmail.com>
 * @copyright 2008 MDLF, Inc.
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version $Revision$
 * @link http://www.campware.org
 */

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// goes to install process if configuration files does not exist yet
if (!file_exists(APPLICATION_PATH . '/../conf/configuration.php')
    || !file_exists(APPLICATION_PATH . '/../conf/database_conf.php')) {
    $subdir = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/', -2));
    header("Location: $subdir/install/");
    exit;
}

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(dirname(__FILE__) . '/../include'),
    get_include_path(),
)));
if (!is_file('Zend/Application.php')) {
	// include libzend if we dont have zend_application
	set_include_path(implode(PATH_SEPARATOR, array(
		'/usr/share/php/libzend-framework-php',
		get_include_path(),
	)));
}

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap('autoloader');
$application->bootstrap('doctrine');

header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");

$g_documentRoot = dirname(__FILE__);

// goes to install process if configuration files does not exist yet
if (!file_exists($g_documentRoot.'/conf/configuration.php')
        || !file_exists($g_documentRoot.'/conf/database_conf.php')) {
    header('Location: install/index.php');
    exit(0);
}

require_once($g_documentRoot.'/include/campsite_init.php');
require_once($g_documentRoot.'/bin/cli_script_lib.php');
require_once($g_documentRoot.'/install/classes/CampInstallation.php');
require_once($g_documentRoot.'/classes/User.php');
require_once($g_documentRoot.'/classes/CampPlugin.php');

$res = camp_detect_database_version($Campsite['DATABASE_NAME'], $dbVersion);
if ($res !== 0) {
    $dbVersion = '[unknown]';
}
echo "Upgrading the database from version $dbVersion...";

// initiates the campsite site
$campsite = new CampSite();

// loads site configuration settings
$campsite->loadConfiguration(CS_PATH_CONFIG.DIR_SEP.'configuration.php');

// starts the session
$campsite->initSession();

$session = CampSite::GetSessionInstance();
$configDb = array('hostname'=>$Campsite['db']['host'],
                  'hostport'=>$Campsite['db']['port'],
                  'username'=>$Campsite['db']['user'],
                  'userpass'=>$Campsite['db']['pass'],
                  'database'=>$Campsite['db']['name']);
$session->setData('config.db', $configDb, 'installation');

// upgrading the database
$res = camp_upgrade_database($Campsite['DATABASE_NAME'], true);
if ($res !== 0) {
    display_upgrade_error("While upgrading the database: $res");
}
CampCache::singleton()->clear('user');
CampCache::singleton()->clear();
SystemPref::DeleteSystemPrefsFromCache();

// replace $campsite by $gimme
require_once($g_documentRoot.'/classes/TemplateConverterNewscoop.php');
$template_files = camp_read_files($g_documentRoot.'/templates');
$converter = new TemplateConverterNewscoop();
foreach($template_files as $template_file) {
    $converter->read($template_file);
    $converter->parse();
    $converter->write();
}

// update plugins
CampPlugin::OnUpgrade();

CampRequest::SetVar('step', 'finish');
$install = new CampInstallation();
$install->initSession();
$step = $install->execute();

CampTemplate::singleton()->clearCache();

// replace javascript by js in .htaccess file
$htaccesspath = $g_documentRoot . '/.htaccess';
if (upgrade_htaccess($htaccesspath) == false) {
    display_upgrade_error('Could not write .htaccess file.<br />Please read the '
        . 'UPGRADE.txt file in this same directory to see what changes need to '
        . 'be apply for this specific version of Newscoop.', FALSE);
}

if (file_exists(__FILE__)) {
    @unlink(__FILE__);
}

function display_upgrade_error($p_errorMessage, $exit = TRUE) {
    if (defined('APPLICATION_ENV') && APPLICATION_ENV == 'development') {
        var_dump($p_errorMessage);
        if ($exit) {
            exit(1);
        }
    }

    $template = CS_SYS_TEMPLATES_DIR.DIR_SEP.'_campsite_error.tpl';
    $templates_dir = CS_TEMPLATES_DIR;
    $params = array('context' => null,
                    'template' => $template,
                    'templates_dir' => $templates_dir,
                    'error_message' => $p_errorMessage
    );
    $document = CampSite::GetHTMLDocumentInstance();
    $document->render($params);
    if ($exit) exit(0);
}

function upgrade_htaccess($htaccesspath)
{
    if (!file_exists($htaccesspath)) {
        return false;
    }
    if (!($htaccess = @file_get_contents($htaccesspath))) {
        return false;
    }
    $htaccess = str_replace('+javascript', '+js', $htaccess);
    if (@file_put_contents($htaccesspath, $htaccess) === false) {
        return false;
    }
    return true;
}

?>
<p>finished<br/><a href="<?php echo "/$ADMIN";?>">Administration</a></p>
