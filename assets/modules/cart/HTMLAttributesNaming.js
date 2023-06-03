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


    static imageModal_forCreator = {
        tagName: 'div',
        class: 'custom-modal detail-image-modal js-delete-modal',
        attributes: [
            { id: 'detail-image-modal' }
        ]
    }

    static productModal_forCreator = {
        tagName: 'div',
        class: 'hidden remove-product-modal',
        attributes: [
            { id: 'product-info-modal' }
        ],
        children: [
            {
                tagName: 'div',
                class: 'inner',
                children: [
                    { tagName: 'h5', class: 'price' },
                    { tagName: 'p', class: 'name' },
                    {
                        tagName: 'a',
                        class: 'detail-link',
                        text: 'Подробнее о товаре',
                        attributes: [
                            { href: '#' },
                            { target: '_blank' }
                        ]
                    },
                    {
                        tagName: 'div',
                        class: 'product-info-modal-buttons',
                        children: [
                            {
                                tagName: 'button',
                                class: 'btn btn-primary add-to-cart',
                                text: 'В корзину',
                                attributes: [
                                    { type: 'button' }
                                ]
                            }
                        ]
                    }
                ]
            }
        ]
    }

    static notAuthorizedModal_forCreator = {
        tagName: 'div',
        class: 'js-delete-modal',
        attributes: [
            { id: 'not-authorized-alert-modal' }
        ],
        children: [
            {
                tagName: 'div',
                class: 'inner',
                children: [
                    { tagName: 'h5', text: 'Требуется авторизация' },
                    { tagName: 'p', text: 'Для добавления товара в корзину требуется авторизация' },
                    {
                        tagName: 'a',
                        class: 'btn btn-outline-dark',
                        text: 'Регистрация',
                        attributes: [
                            { href: '/register', role: 'button' }
                        ]
                    },
                    {
                        tagName: 'span',
                        text: ' '
                    },
                    {
                        tagName: 'a',
                        class: 'btn btn-dark',
                        text: 'Вход',
                        attributes: [
                            { href: '/login', role: 'button' }
                        ]
                    }
                ]
            }
        ]
    }

    static emptyCartMessage_forCreator = {
        tagName: 'div',
        class: 'empty-cart-message-wrapper',
        children: [
            {
                tagName: 'h5',
                text: 'Корзина пуста :('
            }
        ]
    }
}