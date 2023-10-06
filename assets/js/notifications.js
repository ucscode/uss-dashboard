"use strict";

import Ud from './Ud.js';

function jQueryNode(id) {
    const dataset = id === '*' ? `[data-id]` : `[data-id='${id}']`;
    return $(`#notification-list ${dataset}, #notification-dropdown ${dataset}`);
}

function unview(id) {
    jQueryNode(id)
    .find('.unviewed')
    .removeClass('unviewed');
}

function remove(id) {
    jQueryNode(id)
    .fadeOut(300, function() {
        $(this).remove();
        const list = $('#notification-list');
        if(list.length && !list.find('[data-id]').length) {
            window.location.href = '';     
        }
    });
}

function count(num) {
    const counter = $('[data-nx-count]');
    if(!num) {
        counter.remove();
    } else {
        counter.text(num);
    }
}

function updateNotification(id, data, action) {
    data.notificationNonce = Uss.ud.nonce;
    data.id = id;
    $.ajax({
        url: Uss.ud.url + '/notifications',
        method: 'POST',
        data: data,
        dataType: 'json',
        success: result => {
            action(true, result);
        },
        error: result => {
            action(false, result);
        }
    });
}

$('#notification-list, #notification-dropdown, #notification-widget')
.on('click', 'a[data-viewed], a[data-hidden]', function() {
    const parentBlock = $(this).parents('[data-id]')
    const id = parentBlock.attr('data-id');
    const data = Object.assign({}, this.dataset);
    updateNotification(id, data, (success, result) => {
        if(success) {
            if(data.viewed) {
                unview(data.id);
            } else if(data.hidden) {
                remove(data.id);
            }
            count(parseInt(result.message));
        }
    });
});


