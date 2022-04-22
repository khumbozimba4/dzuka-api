import axios from "axios";
import { createStore } from "vuex";
import router from "../router";
axios.defaults.baseURL = "api";

const store = createStore({
    state() {
        return {
            mobile: "yes",
            mobileNav: false,
            windowWidth: null,
            user: null,
            userInfo: null,
            errorMessage: null,
            error: false,
            isLogged: false,
            registerModal: false,
        };
    },

    getters: {
        mobile: (state) => state.mobile,
        mobileNav: (state) => state.mobileNav,
        errorMessage: (state) => state.errorMessage,
        error: (state) => state.error,
        isLogged: (state) => state.isLogged,
        user: (state) => state.user,
        userInfo: (state) => state.userInfo,
        registerModal: (state) => state.registerModal,
    },

    actions: {
        checkScreen({ commit }) {
            this.windowWidth = window.innerWidth;
            if (this.windowWidth <= 750) {
                this.mobile = true;
                commit("setMobile", this.mobile);
            } else if (this.windowWidth > 750) {
                this.mobile = false;
                this.mobileNav = false;
                commit("setMobile", this.mobile);
                commit("setMobileNav", this.mobileNav);
            }
        },
        changeLoginModal({ commit }) {
            this.loginModal = !this.loginModal;
            commit("setLoginModal", this.loginModal);
        },
        changeRegisterModal({ commit }) {
            this.registerModal = !this.registerModal;
            commit("setRegisterModal", this.registerModal);
        },
        login({ commit }, credentials) {
            this.isLoading = true;
            commit("setIsLoading", this.isLoading);
            axios
                .post("/login", credentials)
                .then(({ data }) => {
                    commit("setUserData", data);
                    if (data) {
                        this.isLogged = !this.isLogged;

                        commit("setIsLogged", this.isLogged);
                        this.isLoading = false;
                        commit("setIsLoading", this.isLoading);
                    }
                })
                .then(() => {
                    router.push("/dashboard");
                })
                .catch((err) => {
                    this.isLoading = false;
                    commit("setIsLoading", this.isLoading);
                    this.error = true;
                    this.errorMessage = err.message;
                    commit("setError", this.error);
                    commit("setErrorMessage", this.errorMessage);
                });
        },
        register({ commit }, credentials) {
            axios
                .post("/register", credentials)
                .then(({ data }) => {
                    commit("setUserData", data);
                    if (data) {
                        this.isLogged = !this.isLogged;
                        this.registerModal = !this.registerModal;
                        commit("setIsLogged", this.isLogged);
                        commit("setRegisterModal", this.registerModal);
                        commit("setLoginModal", this.loginModal);
                    }
                })
                .catch((err) => {
                    this.error = true;
                    this.errorMessage = err.message;
                    commit("setError", this.error);
                    commit("setErrorMessage", this.errorMessage);
                });
        },
        logout({ commit }) {
            commit("clearUserData");
        },
    },

    mutations: {
        setMobile: (state, mobile) => (state.mobile = mobile),
        setIsLoading: (state, isLoading) => (state.isLoading = isLoading),
        setRegisterModal: (state, registerModal) =>
            (state.registerModal = registerModal),
        setMobileNav: (state, mobileNav) => (state.mobileNav = mobileNav),
        setError: (state, error) => (state.error = error),
        setIsLogged: (state, isLogged) => (state.isLogged = isLogged),
        setErrorMessage: (state, errorMessage) =>
            (state.errorMessage = errorMessage),
        setUserData(state, userData) {
            state.user = userData;
            state.userInfo = userData.user;
            state.name = userData.user.name;
            localStorage.setItem("user", JSON.stringify(userData));
            axios.defaults.headers.common.Authorization = `Bearer ${userData.token}`;
        },
        clearUserData() {
            localStorage.removeItem("user");
            location.reload();
        },
    },
});

export default store;
