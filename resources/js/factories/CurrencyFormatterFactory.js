import currencyFormatter from "currency-formatter";
export class CurrencyFormatter{
    static getCurrency(amount = 0, code = ""){
        return currencyFormatter.format(amount, { code: code });
    }
}
