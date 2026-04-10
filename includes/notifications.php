<?php
/**
 * Dajot Notification System
 * Handles sending emails, SMS, and WhatsApp messages.
 */

class NotificationSystem {
    
    // Mock API Keys - in production use environment variables
    private $sms_api_key = 'mock_sms_key';
    private $whatsapp_api_key = 'mock_wa_key';
    
    /**
     * Send an email notification
     */
    public static function sendEmail($to, $subject, $message) {
        // In a real app, use PHPMailer or SendGrid
        $headers = "From: no-reply@dajot.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        // Simulating email send
        // mail($to, $subject, $message, $headers);
        
        // Log for demo purposes
        self::log("EMAIL sent to $to: $subject");
        return true;
    }
    
    /**
     * Send SMS Notification (Integration point for Termii/Twilio)
     */
    public static function sendSMS($phone, $message) {
        // Mock Implementation
        // $url = "https://api.termii.com/api/sms/send";
        // curl call here...
        
        self::log("SMS sent to $phone: $message");
        return true;
    }
    
    /**
     * Send WhatsApp Notification (Integration point for WhatsApp Business API)
     */
    public static function sendWhatsApp($phone, $message) {
        // Mock Implementation
        // $url = "https://graph.facebook.com/v15.0/PHONE_NUMBER_ID/messages";
        // curl call here...
        
        self::log("WHATSAPP sent to $phone: $message");
        return true;
    }
    
    private static function log($msg) {
        $logFile = __DIR__ . '/../logs/notifications.log';
        if (!is_dir(dirname($logFile))) mkdir(dirname($logFile), 0777, true);
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $msg" . PHP_EOL, FILE_APPEND);
    }
}
?>
