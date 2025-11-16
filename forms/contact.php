<?php
/**
 * HaulHub Contact Form Handler
 * Handles contact form submissions including file attachments
 */

// Your email address
$receiving_email_address = 'nextcraftsystems@gmail.com';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars($_POST['subject']);
    $inquiry_type = htmlspecialchars($_POST['inquiry_type']);
    $message_content = htmlspecialchars($_POST['message']);

    // Email subject
    $email_subject = "HaulHub Contact Form: $subject";

    // Email body
    $email_body = "You have received a new message from HaulHub Contact Form.\n\n";
    $email_body .= "Name: $name\n";
    $email_body .= "Email: $email\n";
    $email_body .= "Inquiry Type: $inquiry_type\n";
    $email_body .= "Message:\n$message_content\n";

    // Headers
    $headers = "From: $name <$email>\r\n";
    $headers .= "Reply-To: $email\r\n";

    // Check if a file was uploaded
    $has_attachment = false;
    if(isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
        $has_attachment = true;
        $file_tmp_name = $_FILES['attachment']['tmp_name'];
        $file_name = $_FILES['attachment']['name'];
        $file_type = $_FILES['attachment']['type'];
        $file_size = $_FILES['attachment']['size'];
        $file_content = chunk_split(base64_encode(file_get_contents($file_tmp_name)));

        $boundary = md5(uniqid(time()));
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

        $email_message = "--$boundary\r\n";
        $email_message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $email_message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $email_message .= $email_body . "\r\n";

        $email_message .= "--$boundary\r\n";
        $email_message .= "Content-Type: $file_type; name=\"$file_name\"\r\n";
        $email_message .= "Content-Transfer-Encoding: base64\r\n";
        $email_message .= "Content-Disposition: attachment; filename=\"$file_name\"\r\n\r\n";
        $email_message .= $file_content . "\r\n";
        $email_message .= "--$boundary--";

    } else {
        // No attachment
        $email_message = $email_body;
    }

    // Send email
    if(mail($receiving_email_address, $email_subject, $email_message, $headers)) {
        echo json_encode(['status' => 'success', 'message' => 'Your message has been sent successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Sorry, something went wrong. Please try again later.']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
