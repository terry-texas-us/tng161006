
function textSnippet(snippetID, extra) {
    'use strict';
    var snippet;
    $.ajax({
        type: 'get',
        async: false, // [ts] this is now considered bad form
        timeout: 1000,
        url: 'components/ajax/textSnippets.php',
        data: {snippetID: snippetID, extra: extra},
        success: function (data) {
            snippet = data;
        },
        dataType: 'text'
    });
    return snippet;
}

function textSnippetAlert(snippetID, extra) {
    'use strict';
    $.get(
        'components/ajax/textSnippets.php',
        {snippetID: snippetID, extra: extra},
        function (data) {
            this.alert = document.createElement('div');
            var position = document.getElementsByTagName('form')[0];
            position.appendChild(this.alert);
            this.alert.className = 'alert alert-warning alert-dismissible fade in';
            $('.alert').attr({
                role : 'alert'
            });
            this.close = document.createElement('button');
            this.alert.appendChild(this.close);
            this.close.innerHTML = "<span aria-hidden='true'>&times;</span>";
            this.close.className = 'close';
            $('.close').attr({
                'type' : 'button',
                'data-dismiss' : 'alert',
                'aria-label' : 'Close'
            });
            $('.alert').append(data);
        },
        'text'
    );
}

function textSnippetInto(domElement, snippetID, extra) {
    'use strict';
    $.get(
        'components/ajax/textSnippets.php',
        {snippetID: snippetID, extra: extra},
        function (data) {
            $(domElement).val(data);
        },
        'text'
    );
}
