<?php
use WHMCS\Database\Capsule;

/**
 * WHMCS SDK Sample Addon Module Hooks File
 *
 * Hooks allow you to tie into events that occur within the WHMCS application.
 *
 * This allows you to execute your own code in addition to, or sometimes even
 * instead of that which WHMCS executes by default.
 *
 * @see https://developers.whmcs.com/hooks/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

// Require any libraries needed for the module to function.
// require_once __DIR__ . '/path/to/library/loader.php';
//
// Also, perform any initialization required by the service's library.

//=============================================================================
/**
 * Register a hook with WHMCS.
 *
 * This sample demonstrates triggering a service call when a change is made to
 * a client profile within WHMCS.
 *
 * For more information, please refer to https://developers.whmcs.com/hooks/
 *
 * add_hook(string $hookPointName, int $priority, string|array|Closure $function)
 */

//=============================================================================
add_hook('ClientAdd', 1, function($client) {
    try {
        //store client data in log table just for test
        Capsule::table('tblerrorlog')->insert(['severity'=> 'addon-test-client-params','message'=>'after client created','details'=>json_encode($client)]) ;
        //******************************************************************************************************************************************************
        //start to send this data to external endpoint
        $postData = [
            'client_details' => $client,
            // Include any other necessary parameters
        ];
        $webhook_url=retrieveWebhookUrlFromConfig().'new-client';//'https://n8n.murabba.dev/webhook-test/new-client'
        sendPostDataToErp($webhook_url,$postData,'add_client');
        //******************************************************************************************************************************************************
    } catch (Exception $e) {
        // Consider logging or reporting the error.
        Capsule::table('tblerrorlog')->insert(['severity'=> 'addon-test-client-params','message'=>'error after client created','details'=>json_encode($e->getMessage())]) ;
    }
});
//==================================================================================
//=================================================================
add_hook('AfterShoppingCartCheckout', 1, function($vars) {
    // handle actions after add order step
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    /*
        OrderID	        int	    The Order ID
        OrderNumber	    int	    The randomly generated order number
        ServiceIDs	    array	An array of Service IDs created by the order
        AddonIDs	    array	An array of Addon IDs created by the order
        DomainIDs	    array	An array of Domain IDs created by the order
        RenewalIDs	    array	An array of Domain Renewal IDs created by the order
        PaymentMethod	string	The payment gateway selected
        InvoiceID	    int	    The Invoice ID
        TotalDue	    float	The total amount due
    */
    /*
        response
        {"OrderID":54,"OrderNumber":"4605195931","ServiceIDs":[49],"DomainIDs":[],"AddonIDs":[],"UpgradeIDs":[],"RenewalIDs":[],"PaymentMethod":"paypal","InvoiceID":101,"TotalDue":"2.99","Products":[49],"Domains":[],"Addons":[],"Renewals":[],"ServiceRenewals":[],"AddonRenewals":[]}
    */
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    //start to send this data to external endpoint
    $postData = [
        'details' => handleOrderObject($vars),
        // Include any other necessary parameters
    ];
    $webhook_url=retrieveWebhookUrlFromConfig().'new-order';//'https://n8n.murabba.dev/webhook-test/new-order'
    sendPostDataToErp($webhook_url,$postData,'order_created');
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
});
//=============================================================================
// function sendPostDataToErp($url,$data,$type){
//     //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//     //using curl (post)
//         // Create a new cURL resource
//         $ch = curl_init();
    
//         // Set the URL
//         curl_setopt($ch, CURLOPT_URL, $url);
    
//         // Set the request method to POST,default is GET
//         curl_setopt($ch, CURLOPT_POST, true);
//         curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    
//         // Set options for receiving the response
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
//         // Execute the request
//         $response = curl_exec($ch);
            
//         Capsule::table('tblerrorlog')->insert(['severity' => 'hook-http-'.$type, 'details' => $response]);

//         // Check for errors
//         if ($response === false) {
//             // Request failed
//             $error = curl_error($ch);
//             Capsule::table('tblerrorlog')->insert(['severity' => 'hook-test-http-'.$type.'-request-error', 'details' => $error]);
//         } else {
//             // Request was successful
//             $responseData = json_decode($response, true);
//             Capsule::table('tblerrorlog')->insert(['severity' => 'hook-test-http-'.$type.'-request-success', 'details' => $responseData]);
//         }
    
//         // Close the cURL resource
//         curl_close($ch);
//     //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
// }
//=================================================================
function handleOrderObject($order){
    // {"OrderID":54,"OrderNumber":"4605195931","ServiceIDs":[49],"DomainIDs":[],"AddonIDs":[],"UpgradeIDs":[],"RenewalIDs":[],"PaymentMethod":"paypal","InvoiceID":101,"TotalDue":"2.99","Products":[49],"Domains":[],"Addons":[],"Renewals":[],"ServiceRenewals":[],"AddonRenewals":[]}

    $tenant_id      = $order['ServiceIDs'][0];//host_id
    
    $host_data      = Capsule::table('tblhosting')->where('id',$tenant_id)->first();
    $package_id     = $host_data->packageid;//product_id
    
    $package_data   = Capsule::table('tblproducts')->where('id',$package_id)->first();
    $project_id     = $package_data->gid;//group_id

    $client_data    = Capsule::table('tblclients')->where('id',$host_data->userid)->first();

    //******************************************************************* */
    $order_details=[
        'order_details'=>[
            'order_id'       => $order['OrderID'],
            'order_number'   => $order['OrderNumber'],
            'payment_method' => $order['PaymentMethod'],
        ]
    ];
    //******************************************************************* */    
    $host_details=[
        'host_details'=>[
            'host_id'       => $tenant_id,
            'domain'        => $host_data->domain,
            'domain_status' => $host_data->domainstatus,
        ]
    ];
    //******************************************************************* */
    $product_details=[
        'product_details'=>[
            'product_id'    => $host_data->packageid,
            'product_name'  => $package_data->name,
        ]
    ];
    //******************************************************************* */
    $invoice_details=[];
    if($order['InvoiceID']!=0){
        
        $invoice_data    = Capsule::table('tblinvoices')->where('id',$order['InvoiceID'])->first();

        $invoice_details=[
            'invoice_details'=>[
                'invoice_id'     => $order['InvoiceID'],
                'date'           => $invoice_data->date,
                'duedate'        => $invoice_data->duedate,
                'subtotal'       => $invoice_data->subtotal,
                'credit'         => $invoice_data->credit,
                'tax'            => $invoice_data->tax,
                'tax2'           => $invoice_data->tax2,
                'total'          => $invoice_data->total,
                'taxrate'        => $invoice_data->taxrate,
                'taxrate2'       => $invoice_data->taxrate2,
                'paymentmethod'  => $invoice_data->paymentmethod,
                'paymethodid'    => $invoice_data->paymethodid,
                'notes'          => $invoice_data->notes,
                'created_at'     => $invoice_data->created_at,
            ]
        ];
    }
    //******************************************************************* */
    $client_details=[
        'client_details'=>[
            'client_id'         => $client_data->id,
            'firstname'         => $client_data->firstname,
            'lastname'          => $client_data->lastname,
            'companyname'       => $client_data->companyname,
            'email'             => $client_data->email,
            'address1'          => $client_data->address1,
            'city'              => $client_data->city,
            'state'             => $client_data->state,
            'postcode'          => $client_data->postcode,
            'country'           => $client_data->country,
            'phonenumber'       => $client_data->phonenumber,
            'status'            => $client_data->status,
            'defaultgateway'    => $client_data->defaultgateway,
            'emailoptout'       => $client_data->emailoptout,
            'allow_sso'         => $client_data->allow_sso,
        ]
    ];
    //******************************************************************* */
    return array_merge($order_details,$host_details,$product_details,$invoice_details,$client_details);
}
//=================================================================
function retrieveWebhookUrlFromConfig() {
    //******************************************************************************************************************************************************
    $addonSettings = Capsule::table('tbladdonmodules')
    ->where('module', 'murrabatrackingaddonmodule')
    ->pluck('value', 'setting')
    ->toArray();
    if(isset($addonSettings)){
        // Now you can access your settings like this
        $webhook_url = $addonSettings['webhook_url'];
    }else{
         $webhook_url = 'https://n8n.murabba.dev/webhook-test/';
    }
    Capsule::table('tblerrorlog')->insert(['severity'=> 'addon-config','message'=>'read config','details'=>$webhook_url]) ;
    return $webhook_url;
    //******************************************************************************************************************************************************
}