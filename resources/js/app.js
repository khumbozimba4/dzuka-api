require("./bootstrap");
import Alpine from "alpinejs";
import { createApp } from "vue";
import router from "./router";
import App from "./components/App.vue";

const app = createApp({});
app.component("App", App);
app.use(router);
app.mount("#app");

window.Alpine = Alpine;
Alpine.start();
