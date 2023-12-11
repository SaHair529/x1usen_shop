import Inputmask from "inputmask/lib/inputmask";

export default class RegistrationController {
    static init() {
        this.addMaskToPhoneInput()
    }

    static addMaskToPhoneInput() {
        const phoneInput = document.getElementById('registration_form_phone')
        if (!phoneInput)
            return

        Inputmask({mask: '+7 (999)-999-99-99'}).mask(phoneInput)
    }
}