import HTMLElements from "./HTMLElements"
import ElementsCreator from './ElementsCreator'

export default class Renderer {
    static updatePageAfterSuccessAddProductToCart(responseData) {
        this.updateProductTotalBalance(responseData['product_total_balance'])
        if (document.querySelector('button.'+HTMLElements.addToCartButton.class) != null)
            this.swapToCartButtonWithProductCounter(responseData['quantity'])
        else {
            this.updateCounterValue(responseData['quantity'])
            if (!responseData['has_more_product'])
                this.disablePlusButton()
        }
    }

    static updatePageAfterOutOfStock() {
        const inCartAmountWrapper = document.querySelector('span.'+HTMLElements.quantityCounter.amountWrapper.class)
        if (inCartAmountWrapper != null) {
            this.shakeElement(inCartAmountWrapper)
            this.disablePlusButton()
        }
        else {
            const addToCartBtn = document.querySelector('button.'+HTMLElements.addToCartButton.class)
            this.shakeElement(addToCartBtn)

        }

    }

    static disablePlusButton() {
        document.querySelector('span.'+HTMLElements.addToCartButton.class).classList.add('disabled')
    }

    static shakeElement(element) {
        element.classList.add('shake')
        setTimeout(() => {
            element.classList.remove('shake')
        }, 1000)
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