class LassoModal {
    constructor(modalId, options = {}) {
        this.options = options;
        this.modal = document.getElementById(modalId);
        if (!this.modal) {
            throw new Error('Modal with ID ' + modalId + ' not found');
        }
        this.shownEventListeners = [];
        this.hiddenEventListeners = [];
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        if (this.options.resetHiddenListeners) {
            jQuery(document).off('ls.modal.hidden', '#' + this.modal.id);
        }

        if (this.options.resetShownListeners) {
            jQuery(document).off('ls.modal.shown', '#' + this.modal.id);
        }

        // Close the modal when clicking outside of it
        window.onclick = (event) => {
            if (event.target === this.modal) {
                this.close();
            }
        };

        // Close the modal when pressing "ESC"
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                this.close();
            }
        });
    }

    open() {
        this.modal.style.display = "block";
        jQuery(this.modal).trigger('ls.modal.shown'); // Trigger jQuery event
    }

    close() {
        this.modal.style.display = "none";
        jQuery(this.modal).trigger('ls.modal.hidden'); // Trigger jQuery event

        if (this.options.destroyEventsAfterClose) {
            this.destroyEvents();
        }
    }

    toggle() {
        if (this.modal.style.display === "block") {
            this.close();
        } else {
            this.open();
        }
    }

    onModalShow(callback) {
        jQuery(this.modal).on('ls.modal.shown', callback); // Attach jQuery event listener
        this.shownEventListeners.push(callback);
    }

    onModalHidden(callback) {
        jQuery(this.modal).on('ls.modal.hidden', callback); // Attach jQuery event listener
        this.hiddenEventListeners.push(callback);
    }

    destroyEvents() {
        this.shownEventListeners.forEach(callback => {
            jQuery(this.modal).off('ls.modal.shown', callback); // Detach jQuery event listener
        });
        this.hiddenEventListeners.forEach(callback => {
            jQuery(this.modal).off('ls.modal.hidden', callback); // Detach jQuery event listener
        });
        this.shownEventListeners = [];
        this.hiddenEventListeners = [];
    }
}

// jQuery plugin
(function($) {
    $.fn.modal = function(action, options = {}) {
        return this.each(function() {
            let modalInstance = $(this).data('lassoModal');

            if (!modalInstance) {
                modalInstance = new LassoModal(this.id, options);
                $(this).data('lassoModal', modalInstance);
            }

            if (action === 'show') {
                modalInstance.open();
            } else if (action === 'hide') {
                modalInstance.close();
            } else if (action === 'toggle') {
                modalInstance.toggle();
            }
        });
    };
})(jQuery);
