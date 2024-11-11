<?php
/**
 * WHMCS SDK Sample Addon Module
 *
 * An addon module allows you to add additional functionality to WHMCS. It
 * can provide both client and admin facing user interfaces, as well as
 * utilise hook functionality within WHMCS.
 *
 * This sample file demonstrates how an addon module for WHMCS should be
 * structured and exercises all supported functionality.
 *
 * Addon Modules are stored in the /modules/addons/ directory. The module
 * name you choose must be unique, and should be all lowercase, containing
 * only letters & numbers, always starting with a letter.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "murrabatrackingaddonmodule" and therefore all functions
 * begin "murrabatrackingaddonmodule_".
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/addon-modules/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

/**
 * Require any libraries needed for the module to function.
 * require_once __DIR__ . '/path/to/library/loader.php';
 *
 * Also, perform any initialization required by the service's library.
 */

use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\murrabatrackingaddonmodule\Admin\AdminDispatcher;
use WHMCS\Module\Addon\murrabatrackingaddonmodule\Client\ClientDispatcher;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define addon module configuration parameters.
 *
 * Includes a number of required system fields including name, description,
 * author, language and version.
 *
 * Also allows you to define any configuration parameters that should be
 * presented to the user when activating and configuring the module. These
 * values are then made available in all module function calls.
 *
 * Examples of each and their possible configuration parameters are provided in
 * the fields parameter below.
 *
 * @return array
 */
function murrabatrackingaddonmodule_config()
{
    return [
        
        'name'          => 'MurabbaTracking Addon Module',// Display name for your module
        'description'   => 'This module provides an example WHMCS Addon Module'  . ' which can be used as a basic for Tracking Clients and its orders .',
        'author'        => 'Murabba',// Module author name
        'language'      => 'english',// Default language
        'version'       => '1.0',// Version number
        'fields' => [
            // a text field type allows for single line text input
            'webhook_url' => [
                'FriendlyName'      => 'webhook url',
                'Type'              => 'text',
                'Size'              => '25',
                'Default'           => 'https://n8n.murabba.dev/webhook-test/',
                'Description'       => 'url to push data to',
            ],
        ],
    ];
}

/**
 * Activate.
 *
 * Called upon activation of the module for the first time.
 * Use this function to perform any database and schema modifications
 * required by your module.
 *
 * This function is optional.
 *
 * @see https://developers.whmcs.com/advanced/db-interaction/
 *
 * @return array Optional success/failure message
 */
function murrabatrackingaddonmodule_activate(){ 
    return [
        // Supported values here include: success, error or info
        'status' => 'success',
        'description' => 'This is a demo module only. '
            . 'In a real module you might report a success or instruct a '
                . 'user how to get started with it here.',
    ];
}

/**
 * Deactivate.
 *
 * Called upon deactivation of the module.
 * Use this function to undo any database and schema modifications
 * performed by your module.
 *
 * This function is optional.
 *
 * @see https://developers.whmcs.com/advanced/db-interaction/
 *
 * @return array Optional success/failure message
 */
function murrabatrackingaddonmodule_deactivate(){
    return [
        // Supported values here include: success, error or info
        'status' => 'success',
        'description' => 'This is a demo module only. '
            . 'In a real module you might report a success here.',
    ];
}

/**
 * Upgrade.
 *
 * Called the first time the module is accessed following an update.
 * Use this function to perform any required database and schema modifications.
 *
 * This function is optional.
 *
 * @see https://laravel.com/docs/5.2/migrations
 *
 * @return void
 */
function murrabatrackingaddonmodule_upgrade($vars)
{
    $currentlyInstalledVersion = $vars['version'];

    /// Perform SQL schema changes required by the upgrade to version 1.1 of your module
    if ($currentlyInstalledVersion < 1.1) {
        $schema = Capsule::schema();
        // Alter the table and add a new text column called "demo2"
        // $schema->table('tenant_details', function($table) {
        //     $table->text('demo2');
        // });
    }

    /// Perform SQL schema changes required by the upgrade to version 1.2 of your module
    if ($currentlyInstalledVersion < 1.2) {
        $schema = Capsule::schema();
        // Alter the table and add a new text column called "demo3"
        // $schema->table('tenant_details', function($table) {
        //     $table->text('demo3');
        // });
    }
}

/**
 * Admin Area Output.
 *
 * Called when the addon module is accessed via the admin area.
 * Should return HTML output for display to the admin user.
 *
 * This function is optional.
 *
 * @see murrabatrackingaddonmodule\Admin\Controller::index()
 *
 * @return string
 */
function murrabatrackingaddonmodule_output($vars)
{
    // return 'addon index page';
    //=====================================================
    // Dispatch and handle request here. What follows is a demonstration of one
    // possible way of handling this using a very basic dispatcher implementation.

    // $action='create';
    // $_REQUEST['action']=$action;
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

    $dispatcher = new AdminDispatcher();
    $response = $dispatcher->dispatch($action, $vars);
    echo $response;
}

/**
 * Admin Area Sidebar Output.
 *
 * Used to render output in the admin area sidebar.
 * This function is optional.
 *
 * @param array $vars
 *
 * @return string
 */
function murrabatrackingaddonmodule_sidebar($vars){
    //=====================================================
    ///=====================================================
}

/**
 * Client Area Output.
 *
 * Called when the addon module is accessed via the client area.
 * Should return an array of output parameters.
 *
 * This function is optional.
 *
 * @see murrabatrackingaddonmodule\Client\Controller::index()
 *
 * @return array
 */
function murrabatrackingaddonmodule_clientarea($vars)
{
    // Get common module parameters
    $modulelink = $vars['modulelink']; // eg. index.php?m=murrabatrackingaddonmodule
    $version = $vars['version']; // eg. 1.0
    $_lang = $vars['_lang']; // an array of the currently loaded language variables

    // Get module configuration parameters
    $configTextField = $vars['tenant_id'];
    // $configPasswordField = $vars['Password Field Name'];
    // $configCheckboxField = $vars['Checkbox Field Name'];
    $configDropdownField = $vars['project_id'];
    $configRadioField = $vars['deployment_project_type'];
    // $configTextareaField = $vars['Textarea Field Name'];

    /**
     * Dispatch and handle request here. What follows is a demonstration of one
     * possible way of handling this using a very basic dispatcher implementation.
     */

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

    $dispatcher = new ClientDispatcher();
    return $dispatcher->dispatch($action, $vars);
}
