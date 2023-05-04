import HTMLElements from "./HTMLElements";

export default class ElementsCreator {

    static createModal() {
        const modal = document.createElement('div')
        modal.className = HTMLElements.customModal.class

        return modal
    }

}