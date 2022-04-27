import { createRouter, createWebHistory } from "vue-router";
import Dashboard from "../pages/Dashboard.vue";
import Stock from "../pages/Stock.vue";
import Inventory from "../pages/Inventory.vue";
import Products from "../pages/Products.vue";
import Sales from "../pages/Sales.vue";
import Sale from "../pages/Sale.vue";
import Analytics from "../pages/Analytics.vue";
import About from "../pages/About.vue";
import Users from "../pages/Users.vue";
import Expenses from "../pages/Expenses.vue";
import Login from "../pages/Login.vue";
import Transactions from "../pages/Transactions.vue";
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
        path: "/inventory",
        name: "inventory",
        component: Inventory,
        meta: {
            title: "Inventory",
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
        path: "/sale",
        name: "sale",
        component: Sale,
        meta: {
            title: "Sale",
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
        path: "/analytics",
        name: "analytics",
        component: Analytics,
        meta: {
            title: "Analytics",
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
    {
        path: "/about",
        name: "about",
        component: About,
        meta: {
            title: "About",
        },
    },
    {
        path: "/transactions",
        name: "transactions",
        component: Transactions,
        meta: {
            title: "Transactions",
        },
    },
];
const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes,
});
export default router;
