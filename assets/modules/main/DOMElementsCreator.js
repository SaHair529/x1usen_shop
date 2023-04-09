export default class DOMElementsCreator {

    static createDetails404() {
        const details404 = document.createElement('div')
        const details404icon = document.createElement('p')

        details404.classList.add('details404')
        details404icon.className = 'details404icon'

        details404icon.innerText = 'Ничего не найдено :('

        details404.appendChild(details404icon)

        return details404
    }

    static createLoader() {
        const loader = document.createElement('div')
        const spinner = document.createElement('div')
        const spinnerInner = document.createElement('span')

        loader.className = 'loader'
        spinner.className = 'spinner-border text-danger'
        spinnerInner.className = 'visually-hidden'
        spinner.setAttribute('role', 'status')
        spinnerInner.textContent = 'Загрузка...'

        spinner.appendChild(spinnerInner)
        loader.appendChild(spinner)

        return loader
    }
}