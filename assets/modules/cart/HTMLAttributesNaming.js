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
        },
        showDetails: {
            class: 'js-show-details'
        }
    }

    static unitCard = {
        itemsLink: {
            class: 'unit-card__items-link'
        }
    }

    static productSumPriceCounter = {
        class: 'sum-price'
    }

    static unitNodesWindow = {
        unitsList: {
            class: 'units-list',
            unitsListItem: {
                class: 'units-list-item'
            }
        }
    }


    static imageModal_forCreator = {
        tagName: 'div',
        class: 'custom-modal detail-image-modal js-delete-modal',
        attributes: [
            { id: 'detail-image-modal' }
        ]
    }

    static unitsListItem_forCreator = {
        tagName: 'div',
        class: 'units-list-item',
        children: [
            {
                tagName: 'div',
                class: 'order',
                text: '22211' // todo
            },
            {
                tagName: 'div',
                class: 'name',
                text: 'Клапан впускной двигателя' // todo
            },
            {
                tagName: 'div',
                class: 'number',
                text: '22211-22003' // todo
            },
            {
                tagName: 'a',
                class: 'link',
                text: '', // todo
                attributes: [
                    { href: '#' } // todo
                ],
                children: [
                    {
                        tagName: 'span',
                        class: 'link-text',
                        text: 'Цены и аналоги'
                    }
                ]
            },
        ]
    }

    static unitNodesModal_forCreator = {
        tagName: 'div',
        class: 'custom-modal',
        children: [
            {
                tagName: 'div',
                class: 'modal-inner-fullscreen',
                children: [
                    {
                        tagName: 'div',
                        class: 'unit-nodes-window',
                        children: [
                            {
                                tagName: 'div',
                                class: 'js-close-units-modal',
                                text: 'Esc'
                            },
                            {
                                tagName: 'div',
                                class: 'left',
                                children: [
                                    {
                                        tagName: 'div',
                                        class: 'unit-nodes-image-map',
                                        children: [
                                            {
                                                tagName: 'img',
                                                class: 'unit-nodes-image'
                                            }
                                        ]
                                    }
                                ]
                            },
                            {
                                tagName: 'div',
                                class: 'right',
                                children: [
                                    {
                                        tagName: 'div',
                                        class: 'header',
                                        children: [
                                            {
                                                tagName: 'h4',
                                                class: 'right-title',
                                                text: 'Воздушный фильтр' // todo
                                            },
                                            {
                                                tagName: 'p',
                                                class: 'right-subtitle',
                                                text: 'Узел 20-240',
                                            }
                                        ]
                                    },
                                    {
                                        tagName: 'div',
                                        class: 'units-list',
                                    }
                                ]
                            }
                        ]
                    }
                ]
            }
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

    static calculationModalWindow_forCreator = {
        tagName: 'div',
        class: 'custom-calculation-modal-window',
        children: [
            { tagName: 'h4', text: 'Расчет стоимости доставки вручную' },
            { tagName: 'p', class: 'custom-calculation-modal-window__subtitle', text: 'Заполните поля формы и нажмите на "Расчитать стоимость"' },
            { tagName: 'hr' },
            {
                tagName: 'form',
                class: 'custom-calculation-modal-window__form',
                attributes: [ { id: 'calculate-form' } ],
                children: [
                    {
                        tagName: 'div',
                        class: 'input-group',
                        children: [
                            {
                                tagName: 'input',
                                class: 'form-control',
                                attributes: [{
                                    name: 'cargo_length',
                                    type: 'number',
                                    step: '0.01',
                                    placeholder: 'Длина, см',
                                    required: 'required'
                                }]
                            },
                            {
                                tagName: 'input',
                                class: 'form-control',
                                attributes: [{
                                    name: 'cargo_width',
                                    type: 'number',
                                    step: '0.01',
                                    placeholder: 'Ширина, см',
                                    required: 'required'
                                }]
                            }
                        ]
                    },
                    {
                        tagName: 'div',
                        class: 'input-group',
                        children: [
                            {
                                tagName: 'input',
                                class: 'form-control',
                                attributes: [{
                                    name: 'cargo_height',
                                    type: 'number',
                                    step: '0.01',
                                    placeholder: 'Высота, см',
                                    required: 'required'
                                }]
                            },
                            {
                                tagName: 'input',
                                class: 'form-control',
                                attributes: [{
                                    name: 'cargo_weight',
                                    type: 'number',
                                    step: '0.01',
                                    placeholder: 'Вес, кг',
                                    required: 'required'
                                }]
                            }
                        ]
                    },
                    {
                        tagName: 'div',
                        class: 'input-group',
                        children: [
                            {
                                tagName: 'input',
                                class: 'form-control',
                                attributes: [{
                                    name: 'derival_city',
                                    type: 'text',
                                    placeholder: 'Город отправления',
                                    required: 'required'
                                }]
                            },
                            {
                                tagName: 'input',
                                class: 'form-control',
                                attributes: [{
                                    name: 'arrival_city',
                                    type: 'text',
                                    placeholder: 'Город получения',
                                    required: 'required'
                                }]
                            }
                        ]
                    },
                    { tagName: 'br' },
                    {
                        tagName: 'button',
                        class: 'btn btn-outline-primary js-submit-calculate-form',
                        attributes: [ { type: 'submit' } ],
                        text: 'Расчитать стоимость'
                    }
                ]
            }
        ]
    }
}