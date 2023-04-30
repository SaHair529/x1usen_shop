export default class HTMLAttributesNaming {
    static MODALS = {
        PRODUCT_MODAL: {
            ID: 'product-info-modal',
            detailInfoLink: {
                class: 'detail-link'
            }
        },
        NOT_AUTHORIZED_MODAL: {
            ID: 'not-authorized-alert-modal'
        },
        imageModal: {
            id: 'detail-image-modal'
        },
        customModal: {
            class: 'custom-modal js-delete-modal'
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

    static productCard = {
        imageZoomBtn: {
            class: 'action-zoom-img'
        }
    }

    static cartItemCard = {
        class: 'cart-item-card',
        decreaseBtn: {
            class: 'js-decrease-cart-item'
        },
        increaseBtn: {
            class: 'js-increase-cart-item'
        },
        delButton: {
            class: 'js-delete-cart-item'
        }
    }

    static productSumPriceCounter = {
        class: 'sum-price'
    }
}