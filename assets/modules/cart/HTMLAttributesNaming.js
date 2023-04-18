export default class HTMLAttributesNaming {
    static MODALS = {
        PRODUCT_MODAL: {
            ID: 'product-info-modal'
        },
        NOT_AUTHORIZED_MODAL: {
            ID: 'not-authorized-alert-modal'
        }
    }

    static CONTAINERS = {
        MODAL_BUTTONS: {
            CLASS: 'product-info-modal-buttons'
        }
    }

    static BUTTONS = {
        INCREASE_CART_ITEM: {
            CLASS: 'add-to-cart',
            TEXT: '+1'
        },
        REMOVE_FROM_CART: {
            CLASS: 'remove-from-cart',
            TEXT: '-1'
        },
        ADD_TO_CART: {
            CLASS: 'add-to-cart',
            TEXT: 'В корзину'
        }
    }

    static CART_ITEM_COUNTER = {
        CLASS: 'product-modal-counter'
    }
    static productSumPriceCounter = {
        class: 'sum-price'
    }
}