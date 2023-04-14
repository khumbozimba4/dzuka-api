import axios from "axios";

export class API{
    static getProducts(){
        return axios.get('api/products');
    }
}
