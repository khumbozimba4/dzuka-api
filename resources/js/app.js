require("./bootstrap");
// import Alpine from "alpinejs";
import { createApp } from "vue";
// import routes from "./routes";
import App from "./components/App.vue";
// import { createRouter, createWebHashHistory } from "vue-router";

// const router = createRouter({
//     history: createWebHashHistory(),
//     linkActiveClass: "",
//     routes,
// });

const app = createApp({});
app.component("App", App);

// app.use(router);

app.mount("#app");

// window.Alpine = Alpine;
// Alpine.start();
