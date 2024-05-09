<?php 

namespace App\Helpers;

use App\Libraries\EmailRules;
use Config\Services;
use App\Libraries\Emails;
class FilterHelper {
    function playFilterRulesForDepartment($department_id, $ticket, $message, $subject, $body) {
        $rules = new EmailRules();
        $department_rules = $rules->getAllForDepartment($department_id);
        if (!empty($department_rules)) {
            foreach ($department_rules as $rule) {
                $this->checkRule($ticket, $message, $subject, $body, $rule);
            }
        }
    }

    private function checkRule($ticket, $message, $subject, $body, $rule) {
        $rule_result = false;
        if ($rule->type == 0)  {
            $rule_result = $this->checkRuleCondition($subject, $rule->rule_value, $rule->rule_condition);
        }
        if ($rule->type == 1) {
            $rule_result = $this->checkRuleCondition($body, $rule->rule_value, $rule->rule_condition);
        }
        if ($rule_result) {
            $this->takeRuleAction($rule->rule_action, $rule->outcome_id, $rule->outcome, $ticket, $message);
        }
    }

    private function checkRuleCondition($text, $value, $rule_condition) {
        switch ($rule_condition) {
            case 0:
                return strpos($text, $value) !== false;
            case 1:
                return strpos($text, $value) === false;
            case 2:
                return strtolower($text) == strtolower($value);
            case 3:
                return strtolower($text) != strtolower($value);
        }
    }

    private function takeRuleAction($action, $outcome_id, $outcome, $ticket, $message) {
        $tickets = Services::tickets();
        switch ($action) {
            case 0:
                $emails = new Emails();
                $emails->sendFromTemplate('send_copy_to', [
                    '%email%' => $outcome,
                    '%ticket_id%' => $ticket->id,
                    '%ticket_subject%' => $ticket->subject,
                    '%ticket_department%' => $ticket->department_name,
                    '%ticket_status%' => lang('open'),
                    '%ticket_priority%' => $ticket->priority_name,
                    '%original_message%'=> $message
                ], $outcome, $ticket->department_id);
                break;
            case 1:
                $tickets->updateTicket(['agent_id' => $outcome_id], $ticket->id);
                break;
            case 2:
                $tickets->updateTicket(['priority_id' => $outcome_id], $ticket->id);
                break;
        }
    }
}