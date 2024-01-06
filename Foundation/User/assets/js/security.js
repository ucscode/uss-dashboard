"use strict";

let security = new class 
{
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
				centerVertical: true,
				message: "<div class='mb-1'>Please enter your account email address</div>",
				className: 'animate__animated animate__pulse',
				callback: function(value) {
					if( !value || value.trim() == '') return;
					Notiflix.Loading.hourglass();
					self.#createAjax({
						email: value,
						nonce: Uss.dashboard.nonce
					});
				},
			});
		});
	}

	#createAjax(data) {
		const self = this;

		const options = {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: (new URLSearchParams(data)).toString()
		};

		fetch(Uss.dashboard.url + '/ajax/verify-email', options)
			.then(response => {
				let errorMessage = 'Could not handle the request';
				if(response.ok) {
					return response.json().catch(() => {
						throw new Error(errorMessage);
					});
				}
				throw new Error(errorMessage);
			})
			.then(data => {
				self.#toast(data.status, data.message)
			})
			.catch(error => self.#toast(false, error))
			.finally(() => Notiflix.Loading.remove());

	}
	
	#toast(status, message) {
		let background = status ? 'var(--bs-success)' : 'var(--bs-danger)';
		Toastify({
			text: message,
			style: { background },
			duration: 5000
		}).showToast();
	}
}(jQuery);

