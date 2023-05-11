export default class HTMLElements {
    static addToCartButton = {
        class: 'js-add-to-cart'
    }

    static totalBalanceHTag = {
        class: 'detail-full-info__total-balance'
    }

    static buttonsWrapper = {
        class: 'detail-full-info__cart-buttons'
    }

    static toCartButton = {
        class: 'js-add-to-cart'
    }

    static quantityCounter = {
        class: 'detail-full-info__quantity-counter',
        amountWrapper: {
            class: 'detail-full-info__amount'
        }
    }

    static quantityCounter_forCreator = {
        tagName: 'div',
        class: 'detail-full-info__quantity-counter',
        children: [
            {
                tagName: 'span',
                class: 'detail-full-info__minus-btn js-decrease-cart-item',
                text: '-'
            },
            {
                tagName: 'span',
                class: 'detail-full-info__amount',
                text: ''
            },
            {
                tagName: 'span',
                class: 'detail-full-info__plus-btn js-add-to-cart',
                text: '+'
            }
        ]
    }
}