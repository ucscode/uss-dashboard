"use strict";

class Notification {

    API_ENDPOINT = Uss.url + "/api/notification";

    #template;

    /**
     * Mark a list of user dedicated notifications as read
     * 
     * @param {array} indexes 
     * @param {bool} $read
     * @returns Promise
     */
    markAsRead(indexes, read = true) {
        this.#checkError(indexes);
        return this.#createAPICall('mark-as-read', {
            data: {
                indexes,
                read: read ? 1 : 0
            }
        });
    }

    /**
     * Mark a list of user dedicated notifications as hidden
     * 
     * @param {array} indexes 
     * @param {bool} $hidden
     * @returns Promise
     */
    markAsHidden(indexes, hidden = true) {
        this.#checkError(indexes);
        return this.#createAPICall('mark-as-hidden', {
            data: {
                indexes,
                hidden: hidden ? 1 : 0
            }
        });  
    }

    /**
     * Get a list of notifications for the current user
     * 
     * @param {object} object 
     * @param {bool} count
     * @returns Promise
     */
    get(object, count = false) {
        return this.#createAPICall('get', {
            data: object,
            count
        });
    }

    /**
     * Remove a list of notifications for the current user
     * 
     * @param {object} object 
     * @param {bool} count
     * @returns Promise
     */
    remove(object, count = false) {
        return this.#createAPICall('remove', {
            data: object,
            count
        });
    }

    /**
     * Incase your theme does not align, you can apply custom logic
     * 
     * @returns undefined
     */
	applyGlobalLogic() {

        let _self = this;
        this.#template = $(`#notification-secondary .notification-item`).first().clone();

		$(this.#entitySelector()).on('click', ".notification-item [data-item]", function() {
            _self.#notificationRead(this.dataset.item)
        });

        let selector = "#notification-primary .notification-item [data-action]";
		$(selector).click(function() {
			let key = $(this).closest(".notification-item").find("[data-item]").attr("data-item");
			switch(this.dataset.action) {
				case 'mark-as-read':
					_self.#notificationRead(key);
					break;
				case 'mark-as-hidden':
					_self.#notificationHidden(key);
					break;
			}
		});

	}
    
    /**
     * Create and execute an API Request
     * 
     * @param {string} request 
     * @param {object} data 
     * @returns Promise
     */
    #createAPICall(request, data) {
        return fetch(this.API_ENDPOINT, {
            method: 'POST',
            headers: {
                'Context-Type': 'application/json'
            },
            cache: "no-cache",
            body: JSON.stringify({
                nonce: Uss.dashboard.nonce,
                request,
                data: data.data,
                count: data.count ? 1 : 0
            })
        }).then(response => {
            if(!response.ok) {
                throw new Error(`HTTP Error ${response.status}`);
            }
            return response.json()
        });
    }

    #checkError(indexes) {
        if(!Array.isArray(indexes)) {
            throw new Error("Parameter 1 must be of type Array");
        }
    }

    /**
     * Apply read logic to notification items
     * 
     * @param {int} key 
     */
	#notificationRead(key) {
		this.markAsRead([key]).then(data => {
			if(data.status) {
				this.#notificationEntities().then(resolved => {
                    const selector = this.#entitySelector(`.notification-item [data-item='${key}']`);
					resolved ? $(selector).removeClass('unseen') : null;
				});
			}
		});
	}
	
    /**
     * Apply remove/hidden logic to notification items
     * 
     * @param {int} key 
     */
	#notificationHidden(key) {
        let secondaryItemsLimit = 4;
		this.markAsHidden([key]).then(data => {
			if(data.status) {
				this.#notificationEntities(true).then(entities => {
					if(entities) {
                        let promise = new Promise(resolve => {
                            $(`.notification-item [data-item='${key}']`).parent().fadeOut("fast", function() {
                                $(this).remove();
                                let accessibleItems = $("#notification-secondary .notification-item").length;
                                let vacancy = secondaryItemsLimit - accessibleItems;
                                if(entities.length && vacancy > 0) resolve({entities, vacancy});
                            });
                        });
                        this.#resolveHiddenPromise(promise);
					}
				});
			}
		})
	}

    /**
     * Get entities, update the notification brand and return a promise
     * 
     * @param {bool} getItems 
     * @returns Promise
     */
	async #notificationEntities(getItems = false) {
		return this.get({seen: 0, hidden: 0}, !getItems)
		.then(data => {
            let context = data.data;
			if(data.status) {
				const element = $("#notification-secondary .notification-badge");
				const index = !getItems ? context : context.length;
				index ? element.removeClass('d-none').text(index) : element.addClass('d-none');
                return getItems ? context : data.status;
			}
            return false;
		});
	}

    /**
     * A custom action after promise resolved
     * 
     * @param {Promise} promise 
     */
    #resolveHiddenPromise(promise) {
        promise.then(object => {
            const container = $("#notification-secondary");
            for(let entity of object.entities) {
                let selector = this.#entitySelector(`.notification-item [data-item='${entity.id}']`);
                let nodes = $(selector);
                if(nodes.length === 1) {
                    let clone = this.#template.clone();
                    clone.find("[data-item]").attr("data-item", entity.id);
                    clone[parseInt(entity.seen) ? 'removeClass' : 'addClass']("unseen");
                    clone.find("[data-html], [data-bind]").each(function() {
                        if(this.hasAttribute("data-html")) {
                            this.innerHTML = entity[this.dataset.html];
                        }
                        if(this.hasAttribute("data-bind")) {
                            let attr = this.dataset.bind.split(":").map(value => value.trim());
                            this.setAttribute(attr[0], entity[attr[1]]);
                        }
                    });
                    let lastNode = container.find(".notification-item").last();
                    lastNode.length ? lastNode.after(clone) : container.prepend(clone);
                    object.vacancy--;
                    if(object.vacancy < 1) {
                        break;
                    }
                }
            }
        });
    }

    #entitySelector(nodeSelector = '') {
        return `#notification-secondary ${nodeSelector}, #notification-primary ${nodeSelector}`;
    }
}