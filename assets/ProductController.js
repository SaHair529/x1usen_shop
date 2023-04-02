
export default class ProductController {
    static addToCart(productId) {
        fetch(`/cart/add_item?item_id=${productId}`).then(resp => {
            switch (resp.status) {
                case 200:
                    resp.text().then(responseText => {
                        if (responseText === 'ok') {
                            alert('Успешно')
                        }
                        else if (responseText === 'already in cart') {
                            alert('Товар уже в корзине')
                        }
                        else if (responseText === 'out of stock') {
                            alert('Товара нет в наличии');
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