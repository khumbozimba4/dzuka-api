import axios from "axios";

export class API{
    static getProducts(){
        return axios.get('api/products');
    }
    static getCategories(){
        return axios.get('api/categories');
    }
    static searchCategory(name){
        return axios.get(`api/categories/${name}/search`);
    }
    static getCategoryProducts(category){
        return axios.get(`api/categories/${category}`);
    }
    static addCategory(body){
        return axios.post(`api/categories`, body);
    }
    static updateCategory(category, body){
        return axios.patch(`api/categories/${category}`, body);
    }
    static deleteCategory(category){
        return axios.delete(`api/categories/${category}`);
    }
}
