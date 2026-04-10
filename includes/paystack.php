<?php
// Paystack Configuration
// In production, these should be in environment variables or a secure config file
// For this MVP, we'll use Sandbox Keys (replace with user's keys later)

define('PAYSTACK_SECRET_KEY', 'sk_test_replace_this_with_your_actual_key'); // @TODO: User to replace
define('PAYSTACK_PUBLIC_KEY', 'pk_test_replace_this_with_your_actual_key'); // @TODO: User to replace

class Paystack {
    
    // Initialize Transaction
    public static function initialize($email, $amount, $reference, $callback_url) {
        $url = "https://api.paystack.co/transaction/initialize";
        $fields = [
            'email' => $email,
            'amount' => $amount * 100, // Amount in kobo
            'reference' => $reference,
            'callback_url' => $callback_url
        ];
        
        $fields_string = http_build_query($fields);
        
        // Open connection
        $ch = curl_init();
        
        // Set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
            "Cache-Control: no-cache",
        ));
        
        // So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
        
        //execute post
        $result = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($result, true);
    }
    
    // Verify Transaction
    public static function verify($reference) {
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
                "Cache-Control: no-cache",
            ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        
        if ($err) {
            return ['status' => false, 'message' => "cURL Error #:" . $err];
        } else {
            return json_decode($response, true);
        }
    }
}
?>
