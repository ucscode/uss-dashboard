"use strict";

import Ud from './Ud.js';

function unview(id) {
    $(`#notification-list [data-id='${id}'], #notification-dropdown [data-id='${id}']`)
    .find('.unviewed')
    .removeClass('unviewed');
}

function remove(id) {
    $(`#notification-list [data-id='${id}'], #notification-dropdown [data-id='${id}']`)
    .fadeOut(300, function() {
        $(this).remove();
        if(!$('#notification-list [data-id]').length) {
            const emptyNode = $('#notification-empty');
            if(emptyNode.length) {
                emptyNode.removeClass('d-none');
                $(`[data-widget='marker']`).addClass('d-none');
            }       
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

$('#notification-list, #notification-dropdown').on('click', 'a[data-viewed], a[data-hidden]', function() {
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


