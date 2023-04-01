
export default class ProductController {
    static addToCart(productId) {
        fetch(`/cart/add_item?item_id=${productId}`).then(resp => {
            switch (resp.status) {
                case 200:
                    resp.text().then(responseText => {
                        if (responseText === 'ok') {
                            alert('Успешно')
                        }
                        else if (responseText === 'almost in cart') {
                            alert('Товар уже в корзине')
                        }
                    })
                    break
                case 403:
                    resp.text().then(responseText => {
                        if (responseText === 'not authorized') {
                            alert('Требуется авторизация')
                        }
                    })
                    break
            }
        })
    }
}