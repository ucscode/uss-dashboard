"use strict";

new class {
    
    constructor() {
        this.transferClick();
        this.previewUploadedImage();
        this.copyText();
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

}