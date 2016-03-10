
ModalAlert = function (url, options) {
    'use strict';
    this.url = url;
    this.options = {
        type: 'html',
        size: 'modal-md',  // widths: modal-sm 300px modal-md 600px modal-lg 900px
        onremove: null,
        draggable: false,
        resizable: false,
        backdrop: true,
        keyboard: true,
        doneLoading: null
    };
    $.extend(this.options, options || {});
    this.setup();
};

ModalAlert.prototype = {
    setup: function () {
        'use strict';
        this.getWindow();

        var closeControl = this.close;

        $('.modal-content').html($(this.url));
        $('.modal-header').prepend(closeControl);
        $('#myModal').modal({
            backdrop: this.options.backdrop,
            keyboard: this.options.keyboard
        });
        $('.modal-dialog').addClass(this.options.size);
        $('#myModal').modal('show');

        if (this.options.doneLoading) {
            this.options.doneLoading();
        }
    },
    getWindow: function () {
        'use strict';
        this.over = null;
        this.modal = document.createElement('div');
        document.body.appendChild(this.modal);
        this.modal.className = 'modal fade';
        this.modal.id = 'myModal';
        $('#myModal').attr({
            tabindex : '-1',
            role : 'dialog',
            'aria-labelledby' : 'myModalLabel',
            'aria-hidden' : 'true'
        });
        this.modalDialog = document.createElement('div');
        this.modal.appendChild(this.modalDialog);
        this.modalDialog.className = 'modal-dialog';
        $('.modal-dialog').attr({
            role : 'document'
        });
        this.modalContent = document.createElement('div');
        this.modalDialog.appendChild(this.modalContent);
        this.modalContent.className = 'modal-content';

        this.close = document.createElement('button');
        this.close.onclick = this.remove;

        this.close.innerHTML = "<span aria-hidden='true'>&times;</span>";
        this.close.id = 'modalCloseContol';
        this.close.className = 'close';
        $('#modalCloseControl').attr({
            'type' : 'button',
            'data-dismiss' : 'modal',
            'aria-label' : 'Close'
        });
        this.close.onremove = this.options.onremove;
    },
    remove: function () {
        'use strict';
        var onremove = this.onremove;

        $('#myModal').remove();
        $(document.body).removeClass('modal-open');
        $('.modal-backdrop').remove();
        if (onremove) {
            onremove();
        }
        return false;
    }
};

function openFind(form, findscript) {
    'use strict';
    var params = $.param(form);
    if ($('#findspin').length) {
        $('#findspin').show();
    }
    $.ajax({
        url: findscript,
        data: params,
        dataType: 'html',
        success: function (req) {
            $('#findresults').html(req);
            if ($('#findspin').length) {
                $('#findspin').hide();
            }
            $('#finddiv').toggle(200, function () {
                $('#findresults').toggle(200);
            });
        }
    });
    return false;
}

function clearForm(form) {
    'use strict';
    $(form).children(':input').each(function (index, element) {
        if (element.type === 'text') {
            element.value = '';
        }
    });
}

function reopenFindForm() {
    'use strict';
    $('#findresults').toggle(200, function () {
        clearForm(document.findform1);
        $('#finddiv').toggle(200);
    });
}

function openHelp(filename) {
    'use strict';
    var newwindow = window.open(filename, 'newwindow', 'height=600,width=700,resizable=yes,scrollbars=yes');
    newwindow.focus();

    return false;
}
