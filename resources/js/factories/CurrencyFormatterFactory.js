import currencyFormatter from "currency-formatter";
export class CurrencyFormatter{
    static getCurrency(amount = 0){
        return currencyFormatter.format(amount, { code: 'MWK' });
    }
}
