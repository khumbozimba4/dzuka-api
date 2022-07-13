require("./bootstrap");
import Alpine from "alpinejs";
import { createApp } from "vue";
import store from "./store";
import router from "./router";
import App from "./App.vue";

const app = createApp({});
app.component("App", App);
app.use(router);
app.use(store);
app.mount("#app");

window.Alpine = Alpine;
Alpine.start();
