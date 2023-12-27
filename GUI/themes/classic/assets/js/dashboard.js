"use strict";

new class {
	
	constructor() {
		this.toggleSidebar();
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
	
};
