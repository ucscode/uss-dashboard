"use strict";

(new class {

    jQueryNode(id) {
        const dataset = id === '*' ? `[data-id]` : `[data-id='${id}']`;
        return $(`#notification-list ${dataset}, #notification-dropdown ${dataset}`);
    }

    unview(id) {
        this.jQueryNode(id).find('.unviewed').removeClass('unviewed');
    }

    remove(id) {
        this.jQueryNode(id).fadeOut(300, function() {
            $(this).remove();
            const list = $('#notification-list');
            if(list.length && !list.find('[data-id]').length) {
                window.location.href = '';     
            }
        });
    }

    count(num) {
        const counter = $('[data-nx-count]');
        if(!num) counter.remove();
        else counter.text(num);
    }

    updateNotification(id, data, action) {
        data.notificationNonce = __uss.dashboard.nonce;
        data.id = id;
        $.ajax({
            url: __uss.dashboard.url + '/notifications',
            method: 'POST',
            data: data,
            dataType: 'json',
            success: result => action(true, result),
            error: result => action(false, result)
        });
    }

    create() {
        const self = this;
        const elements = '#notification-list, #notification-dropdown, #notification-widget';
        const targets = 'a[data-viewed], a[data-hidden]';

        $(elements).on('click', targets, function() {

            const parentBlock = $(this).parents('[data-id]');
            const id = parentBlock.attr('data-id');
            const data = Object.assign({}, this.dataset);
            
            self.updateNotification(id, data, (success, result) => {
                if(success) {
                    if(data.viewed) self.unview(data.id);
                    else if(data.hidden) self.remove(data.id);
                    self.count(parseInt(result.message));
                }
            });
            
        });
    }

}).create();