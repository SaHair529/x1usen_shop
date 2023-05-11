import HTMLElements from "./HTMLElements"
import ElementsCreator from './ElementsCreator'

export default class Renderer {
    static updatePageAfterAddProductToCart(responseData) {
        this.updateProductTotalBalance(responseData['product_total_balance'])
        if (document.querySelector('.'+HTMLElements.addToCartButton.class) != null)
            this.swapToCartButtonWithProductCounter(responseData['quantity'])
        else
            this.updateCounterValue(responseData['quantity'])
    }

    static updateProductTotalBalance(totalBalance) {
        const productTotalBalanceHTag = document.querySelector('.'+HTMLElements.totalBalanceHTag.class+' span:last-child')
        productTotalBalanceHTag.textContent = totalBalance+'шт'
    }

    static updateCounterValue(productQuantity) {
        document.querySelector('.'+HTMLElements.quantityCounter.amountWrapper.class).textContent = productQuantity
    }

    static swapToCartButtonWithProductCounter(productQuantity) {
        const counter = ElementsCreator.createProductCounter(productQuantity)
        document.querySelector('.'+HTMLElements.toCartButton.class).replaceWith(counter)
    }
}