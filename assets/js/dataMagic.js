"use strict";

new class {
    
    constructor() {
        this.transferClick();
        this.previewUploadedImage();
        this.copyText();
        this.confirmHref();
        this.enableTableCheckboxes();
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
					iziToast.error({
						message: "Please select a valid image",
						position: "bottomLeft"
					})
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
                    iziToast.info({
                        message: "Text copied to clipboard"
                    })
                }, (output) => {
                    iziToast.error({
                        message: "The text was not copied"
                    })
                })
            } else {
                iziToast.warning({
                    message: "Apparently nothing to copy"
                })
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
            bootbox.confirm({
                message,
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
        const selector = 'table[data-ui-table="crud"]';
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

}