<?php 
include ('__autoload.php');
use PhpImap\Mailbox as ImapMailbox;
use PhpImap\IncomingMail;
use PhpImap\IncomingMailAttachment;

$conf = array(
    'label'    => 'Gmail',
    'enable'   => true,
    'mailbox'  => '{imap.gmail.com:993/imap/ssl}INBOX',
    'username' => 'developer.php.vn@gmail.com',
    'password' => 'dev@123456'
);

$mailbox = new PhpImap\Mailbox(
    $conf['mailbox'],
    $conf['username'], 
    $conf['password'], 
    __DIR__
);

$mailsIds = $mailbox->searchMailBox('ALL'); var_dump($mailbox); exit;
if (!$mailsIds) {
    die('Mailbox is empty');
}
//$mailId = reset($mailsIds);
//print_r($mails);
//print_r($mails->getAttachments());

$html = '';
foreach ($mailsIds as $mailId) {   
    $mail = $mailbox->getMail($mailId);
    if (!empty($mail->id)) {
        $html .= '<table>';    
        $html .= "<tr><td class='id'>id: {$mail->id}</td></tr>";
        $html .= "<tr><td class='date'>date: {$mail->date}</td></tr>";
        $html .= "<tr><td class='subject'>subject: {$mail->subject}</td></tr>";
        $html .= "<tr><td class='fromName'>fromName: {$mail->fromName}</td></tr>";
        $html .= "<tr><td class='fromAddress'>fromAddress: {$mail->fromAddress}</td></tr>";
        $html .= "<tr><td class='to'>to:" . implode(',', $mail->to) . "</td></tr>";
        $html .= "<tr><td class='toString'>toString:{$mail->toString}</td></tr>";
        $html .= "<tr><td class='cc'>cc:" . implode(',', $mail->cc) . "</td></tr>";
        $html .= "<tr><td class='replyTo'>replyTo:" . implode(',', $mail->replyTo) . "</td></tr>";    
        $html .= "<tr><td class='textPlain'>textPlain</td></tr>";   
        $html .= "<tr><td class='textPlain'>{$mail->textPlain}</td></tr>";   
        $html .= "<tr><td class='textHtml'>textHtml</td></tr>";
        $html .= "<tr><td class='textHtml'>{$mail->textHtml}</td></tr>";
        $html .= '</table>';
    }
    break;
}
echo $html;