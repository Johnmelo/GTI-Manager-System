$('.table.table-hover > tbody > tr').on('click', function(e) {

    var request_id = e.currentTarget.children[0].innerHTML;
    var table = $(e.currentTarget).parents('.table');

    if (table.hasClass('open-request-list')) {
        fillOpenRequestModal(request_id);
    } else if (table.hasClass('call-request-list')) {
        fillRequestCallModal(request_id);
    }
});
