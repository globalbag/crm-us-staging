<?php

namespace Sparket\Tools\Email;

class Email
{

    static function email_notification($Subject = "", $Body = "", $view_data = [], $MailTo = "", $Options = [])
    {
        if($MailTo && is_string($MailTo))
        {
            $MailInfo['To'] = $MailTo;
        }
        else if ($MailTo && is_array($MailTo))
        {
            $MailInfo['To'] = ['Name' => $MailTo['Name'], 'Email' => $MailTo['Email']];
        }

        if(!$MailInfo['To'] || !$Subject || !$Body)
        {
            return false;
        }

        $view_data['SoftwareName'] = \BN_Var::$Config['Software']['Name'];

        $SMTPServer = \BN::param('SMTPServer', 'json');

        $MailInfo['From'] = $Options['From'] ? : ['Name' => $SMTPServer['ServerFromName'], 'Email' => $SMTPServer['ServerFromEmail']];
        $MailInfo['Reply'] = $Options['Reply'];

        $MailInfo['Cc'] = $Options['Cc'];

        $MailInfo['Bcc'] = $Options['Bcc'];

        $MailInfo['Subject'] = "{$Subject} - {$SMTPServer['ServerFromName']}";

        $MailInfo['Body'] = $Body;

        $MailInfo['Attachment'] = $Options['Attachment'];

        foreach(array("Subject", "Body") as $ii)
        {
            $MailInfo[$ii] = \BN::tplrender($MailInfo[$ii], $view_data);
        }

        $MailInfo['Subject'] = html_entity_decode($MailInfo['Subject']);

        $MailInfo['Subject'] = \BN_Format::remove_accents($MailInfo['Subject']);

        $MailInfo['Body'] = html_entity_decode($MailInfo['Body']);

        $BNMail = new \BN_Mail();
        $BNMail->MailSubject($MailInfo['Subject']);
        $BNMail->MailBody($MailInfo['Body']);

        $BNMail->MailFrom($MailInfo['From']);

        $BNMail->MailTo($MailInfo['To']);

        if($MailInfo['Cc'])
        {
            $BNMail->MailCc($MailInfo['Cc']);
        }

        if($MailInfo['Reply'])
        {
            $BNMail->MailReply($MailInfo['Reply']);
        }

        if($MailInfo['Bcc'])
        {
            $BNMail->MailBcc($MailInfo['Bcc']);
        }

        if($MailInfo['Attachment'])
        {
            $BNMail->MailAttachments($MailInfo['Attachment']);
        }

        if (\BN_Var::$Config['Env'] == 'production')
        {
            $BNMail->SendMail(true, $Options['debug']);
        }
        else
        {
            $BNMail->SendMail(true);
        }

        return ($BNMail->ErrorStatus == 0) ? true : false;
    }

}