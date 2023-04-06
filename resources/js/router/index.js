import {createRouter, createWebHistory} from "vue-router";
import Dashboard from "../pages/Dashboard.vue";
import Stock from "../pages/Stock.vue";
import Categories from "../pages/categories";
import Products from "../pages/Products.vue";
import Sales from "../pages/Sales.vue";
import Users from "../pages/Users.vue";
import Expenses from "../pages/Expenses.vue";
import Login from "../pages/Login.vue";
import Suppliers from "../pages/Suppliers";

const routes = [
    {
        path: "/",
        name: "login",
        component: Login,
        meta: {
            title: "Login",
        },
    },
    {
        path: "/dashboard",
        name: "dashboard",
        component: Dashboard,
        meta: {
            title: "Dashboard",
        },
    },
    {
        path: "/categories",
        name: "categories",
        component: Categories,
        meta: {
            title: "Categories",
        },
    },
    {
        path: "/products",
        name: "products",
        component: Products,
        meta: {
            title: "Products",
        },
    },
    {
        path: "/stock",
        name: "stock",
        component: Stock,
        meta: {
            title: "Stock",
        },
    },
    {
        path: "/sales",
        name: "sales",
        component: Sales,
        meta: {
            title: "Sales",
        },
    },
    {
        path: "/expenses",
        name: "expenses",
        component: Expenses,
        meta: {
            title: "Expenses",
        },
    },
    {
        path: "/suppliers",
        name: "suppliers",
        component: Suppliers,
        meta: {
            title: "Suppliers",
        },
    },

    {
        path: "/users",
        name: "users",
        component: Users,
        meta: {
            title: "Users",
        },
    },
];
const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes,
});
export default router;
