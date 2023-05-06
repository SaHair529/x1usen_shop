export default class HTMLElements {
    static GLOBAL_ACTION_BUTTONS = {
        closeModal: {
            class: 'action-close-modal'
        },
        deleteModal: {
            class: 'js-delete-modal'
        }
    }

    static detailsTree = {
        class: 'tree',
        parentItem: {
            class: 'parent',
            detailLink: {
                class: 'detail-link'
            }
        },

        actionClasses: {
            openItem: 'action-open'
        }
    }

    static detailsWindow = {
        id: 'details-window'
    }

    static userIcon = {
        class: 'user-button'
    }

    static userMenu = {
        class: 'user-menu'
    }
}