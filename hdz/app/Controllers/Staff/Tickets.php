<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */

namespace App\Controllers\Staff;


use App\Controllers\BaseController;
use App\Helpers\FilterHelper;
use App\Libraries\Emails;
use App\Models\CannedModel;
use Config\Services;
use App\Libraries\ChangeLogs;


class Tickets extends BaseController
{
    public function selectDepartment()
    {

        if ($this->request->getPost('do') == 'submit') {
            $departments = Services::departments();
            $validation = Services::validation();
            $validation->setRule('department', 'department', 'required|is_natural_no_zero|is_not_unique[departments.id]');
            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = lang('Admin.error.selectValidDepartment');
            } elseif (!$department = $departments->getByID($this->request->getPost('department'))) {
                $error_msg = lang('Admin.error.selectValidDepartment');
            } else {
                return redirect()->route('staff_ticket_new_department', [$department->id, url_title($department->name)]);
            }
        }
        return view('staff/ticket_departments', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
        ]);
    }

    public function manage($page)
    {
        $tickets = new \App\Libraries\Tickets();

        if ($this->request->getPost('action')) {
            if (!is_array($this->request->getPost('ticket_id'))) {
                $error_msg = lang('Admin.error.noItemsSelected');
            } else {
                foreach ($this->request->getPost('ticket_id') as $ticket_id) {
                    if (is_numeric($ticket_id)) {
                        if ($this->request->getPost('action') == 'remove') {
                            $tickets->deleteTicket($ticket_id);
                        } elseif ($this->request->getPost('action') == 'update') {
                            if (is_numeric($this->request->getPost('department'))) {
                                if (Services::departments()->isValid($this->request->getPost('department'))) {
                                    $tickets->updateTicket([
                                        'department_id' => $this->request->getPost('department')
                                    ], $ticket_id);
                                }
                            }
                            if (is_numeric($this->request->getPost('status'))) {
                                if (array_key_exists($this->request->getPost('status'), $tickets->statusList())) {
                                    $tickets->updateTicket([
                                        'status' => $this->request->getPost('status')
                                    ], $ticket_id);
                                }
                            }
                            if (is_numeric($this->request->getPost('priority'))) {
                                if ($tickets->existPriority($this->request->getPost('priority'))) {
                                    $tickets->updateTicket([
                                        'priority_id' => $this->request->getPost('priority')
                                    ], $ticket_id);
                                }
                            }
                        }
                    }
                }
                return redirect()->to(current_url(true));
            }
        }

        if ($this->session->has('ticket_error')) {
            $error_msg = $this->session->getFlashdata('ticket_error');
        }
        $result = $tickets->staffTickets($page);
        return view('staff/tickets', [
            'departments' => $this->staff->getDepartments(),
            'agents' => $this->staff->getAgents('fullname'),
            'statuses' => $tickets->statusList(),
            'tickets_result' => $result['result'],
            'priorities' => $tickets->getPriorities(),
            'pager' => $result['pager'],
            'page_type' => $page,
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function view($ticket_id)
    {
        $tickets = Services::tickets();
        $changelogs = new ChangeLogs();
        if (!$ticket = $tickets->getTicket(['id' => $ticket_id])) {
            $this->session->setFlashdata('ticket_error', lang('Admin.error.ticketNotFound'));
            return redirect()->route('staff_tickets');
        }
        $departmentData = $this->staff->getDepartments();
        if (!is_null($departmentData)) {
            $key = array_search($ticket->department_id, array_column($this->staff->getDepartments(), 'id'));
            if (!is_numeric($key)) {
                $this->session->setFlashdata('ticket_error', lang('Admin.error.ticketNotPermission'));
                return redirect()->route('staff_tickets');
            }
        }
        $attachments = Services::attachments();
        #Download
        if ($this->request->getGet('download')) {
            if (!$file = $attachments->getRow(['id' => $this->request->getGet('download'), 'ticket_id' => $ticket->id])) {
                return view('client/error', [
                    'title' => lang('Client.error.fileNotFound'),
                    'body' => lang('Client.error.fileNotFoundMsg'),
                    'footer' => ''
                ]);
            }
            return $attachments->download($file);
        } elseif (is_numeric($this->request->getGet('delete_file'))) {
            if (
                !$file = $attachments->getRow([
                    'id' => $this->request->getGet('delete_file'),
                    'ticket_id' => $ticket->id
                ])
            ) {
                return redirect()->to(current_url());
            } else {
                $attachments->deleteFile($file);
                $changelogs->create($this->staff->getData('id'), $ticket->id, $this->staff->getData('fullname'), 'Admin.actions.attachmentRemoved');
                $this->session->setFlashdata('ticket_update', lang('Admin.tickets.attachmentRemoved'));
                return redirect()->to(current_url());
            }
        }
        //Update Information
        if ($this->request->getPost('do') == 'update_information') {
            $validation = Services::validation();
            $validation->setRules([
                'department' => 'required|is_natural_no_zero|is_not_unique[departments.id]',
                'status' => 'required|is_natural|in_list[' . implode(',', array_keys($tickets->statusList())) . ']',
                'priority' => 'required|is_natural_no_zero|is_not_unique[priority.id]',
                'agent' => 'required'
            ], [
                'department' => [
                    'required' => lang('Admin.error.invalidDepartment'),
                    'is_natural_no_zero' => lang('Admin.error.invalidDepartment'),
                    'is_not_unique' => lang('Admin.error.invalidDepartment'),
                ],
                'status' => [
                    'required' => lang('Admin.error.invalidStatus'),
                    'is_natural' => lang('Admin.error.invalidStatus'),
                    'in_list' => lang('Admin.error.invalidStatus'),
                ],
                'priority' => [
                    'required' => lang('Admin.error.invalidPriority'),
                    'is_natural_no_zero' => lang('Admin.error.invalidPriority'),
                    'is_not_unique' => lang('Admin.error.invalidPriority')
                ]
            ]);
            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } else {
                if ($this->request->getPost('department') != $ticket->department_id) {
                    $changelogs->create($this->staff->getData('id'), $ticket->id, $this->staff->getData('fullname'), 'Admin.actions.departmentChanged');
                }
                if ($this->request->getPost('agent') != $ticket->agent_id) {
                    $changelogs->create($this->staff->getData('id'), $ticket->id, $this->staff->getData('fullname'), 'Admin.actions.agentChanged');
                    $emails = new Emails();
                    $newAgent = $this->staff->getAgentById($this->request->getPost('agent'));
                    $emails->sendFromTemplate('staff_ticketnotification', [
                        '%staff_name%' => $newAgent->fullname,
                        '%ticket_id%' => $ticket->id,
                        '%ticket_subject%' => $ticket->subject,
                        '%ticket_department%' => $ticket->department_name,
                        '%ticket_status%' => lang('open'),
                        '%ticket_priority%' => $ticket->priority_name,
                        '%original_message%'=> $tickets->getFirstMessage($ticket->id)->message
                    ], $newAgent->email, $ticket->department_id);
                }
                if ($this->request->getPost('status') != $ticket->status) {
                    $changelogs->create($this->staff->getData('id'), $ticket->id, $this->staff->getData('fullname'), 'Admin.actions.statusChanged');
                }
                if ($this->request->getPost('priority') != $ticket->priority_id) {
                    $changelogs->create($this->staff->getData('id'), $ticket->id, $this->staff->getData('fullname'), 'Admin.actions.priorityChanged');
                }
                $tickets->updateTicket([
                    'department_id' => $this->request->getPost('department'),
                    'status' => $this->request->getPost('status'),
                    'priority_id' => $this->request->getPost('priority'),
                    'agent_id' => $this->request->getPost('agent'),
                ], $ticket->id);
                $this->session->setFlashdata('ticket_update', 'Ticket updated.');
                return redirect()->to(current_url());
            }
        }
        //Reply Ticket
        elseif ($this->request->getPost('do') == 'reply') {
            $validation = Services::validation();
            $validation->setRule('message', 'message', 'required', [
                'required' => lang('Admin.error.enterMessage')
            ]);

            if ($this->settings->config('ticket_attachment')) {
                $max_size = $this->settings->config('ticket_file_size') * 1024;
                $allowed_extensions = unserialize($this->settings->config('ticket_file_type'));
                $allowed_extensions = implode(',', $allowed_extensions);
                $validation->setRule('attachment', 'attachment', 'ext_in[attachment,' . $allowed_extensions . ']|max_size[attachment,' . $max_size . ']', [
                    'ext_in' => lang('Admin.error.fileNotAllowed'),
                    'max_size' => lang_replace('Admin.error.fileBig', ['%size%' => number_to_size($max_size * 1024, 2)])
                ]);
            }

            $cc = $this->request->getPost('cc');

            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } else {
                if ($this->settings->config('ticket_attachment')) {
                    if ($files_uploaded = $attachments->ticketUpload()) {
                        $files = $files_uploaded;
                    }
                }
                //Message
                $message = $this->request->getPost('message') . $this->staff->getData('signature');
                $message_id = $tickets->addMessage($ticket->id, $message, $this->staff->getData('id'));


                //File
                if (isset($files)) {
                    $attachments->addTicketFiles($ticket->id, $message_id, $files);
                }
                $tickets->updateTicketReply($ticket->id, $ticket->status, true);
                if (!defined('HDZDEMO')) {
                    $tickets->replyTicketNotification($ticket, $message, $cc, (isset($files) ? $files : null));
                    if (isset($ticket->agent_id) && $this->staff->getData('id') != $ticket->agent_id) {
                        $tickets->messageAddedToTicket($ticket, $message, $this->staff->getData('fullname'));
                    }
                }
                $changelogs->create($this->staff->getData('id'), $ticket->id, $this->staff->getData('fullname'), 'Admin.actions.replySent');
                $this->session->setFlashdata('ticket_update', lang('Admin.tickets.messageSent'));
                return redirect()->to(previous_url());
            }
        } elseif ($this->request->getPost('do') == 'delete_note') {
            $validation = Services::validation();
            $validation->setRule('note_id', 'note_id', 'required|is_natural_no_zero');
            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = lang('Admin.tickets.invalidRequest');
            } elseif (!$note = $tickets->getNote($this->request->getPost('note_id'))) {
                $error_msg = lang('Admin.tickets.invalidRequest');
            } elseif ($this->staff->getData('admin') == 1 || $this->staff->getData('id') == $note->staff_id) {
                $tickets->deleteNote($ticket->id, $this->request->getPost('note_id'));
                $changelogs->create($this->staff->getData('id'), $ticket->id, $this->staff->getData('fullname'), 'Admin.actions.noteRemoved');
                $this->session->setFlashdata('ticket_update', lang('Admin.tickets.noteRemoved'));
                return redirect()->to(current_url());
            } else {
                $error_msg = lang('Admin.tickets.invalidRequest');
            }
        } elseif ($this->request->getPost('do') == 'edit_note') {
            $validation = Services::validation();
            $validation->setRule('note_id', 'note_id', 'required|is_natural_no_zero');
            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = lang('Admin.tickets.invalidRequest');
            } elseif ($this->request->getPost('new_note') == '') {
                $error_msg = lang('Admin.tickets.enterNote');
            } elseif (!$note = $tickets->getNote($this->request->getPost('note_id'))) {
                $error_msg = lang('Admin.tickets.invalidRequest');
            } elseif ($this->staff->getData('admin') == 1 || $this->staff->getData('id') == $note->staff_id) {
                $tickets->updateNote($this->request->getPost('new_note'), $note->id);
                $changelogs->create($this->staff->getData('id'), $ticket->id, $this->staff->getData('fullname'), 'Admin.actions.noteUpdated');
                $this->session->setFlashdata('ticket_update', lang('Admin.tickets.noteUpdated'));
                return redirect()->to(current_url());
            } else {
                $error_msg = lang('Admin.tickets.invalidRequest');
            }
        } elseif ($this->request->getPost('do') == 'save_notes') {
            if ($this->request->getPost('noteBook') == '') {
                $error_msg = lang('Admin.tickets.enterNote');
            } else {
                $tickets->addNote($ticket->id, $this->staff->getData('id'), $this->request->getPost('noteBook'));
                $changelogs->create($this->staff->getData('id'), $ticket->id, $this->staff->getData('fullname'), 'Admin.actions.noteAdded');
                $this->session->setFlashdata('ticket_update', lang('Admin.tickets.notesSaved'));
                return redirect()->to(current_url());
            }
        } elseif ($this->request->getPost('do') == 'edit_ticket_text') { 
            $validation = Services::validation();
            $validation->setRule('ticket_id', 'ticket_id', 'required|is_natural_no_zero');
            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = lang('Admin.tickets.invalidRequest');
            } elseif ($this->request->getPost('new_text') == '') {
                $error_msg = lang('Admin.tickets.enterText');
            } elseif (!$ticketmessage = $tickets->getTicketMessageById($this->request->getPost('ticket_id'))) {
                $error_msg = lang('Admin.tickets.invalidRequest');
            } elseif ($this->staff->getData('admin') < 2 || $this->staff->getData('id') == $ticketmessage->staff_id) {
                $tickets->updateTicketMessage(['message' => $this->request->getPost('new_text')], $ticketmessage->id);
                $this->session->setFlashdata('ticket_update', lang('Admin.tickets.ticketUpdated'));
                $changelogs->create($this->staff->getData('id'), $ticket->id, $this->staff->getData('fullname'), 'Admin.actions.textChanged');
                return redirect()->to(current_url());
            } else {
                $error_msg = lang('Admin.tickets.invalidRequest');
            }
        }

        if ($this->session->has('ticket_update')) {
            $success_msg = $this->session->getFlashdata('ticket_update');
        }

        $messages = $tickets->getMessages($ticket->id);
        if (defined('HDZDEMO')) {
            $ticket->email = '[Hidden in demo]';
        }
        $next_ticket = $tickets->getNextTicket($ticket->id, $ticket->department_id);
        $prev_ticket = $tickets->getPreviousTicket($ticket->id, $ticket->department_id);
        return view('staff/ticket_view', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => isset($success_msg) ? $success_msg : null,
            'ticket' => $ticket,
            'canned_response' => $tickets->getCannedList(),
            'message_result' => $messages['result'],
            'pager' => $messages['pager'],
            'departments_list' => Services::departments()->getAll(),
            'agents' => Services::staff()->getAgents(),
            'ticket_statuses' => $tickets->statusList(),
            'ticket_priorities' => $tickets->getPriorities(),
            'kb_selector' => Services::kb()->kb_article_selector(),
            'notes' => $tickets->getNotes($ticket->id),
            'changelogs' => $changelogs->getAll($ticket->id),
            'next_ticket' => isset($next_ticket) ? $next_ticket : null,
            'previous_ticket' => isset($prev_ticket) ? $prev_ticket : null,
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function create($department_id)
    {
        $departments = Services::departments();
        $changelogs = new ChangeLogs();
        $filter_helper = new FilterHelper();
        if (!$department = $departments->getByID($department_id)) {
            return redirect()->route('staff_ticket_new');
        }
        $tickets = Services::tickets();
        if ($this->request->getPost('do') == 'submit') {
            $validation = Services::validation();
            $validation->setRules([
                'email' => 'required|valid_email',
                'priority' => 'required|is_natural_no_zero|is_not_unique[priority.id]',
                'status' => 'required|is_natural|in_list[' . implode(',', array_keys($tickets->statusList())) . ']',
                'subject' => 'required',
                'message' => 'required'
            ], [
                'email' => [
                    'required' => lang('Admin.error.enterValidEmail'),
                    'valid_email' => lang('Admin.error.enterValidEmail')
                ],
                'priority' => [
                    'required' => lang('Admin.error.invalidPriority'),
                    'is_natural_no_zero' => lang('Admin.error.invalidPriority'),
                    'is_not_unique' => lang('Admin.error.invalidPriority'),
                ],
                'status' => [
                    'required' => lang('Admin.error.invalidStatus'),
                    'is_natural' => lang('Admin.error.invalidStatus'),
                    'in_list' => lang('Admin.error.invalidStatus'),
                ],
                'subject' => [
                    'required' => lang('Admin.error.enterSubject'),
                ],
                'message' => [
                    'required' => lang('Admin.error.enterMessage'),
                ]
            ]);

            $customFieldList = array();
            if ($customFields = $tickets->customFieldsFromDepartment($department->id)) {
                foreach ($customFields as $customField) {
                    $value = '';
                    if (in_array($customField->type, ['text', 'textarea', 'password', 'email', 'date'])) {
                        $value = $this->request->getPost('custom')[$customField->id];
                    } elseif (in_array($customField->type, ['radio', 'select'])) {
                        $options = explode("\n", $customField->value);
                        $value = $options[$this->request->getPost('custom')[$customField->id]];
                    } elseif ($customField->type == 'checkbox') {
                        $options = explode("\n", $customField->value);
                        $checkbox_list = array();
                        if (is_array($this->request->getPost('custom')[$customField->id])) {
                            foreach ($this->request->getPost('custom')[$customField->id] as $k) {
                                $checkbox_list[] = $options[$k];
                            }
                            $value = implode(', ', $checkbox_list);
                        }
                    }
                    $customFieldList[] = [
                        'title' => $customField->title,
                        'value' => $value
                    ];
                    if ($customField->required == '1') {
                        $validation->setRule('custom.' . $customField->id, $customField->title, 'required');
                    }
                }
            }

            if ($this->settings->config('ticket_attachment')) {
                $max_size = $this->settings->config('ticket_file_size') * 1024;
                $allowed_extensions = unserialize($this->settings->config('ticket_file_type'));
                $allowed_extensions = implode(',', $allowed_extensions);
                $validation->setRule('attachment', 'attachment', 'ext_in[attachment,' . $allowed_extensions . ']|max_size[attachment,' . $max_size . ']', [
                    'ext_in' => lang('Admin.error.fileNotAllowed'),
                    'max_size' => lang_replace('Admin.error.fileBig', ['%size%' => number_to_size($max_size * 1024, 2)])
                ]);
            }

            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } elseif (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $attachments = Services::attachments();
                if ($this->settings->config('ticket_attachment')) {
                    if ($uploaded_files = $attachments->ticketUpload()) {
                        $files = $uploaded_files;
                    }
                }
                $name = ($this->request->getPost('fullname') == '') ? $this->request->getPost('email') : $this->request->getPost('fullname');
                $client_id = $this->client->getClientID($name, $this->request->getPost('email'));
                $ticket_id = $tickets->createTicket($client_id, $this->request->getPost('subject'), $department_id, $this->request->getPost('priority'), $this->request->getPost('agent'));
                $tickets->updateTicket([
                    'custom_vars' => serialize($customFieldList)
                ], $ticket_id);
                $message = $this->request->getPost('message') . $this->staff->getData('signature');
                $message_id = $tickets->addMessage($ticket_id, $message, $this->staff->getData('id'));
                $tickets->updateTicket([
                    'last_replier' => $this->staff->getData('id'),
                    'status' => $this->request->getPost('status')
                ], $ticket_id);
                //File
                if (isset($files)) {
                    $attachments->addTicketFiles($ticket_id, $message_id, $files);
                }

                $ticket = $tickets->getTicket(['id' => $ticket_id]);
                $tickets->staffNotification($ticket);
                $changelogs->create($this->staff->getData('id'), $ticket->id, $this->staff->getData('fullname'), 'Admin.actions.ticketCreated');
                $this->session->setFlashdata('form_success', 'Ticket has been created and client was notified.');
                $filter_helper->playFilterRulesForDepartment($department_id, $ticket, $this->request->getPost('message'), $this->request->getPost('subject'), $this->request->getPost('message'));
                return redirect()->route('staff_ticket_view', [$ticket_id]);
            }
        }


        return view('staff/ticket_new', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => isset($success_msg) ? $success_msg : null,
            'canned_response' => $tickets->getCannedList(),
            'department' => $department,
            'agents' => $departments->getAllActiveAgentsForDepartment($department_id),
            'ticket_statuses' => $tickets->statusList(),
            'ticket_priorities' => $tickets->getPriorities(),
            'kb_selector' => Services::kb()->kb_article_selector(),
            'customFields' => $tickets->customFieldsFromDepartment($department->id),
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function cannedResponses()
    {
        $tickets = Services::tickets();
        if ($this->request->getPost('do') == 'remove') {
            if (!$canned = $tickets->getCannedResponse($this->request->getPost('msgID'))) {
                $error_msg = lang('Admin.error.invalidCannedResponse');
            } elseif (!$this->staff->getData('admin') && $canned->staff_id != $this->staff->getData('id')) {
                $error_msg = lang('Admin.error.invalidCannedResponse');
            } elseif (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $tickets->deleteCanned($canned->id);
                $this->session->setFlashdata('canned_update', 'Canned response has been removed.');
                return redirect()->route('staff_canned');
            }
        }

        if ($this->request->getGet('action') && is_numeric($this->request->getGet('msgID'))) {
            if (!$canned = $tickets->getCannedResponse($this->request->getGet('msgID'))) {
                $error_msg = lang('Admin.error.invalidCannedResponse');
            } elseif (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $cannedModel = new CannedModel();
                switch ($this->request->getGet('action')) {
                    case 'move_up':
                        if ($canned->position > 1) {
                            $cannedModel->protect(false);
                            $cannedModel->set('position', $canned->position)
                                ->where('position', ($canned->position - 1))
                                ->update();
                            $cannedModel->protect(true);
                            $tickets->changeCannedPosition(($canned->position - 1), $canned->id);
                        }
                        break;
                    case 'move_down':
                        if ($canned->position < $tickets->lastCannedPosition()) {
                            $cannedModel->protect(false);
                            $cannedModel->set('position', $canned->position)
                                ->where('position', ($canned->position + 1))
                                ->update();
                            $cannedModel->protect(true);
                            $tickets->changeCannedPosition(($canned->position + 1), $canned->id);
                        }
                        break;
                }
                return redirect()->route('staff_canned');
            }
        }
        if ($this->session->has('canned_update')) {
            $success_msg = $this->session->getFlashdata('canned_update');
        }
        return view('staff/canned_manage', [
            'cannedList' => $tickets->getCannedList(),
            'lastCannedPosition' => $tickets->lastCannedPosition(),
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => isset($success_msg) ? $success_msg : null,
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function editCannedResponses($canned_id)
    {
        $tickets = Services::tickets();
        if (!$canned = $tickets->getCannedResponse($canned_id)) {
            return redirect()->route('staff_canned');
        }
        if ($this->request->getPost('do') == 'submit') {
            $validation = Services::validation();
            $validation->setRules([
                'title' => 'required',
                'message' => 'required'
            ], [
                'title' => [
                    'required' => lang('Admin.error.enterTitle'),
                ],
                'message' => [
                    'required' => lang('Admin.error.enterMessage')
                ]
            ]);
            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } elseif (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $tickets->updateCanned([
                    'title' => esc($this->request->getPost('title')),
                    'message' => $this->request->getPost('message'),
                    'last_update' => time()
                ], $canned_id);
                $this->session->setFlashdata('canned_update', 'Canned response has been updated.');
                return redirect()->to(current_url());
            }
        }

        if ($this->session->has('canned_update')) {
            $success_msg = $this->session->getFlashdata('canned_update');
        }
        return view('staff/canned_form', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => isset($success_msg) ? $success_msg : null,
            'canned' => $canned,
            'staff_canned' => ($canned->staff_id > 0 ? $this->staff->getRow(['id' => $canned->staff_id], 'fullname') : null),
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function newCannedResponse()
    {
        if ($this->request->getPost('do') == 'submit') {
            $validation = Services::validation();
            $validation->setRules([
                'title' => 'required',
                'message' => 'required'
            ], [
                'title' => [
                    'required' => lang('Admin.error.enterTitle'),
                ],
                'message' => [
                    'required' => lang('Admin.error.enterMessage')
                ]
            ]);
            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } elseif (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $tickets = Services::tickets();
                $tickets->insertCanned($this->request->getPost('title'), $this->request->getPost('message'));
                $this->session->setFlashdata('canned_update', 'Canned response has been inserted.');
                return redirect()->route('staff_canned');
            }
        }

        return view('staff/canned_form', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('canned_update') ? $this->session->getFlashdata('canned_update') : null,
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }
}