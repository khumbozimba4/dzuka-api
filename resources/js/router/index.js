import { createRouter, createWebHistory } from "vue-router";
import Dashboard from "../pages/Dashboard.vue";
import Stock from "../pages/Stock.vue";
import Inventory from "../pages/Inventory.vue";
import Analytics from "../pages/Analytics.vue";
import About from "../pages/About.vue";
import Users from "../pages/Users.vue";
const routes = [
    {
        path: "/",
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
        path: "/stock",
        name: "stock",
        component: Stock,
        meta: {
            title: "Stock",
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
];
const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes,
});
export default router;
