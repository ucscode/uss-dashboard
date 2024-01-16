"use strict";

class Notification {

    API_ENDPOINT = Uss.url + "/api/notification";

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
            indexes,
            read: read ? 1 : 0
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
            indexes,
            hidden: hidden ? 1 : 0
        });  
    }

    /**
     * Get a list of notifications for the current user
     * 
     * @param {object} object 
     * @returns Promise
     */
    get(object) {
        return this.#createAPICall('get', object);
    }

    /**
     * Remove a list of notifications for the current user
     * 
     * @param {object} object 
     * @returns Promise
     */
    remove(object) {
        return this.#createAPICall('remove', object);
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
                data
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
}