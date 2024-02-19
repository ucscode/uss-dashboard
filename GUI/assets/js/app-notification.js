"use strict";

class AppNotification {

    API_ENDPOINT = Uss.url + "/api/notification";

    #template;
    #secondaryItemsLimit = 4;

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
        let selector = "#notification-primary .notification-item [data-action]";
        this.#template = $(`#notification-secondary .notification-item`).first().clone();

		$(this.#entitySelector()).on('click', ".notification-item [data-item]", function() {
            _self.#notificationRead(this.dataset.item)
        });

        this.#notificationReadAll();

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
		this.markAsRead([key]).then(result => {
			if(result.status) {
				this.#notificationEntities(result.data).then(() => {
                    const selector = this.#entitySelector(`.notification-item [data-item='${key}']`);
					$(selector).removeClass('unseen');
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
        const _self = this;
		this.markAsHidden([key]).then(result => {
			if(result.status) {
				this.#notificationEntities(result.data, {hidden: 0}).then(entities => {
                    let promises = [];
                    $(`.notification-item [data-item='${key}']`).each(function() {
                        let deferred = $.Deferred();
                        $(this).parent().fadeOut("fast", function() {
                            $(this).remove();
                            deferred.resolve();
                        });
                        promises.push(deferred.promise());
                    });
                    $.when.apply($, promises).done(function() {
                        _self.#resolveHiddenPromise(entities);
                    });
				});
			}
		})
	}

    #notificationReadAll() {
        $("#notification-primary [data-action='mark-all-as-read']").click(() => {
            this.markAsRead([]).then(result => {
                if(result.status) {
                    this.#notificationEntities(result.data).then(entities => {
                        $(this.#entitySelector(`.notification-item [data-item]`)).removeClass("unseen");
                    });
                }
            });
        })
    }

    /**
     * Get entities, update the notification brand and return a promise
     * 
     * @param {bool} getItems 
     * @returns Promise
     */
	async #notificationEntities(data, object = null) {
        const element = $("#notification-secondary .notification-badge");
        data.pending ? element.removeClass('d-none').text(data.pending) : element.addClass('d-none');
        if(object) {
            data = await this.get(object).then(result => {
                return (result.status) ? result.data : data;
            });
        }
        return data.entities;
	}

    /**
     * Derive node selector matching both primary & secondary notification block
     * 
     * @param {string} nodeSelector 
     * @returns string
     */
    #entitySelector(nodeSelector = '') {
        return [
            `#notification-secondary ${nodeSelector}`, 
            `#notification-primary ${nodeSelector}`
        ]
        .map(selector => selector.trim())
        .join(", ");
    }

    /**
     * A custom action after promise resolved
     * 
     * @param {object} entities
     */
    #resolveHiddenPromise(entities) {
        const primaryItems = $("#notification-primary .notification-item").length;
        if(!primaryItems) {
            Notiflix.Loading.circle();
            return window.location.reload();
        }
        const secondaryContainer = $("#notification-secondary");
        let secondaryItems = secondaryContainer.find(".notification-item").length;
        let vacancy = this.#secondaryItemsLimit - secondaryItems;

        if(entities.length && vacancy > 0) {
            for(let entity of entities) {

                let selector = this.#entitySelector(`.notification-item [data-item='${entity.id}']`);
                let nodes = $(selector);

                if(nodes.length === 1) {
                    let clone = this.#template.clone();

                    clone.find("[data-item]")
                        .attr("data-item", entity.id)[
                            parseInt(entity.seen) ? 'removeClass' : 'addClass'
                        ]("unseen");

                    clone.find("[data-html], [data-bind]").each(function() {
                        if(this.hasAttribute("data-html")) {
                            this.innerHTML = entity[this.dataset.html];
                        }
                        if(this.hasAttribute("data-bind")) {
                            let attr = this.dataset.bind.split(":").map(value => value.trim());
                            this.setAttribute(attr[0], entity[attr[1]]);
                        }
                    });

                    let lastNode = secondaryContainer.find(".notification-item").last();
                    lastNode.length ? lastNode.after(clone) : secondaryContainer.prepend(clone);
                    
                    vacancy--;
                    if(vacancy < 1) break;
                }

            }
        }
    }
}