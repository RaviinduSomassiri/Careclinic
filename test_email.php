<?php
require 'email_service.php';

sendEmail(
    'mayura.chameera@gmail.com',
    'Test User',
    'SMTP Test Email',
    '<h1>SMTP is working 🎉</h1>'
);

echo "Email Sent (Check Inbox / Spam)";
