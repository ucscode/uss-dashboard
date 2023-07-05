"use strict";

new class {
	
	constructor() {
		this.sidebarToggle();
		this.initNX();
		this.initDOMTable();
		this.forwardClickEvent();
	}
	
	/**
	 * Delay an event triggered by the user
	 *
	 * When a user performs an event (e.g click), it is triggered almost instantly
	 * But in the case where you want to delay the effect of the event for certain period of time
	 * The option is not available on browser
	 *
	 * However, this method does exactly that
	 * It delays the default effect of the event triggered by the user
	 *
	 * @param Object event The event object
	 * @param int delay The time in milliseconds
	 * @param function func A function to call while the event is being delayed
	 *
	 */
	delayEvent(e, delay, func) {
		
		let event = e.originalEvent || e;
		
		if( event.isTrusted ) {
			
			event.preventDefault();
			
			/**
			 * A new event need to be created
			 * Because we've called `preventDefault()` method on the old event
			 * Thus, if we try to dispatchEvent on an element using the old event object, it won't work
			 */
			let newEvent = new event.constructor( event.type, event );
			
			setTimeout(function(){
				event.target.dispatchEvent( newEvent );
			}, delay);
			
			/**
			 * We'll pass the `newEvent` object to the function
			 * In case the programmer still intends to `preventDefault()` of the newEvent
			 */
			if( typeof func == 'function' ) func(event, newEvent);
			
		};
		
	}
	
	//! Toggle the sidebar
	
	sidebar( action ) {
		let list = [ ".sidebar-nav-wrapper", ".main-wrapper", ".overlay" ];
		for( let x of list ) {
			let el = $(x);
			el[ action ]( 'active' );
		}
	}
	
	//! 
	sidebarToggle() {
		
		$('#menu-toggle').on("click", ()=> {
			// toggle menu;
			this.sidebar( 'toggleClass' );
		});
		
		$('.overlay').on("click", ()=> {
			// remove menu;
			this.sidebar( 'removeClass' );
		});
		
		window.addEventListener('resize', ()=> {
			if( document.body.clientWidth > 1200 ) this.sidebar( 'removeClass' );
		});
	
	}
	
	
	/**
	 * Notification remark
	 * Notifications may be marked as "read", "removed"... or "custom"
	 */
	markNX( indexes, remark ) {
		
		/** Get the ajax URI */
		let nxurl = $('[data-nx-container]').parents('[data-nxurl]').attr('data-nxurl');
		
		if( !nxurl ) return;
		
		let self = this;
		
		/**
		 * Send an ajax request to the server
		 */
		$.ajax({
			method: 'POST',
			url: nxurl,
			data: { 
				nx: indexes,
				remark: remark,
				nonce: Uss.Nonce,
				route: 'ud-notification'
			},
			success: function( response ) {
				try {
					var result = JSON.parse( response );
				} catch(e) {
					return console.error( "Notification: Invalid response string received from the server" );
				}
				if( !result.status ) return; 
				/**
				 * Make changes to associated elements
				 */
				indexes.forEach( index => self.execNX(remark, $(`[data-nx='${index}']`)) );
			}
		});
		
	}
	
	/**
	 * Execute the marker response
	 * `$el` = node list of affected notifications
	 */
	execNX( key, $el ) {	
	
		let $obj = new class {
			
			/**
			 * Process viewed notification
			 */
			viewed() {
				if( !$el.hasClass('unviewed') ) return;
				$el.removeClass('unviewed');
				this.#NXCounter()
			}
			
			#NXCounter() {
				let val;
				$('.notification-box [data-nx-count]').each((i,el) => {
					let num = parseInt( $(el).text().trim() ) || 0;
					num--;
					if( num < 1 ) {
						$(el).addClass('d-none');
						num = 0;
					} else $(el).text( num );
					val = num;
				});
				return val;
			}
			
			/**
			 * Process removed notifications
			 */
			remove() {
				$el.each(function() {
					$(this).parent().fadeOut(500, function() {
						this.parentElement.removeChild(this);
						if( !$("[data-nx-container='body']").children().length ) {
							Notiflix.Loading.pulse();
							window.location.reload();
						};
					});
				});
			}
			
		};
		
		/**
		 * Call the object method
		 */ 
		if( $el.length && $obj[key] ) $obj[key]();
		
	}
	
	
	/**
	 * Invoke events that will enable notification blocks to become functional
	 * Such as:
	 * - Mark notification as "seen" and remove the animated Number
	 * - Mark notification as "deleted" and remove the DOM Element etc...
	 */
	initNX() {
		
		const self = this;
		
		/**
		 * Mark a notification as "viewed"
		 */
		$('[data-nx-container]').delegate( '[data-nx]', "click", function(e) {
			let el = this;
			self.delayEvent(e, 500, function(e, newEvent) {
				//newEvent.preventDefault();
				self.markNX( [el.dataset.nx], 'viewed' );
			});
		});
		
		/**
		 * Mark multiple notifications as "viewed"
		 */
		$("[data-nx-marker]").click(function(e) {
			let list = [].slice.call( $("[data-nx-container='body'] [data-nx]") ).map(function(el) {
				return el.dataset.nx;
			});
			self.markNX( list, 'viewed' );
		});
		
		/**
		 * Perform an action
		 */
		$("[data-nx-container='body'] [data-nx-action]").click(function(e) {
			let nx = $(this).parents('.single-notification').find('[data-nx]').attr('data-nx');
			self.markNX( [nx], this.dataset.nxAction );
		});
		
	}
	
	initDOMTable() {
		
		$("[data-domtablet]").each(function() {
			
			let name = this.dataset.domtablet;
			let self = this;
			
			let checkTh = `input[type='checkbox'][data-check-all='${name}']`;
			let checkTd = `input[type='checkbox'][data-check='${name}']`;
			
			/** 
			 * Check all when `thead` checkbox is checked 
			 */
			$(self).find( checkTh ).click(function() {
				$(self).find(`${checkTh}, ${checkTd}`).prop('checked', this.checked);
			});
			
			/** 
			 * Check `thead` when all checkbox is manually checked 
			 */
			$(self).find( checkTd ).click(function() {
				let checked_all = $(self).find(`${checkTd}:checked`).length === $(self).find(`${checkTd}`).length;
				$(self).find( checkTh ).prop( 'checked', checked_all );
			});
			
			/**
			 * Activate Bulk Option
			 */
			let bulkForm = `.dt-uss-bulk form#dt-${name}-bulk`;
			
			$(self).find( bulkForm ).on('submit', function(e) {
				let form = this;
				e.preventDefault();
				bootbox.confirm({
					message: "Are you sure you want to apply this action?",
					className: 'text-center',
					size: 'small',
					closeButton: false,
					centerVertical: true,
					callback: function(yes) {
						if( !yes ) return;
						if( !$(self).find(`${checkTd}:checked`).length ) {
							return bootbox.alert({
								message: "<i class='bi bi-exclamation-octagon me-1'></i> No table rows were selected",
								className: 'text-center',
								closeButton: false
							});
						};
						form.submit();
					},
					buttons: {
						confirm: { label: 'Yes' },
						cancel: { label: 'No' }
					}
				});
			});
			
		});
		
	}
	
	/* 
		==================== Auto click a different object ==================
		
		Let's assume you want to hide an element because it's interface looks ugly
		
		You can still click on the hidden element by passing `[data-uss-trigger-click]` attribute to the custom 
		element that you've create to replace the ugly element. 
		
		Example;
		
		<input style='display: none' id='hidden-input'>
		
		<div class='custom-beautiful-input' data-uss-trigger-click='#hidden-input' />
		
	*/
	
	forwardClickEvent() {
		$("body").on("click", "[data-uss-trigger-click]", function() {
			let element = $( this.dataset.ussTriggerClick ).get(0);
			if( !element ) return;
			let click = new MouseEvent('click', {
				bubbles: true,
				cancelable: true,
				view: window
			});
			element.dispatchEvent(click);
		});
	}
	
};
