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

    static addProduct(formData) {
        return axios.post(`${baseUrl}/products`, formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });
    }

    static deleteProduct(product) {
        return axios.delete(`api/products/${product}`);
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

    static listCategoryProducts(category) {
        return axios.get(`api/categories/${category}`);
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

    static addSupplier(data) {
        return axios.post(`${baseUrl}/suppliers`, data);
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

    static addExpense(data) {
        return axios.post(`${baseUrl}/expenses`, data);
    }

    static updateExpense(expense, data) {
        return axios.patch(`${baseUrl}/expenses/${expense}`, data);
    }

    static deleteExpense(expense) {
        return axios.patch(`${baseUrl}/expenses/${expense}`);
    }

    static listReports() {
        return axios.get(`${baseUrl}/reports`);
    }

    static searchCategory(name) {
        return axios.get(`api/categories/${name}/search`);
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
