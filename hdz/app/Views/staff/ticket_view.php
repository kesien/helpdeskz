<?php
/**
 * @var $this \CodeIgniter\View\View
 * @var $pager \CodeIgniter\Pager\Pager
 */
$this->extend('staff/template');
$this->section('content');
?>
<!-- Page Header -->
<div class="page-header row no-gutters py-4">
    <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
        <span class="text-uppercase page-subtitle">
            <?php echo lang('Admin.tickets.menu'); ?>
        </span>
        <h3 class="page-title">
            <?php echo '[#' . $ticket->id . '] ' . esc($ticket->subject); ?>
        </h3>
    </div>
</div>
<!-- End Page Header -->
<div class="row justify-content-end mb-3">
    <?php
    if (isset($previous_ticket)) {
        echo '<div class="col-2"><a href="' . site_url(route_to('staff_ticket_view', $previous_ticket->id)) . '" class="btn btn-primary d-inline-block w-100">' . lang('Admin.previous-ticket') . '</a></div>';
    }
    if (isset($next_ticket)) {
        echo '<div class="col-2"><a href="' . site_url(route_to('staff_ticket_view', $next_ticket->id)) . '" class="btn btn-primary d-inline-block w-100">' . lang('Admin.next-ticket') . '</a></div>';
    }
    ?>
</div>
<div class="card mb-3">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs border-bottom" id="myTab" role="tablist">
            <?php if (staff_data('admin') != 2): ?>
                <li class="nav-item">
                    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-selected="true">
                        <?php echo lang('Admin.form.general'); ?>
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link <?php if (staff_data('admin') == 2) {
                    echo 'active';
                } ?>" id="reply-tab" data-toggle="tab" href="#replyBox" role="tab" aria-selected="false">
                    <?php echo lang('Admin.form.reply'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="notes-tab" data-toggle="tab" href="#notesBox" role="tab" aria-selected="false">
                    <?php echo lang('Admin.tickets.notes'); ?>
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content mb-3" id="myTabContent">
            <?php if (staff_data('admin') != 2): ?>
                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                    <div class="pb-3">
                        <div class="text-muted">
                            <i class="far fa-calendar"></i>
                            <?php echo lang_replace('Admin.form.createdOn', ['%date%' => dateFormat($ticket->date)]); ?>
                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                            <i class="far fa-calendar"></i>
                            <?php echo lang_replace('Admin.form.updatedOn', ['%date%' => dateFormat($ticket->last_update)]); ?>
                        </div>
                    </div>
                    <?php echo form_open('', [], ['do' => 'update_information']); ?>
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>
                                    <?php echo lang('Admin.form.department'); ?>
                                </label>
                                <select name="department" class="form-control custom-select" id="ticket_department">
                                    <?php
                                    if (isset($departments_list)) {
                                        foreach ($departments_list as $item) {
                                            if ($item->id == $ticket->department_id) {
                                                echo '<option value="' . $item->id . '" selected>' . $item->name . '</option>';
                                            } else {
                                                echo '<option value="' . $item->id . '">' . $item->name . '</option>';
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>
                                    <?php echo lang('Admin.form.agent'); ?>
                                </label>
                                <select name="agent" id="agent_select" class="form-control custom-select">
                                    <?php
                                    echo '<option value="0"' . ((!isset($ticket->agent_id) || $ticket->agent_id == 0) ? " selected" : "") . ' disabled>'. lang('Admin.form.none') .'</option>' ;
                                    if (isset($agents)) {
                                        foreach ($agents as $item) {
                                            if ($item->id == $ticket->agent_id) {
                                                echo '<option value="' . $item->id . '" selected>' . $item->fullname . '</option>';
                                            } else {
                                                echo '<option value="' . $item->id . '">' . $item->fullname . '</option>';
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                                <small class="text-muted" id="agent_select_hint"><?php echo lang('Admin.form.noAgents') ?></small>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>
                                    <?php echo lang('Admin.form.status'); ?>
                                </label>
                                <select name="status" class="form-control custom-select">
                                    <?php
                                    foreach ($ticket_statuses as $k => $v) {
                                        if ($k == $ticket->status) {
                                            echo '<option value="' . $k . '" selected>' . lang('Admin.form.' . $v) . '</option>';
                                        } else {
                                            echo '<option value="' . $k . '">' . lang('Admin.form.' . $v) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>
                                    <?php echo lang('Admin.form.priority'); ?>
                                </label>
                                <select name="priority" class="form-control custom-select">
                                    <?php
                                    if (isset($ticket_priorities)) {
                                        foreach ($ticket_priorities as $item) {
                                            if ($item->id == $ticket->priority_id) {
                                                echo '<option value="' . $item->id . '" selected>' . $item->name . '</option>';
                                            } else {
                                                echo '<option value="' . $item->id . '">' . $item->name . '</option>';
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php
                    if ($ticket->custom_vars != '') {
                        $custom_vars = unserialize($ticket->custom_vars);
                        if (is_array($custom_vars)) {
                            echo '<div class="row">';
                            foreach ($custom_vars as $item) {
                                ?>
                                <div class="col-md-6 col-lg-3">
                                    <div class="form-group">
                                        <label>
                                            <?php echo $item['title']; ?>
                                        </label>
                                        <input type="text" value="<?php echo esc($item['value']); ?>" class="form-control" readonly>
                                    </div>
                                </div>
                                <?php
                            }
                            echo '</div>';
                        }
                    }
                    ?>
                    <div class="form-group">
                        <button class="btn btn-primary">
                            <?php echo lang('Admin.form.save'); ?>
                        </button>
                    </div>

                    <?php echo form_close(); ?>
                </div>
            <?php endif; ?>
            <div class="tab-pane fade <?php if (staff_data('admin') == 2) {
                echo 'show active';
            } ?>" id="replyBox" role="tabpanel" aria-labelledby="reply-tab">
                <?php
                echo form_open_multipart('', [], ['do' => 'reply']);
                ?>
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">
                        <?php echo lang('Admin.form.to'); ?>
                    </label>
                    <div class="col">
                        <input type="text" class="form-control"
                            value="<?php echo esc($ticket->fullname . ' <' . $ticket->email . '>'); ?>" readonly>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">
                        <?php echo lang('Admin.form.cc'); ?>
                    </label>
                    <div class="col">
                        <input type="text" name="cc" class="form-control"
                            value="">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">
                        <?php echo lang('Admin.form.quickInsert'); ?>
                    </label>
                    <div class="col">
                        <div class="row">
                            <div class="col-md-6">
                                <select name="canned" id="cannedList" onchange="addCannedResponse(this.value);"
                                    class="custom-select">
                                    <option value="">
                                        <?php echo lang('Admin.cannedResponses.menu'); ?>
                                    </option>
                                    <?php
                                    if (isset($canned_response)) {
                                        foreach ($canned_response as $item) {
                                            echo '<option value="' . $item->id . '">' . $item->title . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select name="knowledgebase" id="knowledgebaseList"
                                    onchange="addKnowledgebase(this.value);" class="custom-select">
                                    <option value="">
                                        <?php echo lang('Admin.kb.menu'); ?>
                                    </option>
                                    <?php
                                    echo $kb_selector;
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <textarea class="form-control messageBox" name="message" id="messageBox"
                        rows="20"><?php echo set_value('message'); ?></textarea>
                </div>
                <?php
                if (site_config('ticket_attachment')) {
                    ?>
                    <div class="form-group">
                        <label>
                            <?php echo lang('Admin.form.attachments'); ?>
                        </label>
                        <?php
                        for ($i = 1; $i <= site_config('ticket_attachment_number'); $i++) {
                            ?>
                            <div class="row">
                                <div class="col-lg-4 mb-2">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="attachment[]"
                                            id="customFile<?php echo $i; ?>">
                                        <label class="custom-file-label" for="customFile<?php echo $i; ?>"
                                            data-browse="<?php echo lang('Admin.form.browse'); ?>">
                                            <?php echo lang('Admin.form.chooseFile'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <small class="text-muted">
                            <?php echo lang('Admin.form.allowedFiles') . ' *.' . implode(', *.', unserialize(site_config('ticket_file_type'))); ?>
                        </small>
                    </div>
                    <?php
                }
                ?>
                <div class="form-group">
                    <button class="btn btn-primary"><i class="fa fa-paper-plane"></i>
                        <?php echo lang('Admin.form.submit'); ?>
                    </button>
                </div>

                <?php echo form_close(); ?>
            </div>
            <div class="tab-pane fade" id="notesBox" role="tabpanel" aria-labelledby="notes-tab">
                <?php
                if (isset($notes)) {
                    foreach ($notes as $note) {
                        ?>
                        <div class="alert alert-light border mb-3">
                            <div class="alert-heading">
                                by
                                <?php echo $note->fullname; ?>
                                <small>&raquo;
                                    <?php echo dateFormat($note->date); ?>
                                </small>
                                <?php
                                if (staff_data('admin') == 1 || staff_data('id') == $note->staff_id) {
                                    ?>
                                    <div class="float-right">
                                        <?php echo form_open('', ['id' => 'noteForm' . $note->id], ['do' => 'delete_note', 'note_id' => $note->id]); ?>
                                        <button type="button" onclick="editNoteToggle('<?php echo $note->id; ?>');"
                                            class="btn btn-link" title="Edit note" data-toggle="tooltip"><i
                                                class="fa fa-edit"></i></button>
                                        <button type="button" onclick="deleteNote('noteForm<?php echo $note->id; ?>');"
                                            class="btn btn-link" title="Delete note" data-toggle="tooltip"><i
                                                class="fa fa-trash-alt"></i></button>
                                        <?php echo form_close(); ?>
                                    </div>
                                    <?php
                                }
                                ?>
                                <hr>
                            </div>
                            <div id="plainNote<?php echo $note->id; ?>">
                                <p>
                                    <?php echo nl2br($note->message); ?>
                                </p>
                            </div>
                            <div id="inputNote<?php echo $note->id; ?>" style="display: none">
                                <?php echo form_open('', ['id' => 'editNoteForm' . $note->id], ['do' => 'edit_note', 'note_id' => $note->id]); ?>
                                <div class="form-group">
                                    <textarea class="form-control"
                                        name="new_note"><?php echo set_value('new_note', $note->message, false); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-primary">
                                        <?php echo lang('Admin.form.save'); ?>
                                    </button>
                                    <button type="button" onclick="editNoteToggle(<?php echo $note->id; ?>);"
                                        class="btn btn-dark">
                                        <?php echo lang('Admin.form.cancel'); ?>
                                    </button>
                                </div>
                                <?php
                                echo form_close();
                                ?>
                            </div>

                        </div>
                        <?php
                    }
                }
                ?>
                <?php
                echo form_open_multipart('', [], ['do' => 'save_notes']);
                ?>
                <div class="form-group">
                    <textarea class="form-control" name="noteBook"><?php echo set_value('noteBook'); ?></textarea>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary"><i class="fa fa-edit"></i>
                        <?php echo lang('Admin.tickets.addNote'); ?>
                    </button>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>




<?php

if (isset($error_msg)) {
    echo '<div class="alert alert-danger">' . $error_msg . '</div>';
}
if (isset($success_msg)) {
    echo '<div class="alert alert-success">' . $success_msg . '</div>';
}
if (isset($message_result)) {
    foreach ($message_result as $item) {
        ?>
        <div class="card mb-3 <?php echo ($item->customer == 1 ? '' : 'bg-staff'); ?>">
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-2 col-lg-3">
                        <?php
                        if ($item->customer == 1) {
                            ?>
                            <div class="text-center">
                                <div class="mb-3">
                                    <img src="<?php echo user_avatar($ticket->avatar); ?>"
                                        class="user-avatar rounded-circle img-fluid" style="max-width: 100px">
                                </div>
                                <div class="mb-3">
                                    <div>
                                        <?php echo $ticket->fullname; ?>
                                    </div>
                                    <?php
                                    echo '<span class="badge badge-dark">' . lang('Admin.form.user') . '</span>';
                                    ?>
                                </div>
                            </div>
                            <?php
                        } else {
                            $staffData = staff_info($item->staff_id);
                            ?>
                            <div class="text-center">
                                <div class="mb-3">
                                    <img src="<?php echo $staffData['avatar']; ?>" class="user-avatar rounded-circle img-fluid"
                                        style="max-width: 100px">
                                </div>
                                <div class="mb-3">
                                    <div>
                                        <?php echo $staffData['fullname']; ?>
                                    </div>
                                    <?php
                                    echo '<span class="badge badge-primary">' . lang('Admin.form.staff') . '</span>';
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="col">
                        <?php
                                if (staff_data('admin') == 1 || staff_data('id') == $item->staff_id) {
                                    ?>
                        <div class="float-right">
                                        <button type="button" onclick="editTicketToggle('<?php echo $item->id; ?>');"
                                            class="btn btn-link" title="Edit ticket text" data-toggle="tooltip"><i
                                                class="fa fa-edit"></i></button>
                                    </div>
                                    <?php
                                }
                                ?>
                        <div class="mb-3">
                            <div class="text-muted"><i class="fa fa-calendar"></i>
                                <?php echo dateFormat($item->date); ?>
                            </div>
                        </div>

                        <div id="msg_<?php echo $item->id; ?>" class="form-group message-div">
                            <?php echo tidy_repair_html($item->message); //echo ($item->email == 1 ? $item->message : nl2br($item->message)); ?>
                        </div>

                        <div id="inputTicketText_<?php echo $item->id; ?>" style="display: none">
                                <?php echo form_open('', ['id' => 'editTicketForm' . $item->id], ['do' => 'edit_ticket_text', 'ticket_id' => $item->id]); ?>
                                <div class="form-group">
                                    <textarea class="form-control messageBox" 
                                        name="new_text"><?php echo set_value('new_note', $item->message, false); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-primary">
                                        <?php echo lang('Admin.form.save'); ?>
                                    </button>
                                    <button type="button" onclick="editTicketToggle(<?php echo $item->id; ?>);"
                                        class="btn btn-dark">
                                        <?php echo lang('Admin.form.cancel'); ?>
                                    </button>
                                </div>
                                <?php
                                echo form_close();
                                ?>
                            </div>
                        <?php
                        if ($files = ticket_files($ticket->id, $item->id)) {
                            ?>
                            <div class="alert alert-info">
                                <p class="font-weight-bold">
                                    <?php echo lang('Admin.form.attachments'); ?>
                                </p>
                                <?php foreach ($files as $file): ?>
                                    <div class="form-group">
                                        <span class="knowledgebaseattachmenticon"></span>
                                        <i class="fa fa-file-archive-o"></i> <a
                                            href="<?php echo current_url() . '?download=' . $file->id; ?>" target="_blank">
                                            <?php echo $file->name; ?>
                                        </a>

                                        <?php echo number_to_size($file->filesize, 2); ?>
                                        <a href="<?php echo current_url() . '?delete_file=' . $file->id; ?>"
                                            class="btn btn-danger btn-sm"><i class="fa fa-trash"></i>
                                            <?php echo lang('Admin.form.delete'); ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="form-group mt-5">
                            <button class="btn btn-dark btn-sm" type="button"
                                onclick="quoteMessage(<?php echo $item->id; ?>);"><i class="fa fa-quote-left"></i>
                                <?php echo lang('Admin.form.quote'); ?>
                            </button>
                        </div>
                        <div class="border-top mt-3 pt-4 text-right">
                            <?php
                            if ($item->ip != '') {
                                echo '<i class="fa fa-globe"></i> ' . $item->ip;
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
        <?php
    }
}
    echo $pager->links(); ?>
    <div class="card mb-3">

    <div class="card-header">
        <div class="row">
            <div class="col-sm-12">
                <?php
                    echo lang('Admin.changelogs.title')
                ?>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th style="width: 15%">
                        <?php echo lang('Admin.changelogs.date'); ?>
                    </th>
                    <th style="width: 10%">
                        <?php echo lang('Admin.changelogs.user'); ?>
                    </th>
                    <th>
                        <?php echo lang('Admin.changelogs.action'); ?>
                    </th>
                </tr>
            </thead>
            <?php if (!$changelogs): ?>
                <tr>
                    <td colspan="3">
                        <i>
                            <?php echo lang('Admin.changelogs.noChangelogsFound'); ?>
                        </i>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($changelogs as $changelog): ?>
                    <tr>
                        <td class="font-weight-bold"><?php echo dateFormat($changelog->date); ?></td>
                        <td><?php echo $changelog->staff_name; ?></td>
                        <td><?php echo lang($changelog->action); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
    </div>
    <?php

$this->endSection();
$this->section('script_block');
include __DIR__ . '/tinymce.php';
?>
<script>
    $(document).ready(function () {
        bsCustomFileInput.init();
    });
    <?php
    if (isset($canned_response)) {
        echo 'var canned_response = ' . json_encode($canned_response) . ';';
    }
    ?>
    var KBUrl = '<?php echo site_url(route_to('staff_ajax_kb')); ?>';
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })

    function deleteNote(noteFormId) {
        Swal.fire({

            text: langNoteConfirmation,
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: langDelete,
            cancelButtonText: langCancel,
            cancelButtonColor: '#d33',
        }).then((result) => {
            if (result.value) {
                $('#' + noteFormId).submit();
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                return false;
            }
        });
    }

    function editNoteToggle(noteId) {
        $('#plainNote' + noteId).toggle();
        $('#inputNote' + noteId).toggle();
    }

    function editTicketToggle(ticketId) {
        $('#msg_' + ticketId).toggle();
        $('#inputTicketText_' + ticketId).toggle();
    }

    <?php
        $agent_info = array_map(function($a) {
            return new class($a->id, $a->fullname, unserialize($a->department)) {
                public $id;
                public $fullname;
                public $departments;

                public function __construct($id, $fullname, $department) {
                    $this->id = $id;
                    $this->fullname = $fullname;
                    $this->departments = $department ? $department : array();
                }
            };
        }, $agents);
    ?>
    let agents = <?php echo json_encode($agent_info); ?>;
    let groupedByDepartment = {};
    agents.forEach(agent => {
        agent.departments.forEach(department => {
            if (!groupedByDepartment[department]) {
                groupedByDepartment[department] = [];
            }
            groupedByDepartment[department].push(agent);
        })
    });
    departmentSelectionChanged();
    $("#ticket_department").on('change', departmentSelectionChanged)
    function departmentSelectionChanged() {
        $("#agent_select_hint").hide();
        $("#agent_select").empty();
        let option = $('<option></option>').attr("value", 0).text("<?php echo lang('Admin.form.none'); ?>");
        $("#agent_select").append(option);
        if (Object.hasOwn(groupedByDepartment, [$("#ticket_department").val()])) {
            for (let department in groupedByDepartment) {
                if (department == $("#ticket_department").val()) {
                    groupedByDepartment[department].forEach(a => {
                        let option = $('<option></option>').attr("value", a.id).text(a.fullname);
                         $("#agent_select").append(option);
                         if (a.id == <?php echo (isset($ticket->agent_id) ? $ticket->agent_id : 0) ?>) {
                            $("#agent_select").val(a.id);
                         }
                    })
                }
            }
        } else {
            $("#agent_select_hint").show();
        }
    }
</script>
<?php
$this->endSection();