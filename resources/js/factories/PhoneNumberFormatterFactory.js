import {parsePhoneNumber} from "libphonenumber-js";

export class PhoneNumberFormatter{
    static getPhoneNumber(phone){
        return parsePhoneNumber(phone, 'MW').formatNational();
    }
}
