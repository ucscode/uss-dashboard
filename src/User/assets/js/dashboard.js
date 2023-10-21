"use strict";

new class {
	
	constructor() {
		this.toggleSidebar();
		this.dataUICollection();
	}
	
	toggleSidebar() {
		$('#menu-toggle').on("click", () => this.sidebar('toggleClass'));
		$('.overlay').on("click", () => this.sidebar('removeClass'));
		window.addEventListener('resize', () => {
			if( document.body.clientWidth > 1200 ) this.sidebar('removeClass');
		});
	}
	
	sidebar(action) {
		let list = [".sidebar-nav-wrapper", ".main-wrapper", ".overlay"];
		for(let x of list) {
			$(x)[action]('active');
		};
	}

	dataUICollection() {
		const attr = [
			'[data-ui-transfer-click-event-to]',
			'input[type="file"][data-ui-preview-uploaded-image-in]'
		];

		$(attr[0]).click(function() {
			$(this.dataset.uiTransferClickEventTo).trigger('click');
		});

		$(attr[1]).on('change', function() {
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
	
};
