<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */

namespace App\Libraries;


use App\Helpers\FilterHelper;
use CodeIgniter\Files\File;
use Config\Services;
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Mailbox;
use ZBateson\MailMimeParser\Header\HeaderConsts;
use ZBateson\MailMimeParser\MailMimeParser;
use App\Libraries\ChangeLogs;

class MailFetcher
{
    const SUBJECT_TO_IGNORE = array("undelivered mail returned to sender", "out of office", "abwesend", "abwesenheit", "delivery status notification");
    const WUFOO_EMAIL = "no-reply@wufoo.com";
    private $attachment_dir;
    public function __construct()
    {
        $this->attachment_dir = WRITEPATH . 'attachments';
    }
    public function parse_imap()
    {
        $emails = new Emails();
        if ($email_list = $emails->getFetcher()) {
            foreach ($email_list as $email) {
                $mailbox = new Mailbox(
                    '{' . $email->imap_host . ':' . $email->imap_port . '/' . $email->incoming_type . '/ssl/novalidate-cert}INBOX', // IMAP server and mailbox folder
                    $email->imap_username, // Username for the before configured mailbox
                    $email->imap_password // Password for the before configured username
                );
                try {
                    log_message('info', 'Trying to connect to ' . $email->imap_host . ' with ' . $email->imap_username . '.');
                    $mailsIds = $mailbox->searchMailbox('ALL');
                } catch (ConnectionException $ex) {
                    log_message('error', 'Connection to ' . $email->imap_host . ' with ' . $email->imap_username . ' failed.');
                    log_message('error', 'IMAP connection failed: ' . $ex);
                    return false;
                }
                if (!$mailsIds) {
                    continue;
                }
                $mailbox->setAttachmentsDir($this->attachment_dir);
                foreach ($mailsIds as $k => $v) {
                    $mail = $mailbox->getMail($mailsIds[$k]);
                    $subject = strtolower(trim($mail->subject ?? ''));
                    if ($subject !== '' && in_array($subject, array_map('strtolower', self::SUBJECT_TO_IGNORE))) {
                        log_message('info', 'Ignoring bouncing and out of office emails');
                        $mailbox->deleteMail($mail->id);
                        continue;
                    }
                    $message = ($mail->textHtml) ? $this->cleanMessage($mail->textHtml) : $mail->textPlain;
                    $fromEmailAddress = $mail->fromAddress;
                    if (strpos(self::WUFOO_EMAIL, $mail->fromAddress) !== false) {
                        preg_match('/(?:&nbsp;|\s)*(?:<a[^>]*?href="mailto:([^">]+)"[^>]*?>|([^\s<]+@[^\s>]+))/', ($mail->textHtml) ? $mail->textHtml : $mail->textPlain, $matches);
                        $fromEmailAddress = (isset($matches[1]) && $matches[1] != "") ? $matches[1] : (isset($matches[2]) ? $matches[2] : $mail->fromAddress);
                    }
                    preg_match('/https:\/\/flyingteachers\.wufoo\.com\/[^\s"<>]+/', ($mail->textHtml) ? $mail->textHtml : $mail->textPlain, $linkMatches);
                    $link = (isset($linkMatches[0]) && $linkMatches[0] != "") ? $linkMatches[0] : (isset($linkMatches[1]) ? $linkMatches[1] : '');
                    $toTicket = $this->parseToTicket($mail->fromName, $fromEmailAddress, $mail->subject, $message, $email->department_id, $mail->to);
                    list($ticket_id, $message_id) = $toTicket;
                    //Attachments
                    $attachments = new Attachments();
                    if (!empty($link)) {
                        $ch = curl_init($link);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        $fileContents = curl_exec($ch);
                        if ($fileContents !== false) {
                            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                            $originalFilename = uniqid() . $this->getExtensionFromMimeType($contentType);
                            $fileExtension = pathinfo($originalFilename, PATHINFO_EXTENSION);
                            $fileSize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);

                            // Use the original filename if available, or create a new one based on the URL
                            $fileName = $originalFilename !== 'unknown' ? $originalFilename : 'downloaded_file_' . time() . '.' . $fileExtension;

                            // Save the file with the correct extension
                            $filePath = realpath(rtrim($this->attachment_dir, '\/ ')) . DIRECTORY_SEPARATOR . $fileName;
                            file_put_contents($filePath, $fileContents);

                            $attachments->addFromTicket(
                                $ticket_id,
                                $message_id,
                                $fileName,
                                $fileName,
                                $fileSize,
                                $contentType
                            );
                        }
                    }
                    if (!empty($mail->getAttachments())) {
                        foreach ($mail->getAttachments() as $file) {
                            if (file_exists($file->filePath)) {
                                $fileInfo = new File($file->filePath);
                                $size = $fileInfo->getSize();
                                $file_type = $fileInfo->getMimeType();
                                $filename = $fileInfo->getRandomName();
                                $fileInfo->move($this->attachment_dir, $filename);
                                $original_name = $file->name;
                                $attachments->addFromTicket(
                                    $ticket_id,
                                    $message_id,
                                    $original_name,
                                    $filename,
                                    $size,
                                    $file_type
                                );
                            }
                        }
                    }
                    $mailbox->deleteMail($mail->id);
                }
                $mailbox->disconnect();
            }
        }
        return true;
    }

    public function parse_pipe()
    {
        #Read email
        $tmpfilepath = tempnam(WRITEPATH . 'mails', strval(mt_rand(1000, 9999)));
        $tmpfp = fopen($tmpfilepath, "w");
        $fp = fopen("php://stdin", "r");
        $fileContent = @stream_get_contents($fp);
        fwrite($tmpfp, $fileContent);
        fclose($tmpfp);

        #Parse email
        $mailPath = WRITEPATH . 'mails';
        $files = directory_map($mailPath);
        foreach ($files as $file) {
            $pipe_file = $mailPath . DIRECTORY_SEPARATOR . $file;
            if (is_file($pipe_file)) {
                $this->convert_pipe($pipe_file);
            }
        }
        return true;
    }

    public function convert_pipe($pipeFile)
    {
        $mailParser = new MailMimeParser();
        $handle = fopen($pipeFile, 'r');
        $message = $mailParser->parse($handle);
        fclose($handle);
        $from_address = $message->getHeaderValue(HeaderConsts::FROM);
        if ($from_address == '') {
            @unlink($pipeFile);
            return false;
        }
        $from_name = $message->getHeader(HeaderConsts::FROM)->getPersonName();
        $to = $message->getHeaderValue(HeaderConsts::TO);
        $subject = $message->getHeaderValue(HeaderConsts::SUBJECT);
        $body = $this->cleanMessage($message->getHtmlContent());
        if ($body == '') {
            $body = $message->getTextContent();
        }

        $emails = new Emails();
        if (!$emailData = $emails->getRow(['email' => $to])) {
            @unlink($pipeFile);
            return false;
        }

        $toTicket = $this->parseToTicket($from_name, $from_address, $subject, $body, $emailData->department_id);
        list($ticket_id, $message_id) = $toTicket;
        //Attachments
        $attachments = new Attachments();
        $total_attachments = $message->getAttachmentCount();
        if ($total_attachments > 0) {
            foreach ($message->getAllAttachmentParts() as $attachmentPart) {
                $fileName = $attachmentPart->getFilename();
                if ($fileName == '') {
                    continue;
                }
                $attachmentPath = WRITEPATH . 'uploads/' . $fileName;
                $attachmentPart->saveContent($attachmentPath);
                $fileInfo = new File(WRITEPATH . 'uploads/' . $fileName);
                $size = $fileInfo->getSize();
                $file_type = $fileInfo->getMimeType();
                $filename = $fileInfo->getRandomName();
                $fileInfo->move($this->attachment_dir, $filename);
                $original_name = $fileName;
                $attachments->addFromTicket(
                    $ticket_id,
                    $message_id,
                    $original_name,
                    $filename,
                    $size,
                    $file_type
                );
                @unlink($attachmentPath);
            }
        }
        @unlink($pipeFile);
    }

    public function parseToTicket($clientName, $clientEmail, $subject, $body, $department_id = 1, $to = [])
    {
        $client = Services::client();
        $tickets = Services::tickets();
        $departments = Services::departments();
        $agent_id = 0;
        $found = false;
        if (!empty($to)) {
            $all_agents_for_department = $departments->getAllAgentsForDepartment($department_id);
            foreach ($to as $k => $v) {
                foreach ($all_agents_for_department as $agent) {
                    $agent_states = isset($agent->state) ? unserialize($agent->state) : array();
                    $is_agent_active = array_key_exists($department_id, $agent_states) && $agent_states[$department_id] == "1";
                    if ($agent->email == $k) {
                        $agent_id = $is_agent_active ? $agent->id : 0;
                        $found = true;
                        break;
                    }
                }

                if ($found) {
                    break;
                }
            }
        }
        $changelogs = new Changelogs();
        $filter_helper = new FilterHelper();
        $client_id = $client->getClientID($clientName, $clientEmail);
        $body = $this->removeTicketDetailsIfAny($body);
        $body = $this->cleanLineBreaks($body);
        if (!$ticket = $tickets->getTicketFromEmail($subject)) {
            $ticket_id = $tickets->createTicket(
                $client_id,
                $subject,
                $department_id,
                1,
                $agent_id
            );
            $changelogs->create($client_id, $ticket_id, $client->getRow(['id' => $client_id])->fullname, 'Admin.actions.ticketCreatedFromEmail');
            $message_id = $tickets->addMessage($ticket_id, $body, 0, false);
            $ticket = $tickets->getTicket(['id' => $ticket_id]);
            $tickets->staffNotification($ticket);
            $message = $tickets->getFirstMessage($ticket_id);
            $filter_helper->playFilterRulesForDepartment($department_id, $ticket, $message->message, $subject, $body);
        } else {
            $ticket_id = $ticket->id;
            $message_id = $tickets->addMessage($ticket_id, $body, 0, false);
            $tickets->updateTicketReply($ticket_id, $ticket->status);
            $changelogs->create($client_id, $ticket_id, $client->getRow(['id' => $client_id])->fullname, 'Admin.actions.messageAddedFromEmail');
            $tickets->messageAddedToTicket($ticket, $body, $client->getRow(['id' => $client_id])->fullname);
        }
        return [$ticket_id, $message_id];
    }

    

    public function cleanMessage($message)
    {
        $config = \HTMLPurifier_Config::createDefault();
        $html_purifier = new \HTMLPurifier($config);
        return $html_purifier->purify($message);
    }

    private function cleanLineBreaks($body)
    {
        $pattern = '/(<br\s*\/?>\s*){2,}/i';
        $replacement = '<br>';
        return preg_replace($pattern, $replacement, $body);
    }

    private function removeTicketDetailsIfAny($body) {
        $pattern_1 = '/Ticket[-\s]Details[\s\S]*$/m';
        $pattern_2 = '/-{11,}[\s\S]*$/m';
        $cleaned_text = preg_replace($pattern_2, '', $body);
        $cleaned_text = preg_replace($pattern_1, '', $cleaned_text);
        return $cleaned_text;
    }

    public function getExtensionFromMimeType($contentType)
    {
        $mimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'plain/text' => 'txt',
            'image/bmp' => 'bmp',
            'text/csv' => 'csv',
            'application/msword' => 'doc',
            'application/vnd.ms-word.document.macroEnabled.12' => 'docm',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'image/gif' => 'gif',
            'text/htm' => 'htm',
            'text/html' => 'html',
            'application/pdf' => 'pdf',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.ms-powerpoint.presentation.macroEnabled.12' => 'pptm',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.ms-excel.sheet.macroEnabled.12' => 'xlsm',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'text/xml' => 'xml',
            'application/x-zip-compressed' => 'zip',
            'application/json' => 'json',
            'application/vnd.rar' => 'rar',
            'application/x-tar' => 'tar',

            // Add more mime types as needed
        ];

        // Default extension if not found
        $defaultExtension = 'dat';

        return isset($mimeTypes[$contentType]) ? '.' . $mimeTypes[$contentType] : $defaultExtension;
    }

}