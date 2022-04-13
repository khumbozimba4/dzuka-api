import Vue from "vue";
import Vuex from "vuex";
import axios from "axios";

Vue.use(Vuex);

// axios.defaults.baseURL = "http://127.0.0.1:8000/api";

export default new Vuex.Store({
    state: {
        mobile: false,
        mobileNav: false,
        windowWidth: null,
        user: null,
        userInfo: null,
        name: null,
        errorMessage: null,
        error: false,
        isLogged: false,
        loginModal: false,
        registerModal: false,
    },

    getters: {
        mobile: (state) => state.mobile,
        mobileNav: (state) => state.mobileNav,
        loginModal: (state) => state.loginModal,
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

        toggleMobileNav({ commit }) {
            this.mobileNav = !this.mobileNav;
            commit("setMobileNav", this.mobileNav);
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
                        this.loginModal = false;
                        commit("setIsLogged", this.isLogged);
                        commit("setLoginModal", this.loginModal);
                        this.isLoading = false;
                        commit("setIsLoading", this.isLoading);
                    }
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
                        this.loginModal = !this.loginModal;
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
        setIsLiked: (state, isLiked) => (state.isLiked = isLiked),
        setIsLoading: (state, isLoading) => (state.isLoading = isLoading),
        setLoginModal: (state, loginModal) => (state.loginModal = loginModal),
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
        setProfileInitials(state) {
            state.profileInitials =
                state.name.match(/(\b\S)?/g).join("") +
                state.name.match(/(\b\S)?/g).join("");
        },
    },

    modules: {},
});
