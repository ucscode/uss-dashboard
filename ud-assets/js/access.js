"use strict";

/*!
 * Anonymous class
 */
 
new class {
	
	#form;
	#action;
	#type;
	
	constructor() {
		this.initForm( jQuery );
	}
	
	initForm($) {
		
		this.form = $('#auth-form[data-type]');
		
		if( !this.form ) return;
		
		this.action = this.form.attr('action');
		this.type = this.form.attr('data-type');
		
		this.initVCode($);
		
		this.form.on('submit', (e)=> {
			
			// prevent default send request;
			e.preventDefault();
			
			// Show Loader;
			Notiflix.Loading.circle();
			
			/*! New FormData */
			let formData = new FormData( this.form.get(0) );
			
			/*!
			 * Specify the route
			 */
			formData.set('route', this.type);
			
			// send request;
			$.ajax({
				url: this.action,
				type: 'POST',
				cache: false,
				processData: false,
				contentType: false,
				data: formData,
				success: function(response) {
					
					// remove Loader;
					Notiflix.Loading.remove();
					
					try {
						
						let result = JSON.parse( response );
						let type = result.status ? 'success' : 'failure';
						
						let option = {
							message: result.message,
							centerVertical: true,
							className: 'text-center',
							closeButton: !result.status
						};
						
						if( result.status ) {
							option.callback = function() {
								Notiflix.Loading.circle();
								if( result.data['redirect'] ) 
									window.location.href = result.data['redirect'];
								else window.location.reload();
							};
						};
						
						bootbox.alert(option);
						
					} catch( err ) {
						
						toastr.error( 'A critical error occured' );
						
						console.log( err );
						
					};
					
				}
			});
			
		});
	
	}

	initVCode($) {
		
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
	
};

