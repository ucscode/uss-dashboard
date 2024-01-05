"use strict";

let security = new class {
	
	constructor($) {
		this.#initialize($);
	}

	redirect(event, redirect) {
		if(redirect) {
			window.location.href = redirect;
			return;
		}
		console.warn("No redirect value set for property `app:redirect`");
	}

	#initialize($) {
		
		const self = this;
		
		$('[data-vcode]').click(function() {
			bootbox.prompt({
				title: "Resend Confirmation Email?",
				inputType: 'email',
				required: true,
				callback: function(value) {
					if( !value || value.trim() == '') return;
					Notiflix.Loading.hourglass();
					$.ajax({
						url: self.action,
						data: {
							email: value,
							route: 'ud-vcode'
						},
						method: 'POST',
						success: function(response) {
							Notiflix.Loading.remove();
							let result = JSON.parse(response);
							bootbox.alert({
								title: Uss.platform,
								message: result.message 
							});
						}
					});
				},
				message: "<div class='mb-1'>Please enter your account email address</div>",
				centerVertical: true,
				className: 'animate__animated animate__pulse'
			});
		});
		
	}
	
}(jQuery);

