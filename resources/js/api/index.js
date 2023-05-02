import axios from "axios";
const baseUrl = "api";

export class API {
    static listProducts(page = 1) {
        return axios.get(`${baseUrl}/products`, {
            params: {
                page: page,
            },
        });
    }

    static listAudits(page = 1) {
        return axios.get(`${baseUrl}/submit-audit-stock`, {
            params: {
                page: page,
            },
        });
    }

    static listCategories(page = 1) {
        return axios.get(`${baseUrl}/categories`, {
            params: {
                page: page,
            },
        });
    }

    static listSales(page = 1) {
        return axios.get(`${baseUrl}/sales`, {
            params: {
                page: page,
            },
        });
    }

    static listSuppliers(page = 1) {
        return axios.get(`${baseUrl}/suppliers`, {
            params: {
                page: page,
            },
        });
    }

    static listUsers(page = 1) {
        return axios.get(`${baseUrl}/users`, {
            params: {
                page: page,
            },
        });
    }

    static listExpenses(page = 1) {
        return axios.get(`${baseUrl}/expenses`, {
            params: {
                page: page,
            },
        });
    }

    static listSummaries() {
        return axios.get(`${baseUrl}/summaries`);
    }

    static searchCategory(name) {
        return axios.get(`api/categories/${name}/search`);
    }
    static getCategoryProducts(category) {
        return axios.get(`api/categories/${category}`);
    }
    static addCategory(body) {
        return axios.post(`api/categories`, body);
    }
    static updateCategory(category, body) {
        return axios.patch(`api/categories/${category}`, body);
    }
    static deleteCategory(category) {
        return axios.delete(`api/categories/${category}`);
    }
}
