"use strict";

new class {
    
    constructor() {
        this.transferClick();
        this.previewUploadedImage();
        this.copyText();
        this.confirmHref();
        this.enableTableCheckboxes();
        this.manageBulkActions();
        this.glightbox();
    }

    transferClick() {
        const selector = '[data-ui-transfer-click-event-to]';
		$(selector).click(function() {
			$(this.dataset.uiTransferClickEventTo).trigger('click');
		});
    }

	previewUploadedImage() {
        const selector = '[data-ui-preview-uploaded-image-in]';
		$('input[type="file"]' + selector).on('change', function() {
			const element = $(this.dataset.uiPreviewUploadedImageIn);
			if(element.prop('tagName') === 'IMG') {
				const file = this.files[0];
				if(file && file.type.startsWith('image/')) {
					const reader = new FileReader();
					reader.onload = function() {
						element.attr('src', this.result);
					};
					reader.readAsDataURL(file);
				} else {
					this.value = '';
					Toastify({
						text: "Please select a valid image",
                        style: {
                            background: "var(--bs-danger)"
                        }
					}).showToast();
				}
			}
		});
	}

    copyText() {
        const selector = '[data-ui-copy]';
        $(selector).click(function() {
            const element = $(this.dataset.uiCopy);
            if(element.length) {
                let text;
                if(['SELECT', 'INPUT', 'TEXTAREA'].includes(element.prop('tagName'))) {
                    text = element.val();
                } else {
                    text = element.text();
                }
                const promise = navigator.clipboard.writeText(text);
                promise.then(output => {
                    Toastify({
                        text: "Text copied to clipboard"
                    }).showToast();
                }, (output) => {
                    Toastify({
                        text: "The text was not copied",
                        style: {
                            background: "var(--bs-danger)"
                        }
                    }).showToast();
                })
            } else {
                Toastify({
                    text: "Apparently nothing to copy",
                    style: {
                        background: "var(--bs-warning)"
                    }
                }).showToast();
            }
        })
    }

    confirmHref() {
        const selector = 'a[data-ui-confirm]';
        $(selector).click(function(e) {
            e.preventDefault();
            const anchor = this;
            const message = this.dataset.uiConfirm;
            const size = this.dataset.uiSize || null;
            const className = this.dataset.uiClass || (size == 'small' ? size : null);
            bootbox.confirm({
                message: `<div class='${className}'>${message}</div>`,
                callback(ok) {
                    if(ok) {
                        const clone = anchor.cloneNode();
                        clone.click();
                    }
                },
                size,
                closeButton: false,
                buttons: {
                    cancel: {
                        className: 'btn btn-secondary btn-sm'
                    },
                    confirm: {
                        className: 'btn btn-primary btn-sm'
                    }
                }
            })
        });
    }

    enableTableCheckboxes() {
        const selector = 'table[data-ui-table="inventory"]';
        $(selector).each(function() {
            const table = this;
            const checkboxSelector = '[data-ui-checkbox]';
            const singleCheckboxSelector = checkboxSelector + '[data-ui-checkbox="single"]';
            const multipleCheckboxSelector = checkboxSelector + '[data-ui-checkbox="multiple"]';
            $(this).find(checkboxSelector).on('change', function() {
                if(this.dataset.uiCheckbox === 'multiple') {
                    $(table).find(checkboxSelector).prop('checked', this.checked);
                } else {
                    const singleCheckboxes = $(table).find(singleCheckboxSelector);
                    const checkedCheckboxes = $(table).find(singleCheckboxSelector + ':checked');
                    $(multipleCheckboxSelector).prop('checked', singleCheckboxes.length === checkedCheckboxes.length);
                }
            });
        })
    }

    manageBulkActions() {
        const selector = 'form[data-ui-crud-form="inventory"]';
        $(selector).each(function() {
            const formElement = this;
            const formId = this.getAttribute('id');
            const selectElement = $(formElement).find('select[data-ui-bulk-select]');
            if(selectElement.length) {
                $(formElement).on('submit', function(e) {
                    e.preventDefault();
                    const checkedCheckboxes = $(`table[data-ui-table="inventory"][data-form-id='${formId}'] [data-ui-checkbox="single"]:checked`);
                    if(checkedCheckboxes.length) {
                        const option = selectElement.find('option:selected').get(0);
                        let message = option.dataset.uiConfirm || `You are about to perform a bulk action on {items} items! <br> Are you sure you want to proceed?`;
                        message = message.replace('{items}', checkedCheckboxes.length);
                        const size = option.dataset.uiSize || 'small';
                        bootbox.confirm({
                            message,
                            size,
                            callback: function(ok) {
                                if(ok) formElement.submit();
                            },
                            buttons: {
                                confirm: {
                                    className: 'btn btn-primary btn-sm'
                                },
                                cancel: {
                                    className: 'btn btn-secondary btn-sm'
                                }
                            },
                            closeButton: false
                        })
                    } else {
                        Toastify({
                            text: "No item was selected",
                            style: {
                                background: "var(--bs-info)"
                            }
                        }).showToast();
                    }
                });
            }
        });
    }

    glightbox() {
        const selector = "a[data-glightbox]";
        const el = $(selector).get(0);
        const glightbox = GLightbox({
            selector
        });
    }

}