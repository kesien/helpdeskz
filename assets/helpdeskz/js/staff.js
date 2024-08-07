$(function () {
  //File
  bsCustomFileInput.init();
  //Checkboxes
  $("#select_all").on("click", function () {
    if ($(this).prop("checked") === true) {
      $(".select_item").prop("checked", true);
    } else {
      $(".select_item").prop("checked", false);
    }
  });
  //DatePicker
  $(".datepicker").daterangepicker(
    {
      autoUpdateInput: false,
    },
    function (start_date, end_date) {
      this.element.val(
        start_date.format("MM/DD/YYYY") + " - " + end_date.format("MM/DD/YYYY")
      );
    }
  );
  //pointer
  $("span.pointer").on("click", function () {
    alink = $(this).attr("data-href");
    if (alink !== undefined && alink !== false) {
      location.href = alink;
    }
  });
});

//Tickets Manager
function ticketsPage() {
  $(".select_item,#select_all").change(function () {
    var numberCheckboxes = $(".select_item:checked").length;
    if (numberCheckboxes > 0) {
      $("#ticket_options").show();
    } else {
      $("#ticket_options").hide();
      $("#select_all").prop("checked", false);
    }
  });

  $("#trash_button").on("click", function () {
    Swal.fire({
      text: langRemoveConfirmation,
      type: "warning",
      showCancelButton: true,
      confirmButtonText: langDelete,
      cancelButtonText: langCancel,
      cancelButtonColor: "#d33",
    }).then((result) => {
      if (result.value) {
        $("#ticket_action").val("remove");
        $("#ticketForm").submit();
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        $("#ticket_action").val("update");
        return false;
      }
    });
  });
}

//View ticket
function addCannedResponse(msgid) {
  if (msgid != "") {
    $.each(canned_response, function (i, v) {
      if (v.id == msgid) {
        myMsg = v.message;
        myMsg = myMsg.replace(/{{NAME}}/g, "<?php echo $ticket->fullname;?>");
        myMsg = myMsg.replace(/{{EMAIL}}/g, "<?php echo $ticket->email;?>");
        tinyMCE.get("messageBox").getBody().innerHTML =
          tinyMCE.get("messageBox").getContent() + myMsg;
        $("#cannedList").val("").trigger("change");
      }
    });
  }
}
function quoteMessage(msgID) {
  $("#reply-tab").tab("show");
  var message =
    '<blockquote style="background: #FBFFDB; padding: 5px">' +
    $("#msg_" + msgID).html() +
    "</blockquote>";
  tinyMCE.get("messageBox").getBody().innerHTML =
    tinyMCE.get("messageBox").getContent() + message + "<br>";
}
function addKnowledgebase(_kbID) {
  $("#myTabContent").block({
    message: '<i class="fa fa-refresh fa-spin fa-3x fa-fw"></i>',
    overlayCSS: {
      backgroundColor: "#fff",
      opacity: 0.7,
      cursor: "normal",
    },
    css: {
      border: 0,
      padding: 0,
      backgroundColor: "transparent",
    },
  });
  $.ajax({
    url: KBUrl + "?kb=" + _kbID,
    method: "GET",
    dataType: "json",
    cache: false,
    processData: false,
    contentType: false,
  })
    .done(function (data) {
      tinyMCE.get("messageBox").getBody().innerHTML =
        tinyMCE.get("messageBox").getContent() + data.article;
      $("#knowledgebaseList").val("");
      $("#myTabContent").unblock();
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
      $("#myTabContent").unblock();
    });
}

//Canned Responses
function removeCannedResponse(msgID) {
  Swal.fire({
    text: langRemoveCannedConfirmation,
    type: "warning",
    showCancelButton: true,
    confirmButtonText: langDelete,
    cancelButtonText: langCancel,
    cancelButtonColor: "#d33",
  }).then((result) => {
    if (result.value) {
      $("#cannedID").val(msgID);
      $("#manageForm").submit();
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      $("#cannedID").val("");
      return false;
    }
  });
}

//Remove KB Cat
function removeCategory(catID) {
  Swal.fire({
    text: langKbCatConfirmation,
    type: "warning",
    showCancelButton: true,
    confirmButtonText: langDelete,
    cancelButtonText: langCancel,
    cancelButtonColor: "#d33",
  }).then((result) => {
    if (result.value) {
      $("#category_id").val(catID);
      $("#manageForm").submit();
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      $("#category_id").val("");
      return false;
    }
  });
}

function removeArticle(articleID) {
  Swal.fire({
    text: langKbArticleConfirmation,
    type: "warning",
    showCancelButton: true,
    confirmButtonText: langDelete,
    cancelButtonText: langCancel,
    cancelButtonColor: "#d33",
  }).then((result) => {
    if (result.value) {
      $("#article_id").val(articleID);
      $("#manageForm").submit();
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      $("#article_id").val("");
      return false;
    }
  });
}

function removeDepartment(msgID) {
  Swal.fire({
    text: langDepartmentConfirmation,
    type: "warning",
    showCancelButton: true,
    confirmButtonText: langDelete,
    cancelButtonText: langCancel,
    cancelButtonColor: "#d33",
  }).then((result) => {
    if (result.value) {
      $("#department_id").val(msgID);
      $("#manageForm").submit();
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      $("#department_id").val("");
      return false;
    }
  });
}

function removeLinkCategory(msgID) {
  Swal.fire({
    text: langLinkCategoryConfirmation,
    type: "warning",
    showCancelButton: true,
    confirmButtonText: langDelete,
    cancelButtonText: langCancel,
    cancelButtonColor: "#d33",
  }).then((result) => {
    if (result.value) {
      $("#link_category_id").val(msgID);
      $("#manageForm").submit();
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      $("#link_category_id").val("");
      return false;
    }
  });
}

function removeLink(msgID) {
  Swal.fire({
    text: langLinkConfirmation,
    type: "warning",
    showCancelButton: true,
    confirmButtonText: langDelete,
    cancelButtonText: langCancel,
    cancelButtonColor: "#d33",
  }).then((result) => {
    if (result.value) {
      $("#link_id").val(msgID);
      $("#manageForm").submit();
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      $("#link_id").val("");
      return false;
    }
  });
}

function removeAgent(msgID) {
  Swal.fire({
    text: langAgentsConfirmation,
    type: "warning",
    showCancelButton: true,
    confirmButtonText: langDelete,
    cancelButtonText: langCancel,
    cancelButtonColor: "#d33",
  }).then((result) => {
    if (result.value) {
      $("#agent_id").val(msgID);
      $("#manageForm").submit();
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      $("#department_id").val("");
      return false;
    }
  });
}

function removeFilter(msgID) {
  Swal.fire({
    text: langFilterConfirmation,
    type: "warning",
    showCancelButton: true,
    confirmButtonText: langDelete,
    cancelButtonText: langCancel,
    cancelButtonColor: "#d33",
  }).then((result) => {
    if (result.value) {
      $("#filter_id").val(msgID);
      $("#deleteForm").submit();
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      $("#filter_id").val("");
      return false;
    }
  });
}

function changeEmailStatus(email_id) {
  $("#email_id").val(email_id);
  $("#emailForm").submit();
}

function removeEmail(email_id) {
  Swal.fire({
    text: langEmailConfirmation,
    type: "warning",
    showCancelButton: true,
    confirmButtonText: langDelete,
    cancelButtonText: langCancel,
    cancelButtonColor: "#d33",
  }).then((result) => {
    if (result.value) {
      $("#email_id").val(email_id);
      $("#email_action").val("remove");
      $("#emailForm").submit();
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      return false;
    }
  });
}

function setEmailDefault(email_id) {
  $("#email_id").val(email_id);
  $("#email_action").val("set_default");
  $("#emailForm").submit();
}

function removeCustomField(msgID) {
  Swal.fire({
    text: langCustomFieldConfirmation,
    type: "warning",
    showCancelButton: true,
    confirmButtonText: langDelete,
    cancelButtonText: langCancel,
    cancelButtonColor: "#d33",
  }).then((result) => {
    if (result.value) {
      $("#field_id").val(msgID);
      $("#manageForm").submit();
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      $("#field_id").val("");
      return false;
    }
  });
}

function removeUser(msgID) {
  Swal.fire({
    text: langUserConfirmation,
    type: "warning",
    showCancelButton: true,
    confirmButtonText: langDelete,
    cancelButtonText: langCancel,
    cancelButtonColor: "#d33",
  }).then((result) => {
    if (result.value) {
      $("#user_id").val(msgID);
      $("#manageForm").submit();
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      $("#user_id").val("");
      return false;
    }
  });
}
//Emails
function outgoing_type() {
  var outgoing_type = $("#outgoing_type").val();
  if (outgoing_type === "smtp") {
    $("#outgoing_details").show();
  } else {
    $("#outgoing_details").hide();
  }
}
function incoming_type() {
  var incoming_type = $("#incoming_type").val();
  if (incoming_type === "pop" || incoming_type === "imap") {
    $("#incoming_details").show();
  } else {
    $("#incoming_details").hide();
  }
}
function rule_action() {
  var rule_action = $("#rule_action").val();
  if (rule_action === "0") {
    $("#rule_send_copy").show();
    $("#rule_assign_to_agent").hide();
    $("#rule_set_priority").hide();
  }
  if (rule_action === "1") {
    $("#rule_send_copy").hide();
    $("#rule_assign_to_agent").show();
    $("#rule_set_priority").hide();
  }
  if (rule_action === "2") {
    $("#rule_send_copy").hide();
    $("#rule_assign_to_agent").hide();
    $("#rule_set_priority").show();
  }
}

function toggleSelect(depId) {
  var checked = $("#dep_" + depId).is(":checked");
  var select = $("#" + depId + "_state");
  if (!checked) {
    select.attr("disabled", "disabled");
  } else {
    select.removeAttr("disabled");
  }
}
