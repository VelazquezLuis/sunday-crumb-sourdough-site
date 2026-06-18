Sunday Crumb Sourdough Co - Website Package
===========================================

Files included:
- index.html
- styles.css
- script.js
- send-order.php
- thank-you.html
- logo.jpg
- picture.png

How the order form works:
- The website is a static HTML/CSS/JS front end.
- The form submits to send-order.php.
- send-order.php uses PHP's mail() function to send the order details to:
  javiervelazquez113@yahoo.com

Hosting on Hostinger:
1. Upload all files into your public_html folder.
2. Make sure PHP is enabled on the hosting plan.
3. Keep index.html and send-order.php in the same folder.
4. Test by submitting an order.

Important note about email delivery:
- PHP mail() can work, but SMTP is usually more reliable.
- If mail() does not deliver consistently, switch the PHP file to PHPMailer using Hostinger SMTP.
- Hostinger's own help articles say PHPMailer with SMTP is more reliable than PHP mail().

Suggested next steps:
- Connect your domain.
- Add your social handle / pickup details if needed.
- Replace text once you have final school project copy.
