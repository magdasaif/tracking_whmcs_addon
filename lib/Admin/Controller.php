<?php

namespace WHMCS\Module\Addon\MurrabatrackingAddonModule\Admin;
use WHMCS\Database\Capsule;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use PDO;
use WHMCS\Mail\Message\SendTransactionalEmail;
use WHMCS\Mail\Email;
use Carbon\Carbon;

/**
 * Sample Admin Area Controller
 */
class Controller {
    //=========================================================================
    public function index($vars){
        return 'this model used as a basis for Tracking Clients and its orders.';
    }   
    //=========================================================================
}
