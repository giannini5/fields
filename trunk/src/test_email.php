<?php
use DAG\Framework\Email;

// require 'vendor/autoload.php';
require_once 'lib/autoload.php';

$email = new Email();
$body = '<h1>Send HTML Email using SMTP in PHP w/ cc and bcc test</h1><p>This is a test email I\'m sending using SMTP mail server with PHPMailer.</p>';
$email->send('Hello World', $body, 'david.giannini@gmail.com', 'David', 'dave@giannini5.com', 'dave.giannini@apeelsciences.com');
