'use strict';

class treeData {
	
	#tree;
	
	build( tree, func ) {
		let parent = tree.find(function(a) {
			return a.parent === null;
		});
		if( !parent ) return;
		this.#tree = tree;
		let nodeList = this.#createNode( parent );
		let container = document.createElement('div');
		container.className = 'tree';
		container.innerHTML = nodeList;
		if( typeof func == 'function' ) func(container);
	}
	
	#createNode( parent ) {
		let block = `<li><a href='javascript:void(0)'>${parent.value}</a>`;
		let children = this.#children(parent);
		if( children && children.length ) {
			block += `<ul>`;
			for( let child of children ) {
				block += this.#createNode( child );
			}
			block += `</ul>`;
		}
		this.block += `</li>`;
		return block;
	}
	
	#children( parent ) {
		return this.#tree.filter(function(child) {
			return child.parent == parent.id
		});
	}
	
}