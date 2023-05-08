import axios from "axios";
import { createStore } from "vuex";
import router from "../router";

const store = createStore({
    state() {
        return {
            mobile: null,
            mobileNav: false,
            windowWidth: null,
            user: null,
            userInfo: null,
            errorMessage: null,
            error: false,
            isLogged: false,
            isLoading: false,
            registerModal: false,
            userAdded: false,
            userProfileModal: false,
            histories: [],
        };
    },

    getters: {
        mobile: (state) => state.mobile,
        mobileNav: (state) => state.mobileNav,
        errorMessage: (state) => state.errorMessage,
        error: (state) => state.error,
        isLogged: (state) => state.isLogged,
        isLoading: (state) => state.isLoading,
        user: (state) => state.user,
        userInfo: (state) => state.userInfo,
        registerModal: (state) => state.registerModal,
        userAdded: (state) => state.userAdded,
        userProfileModal: (state) => state.userProfileModal,
        histories: (state) => state.histories,
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
        changeLoading({ commit }) {
            this.isLoading = !this.isLoading;
            commit("setIsLoading", this.isLoading);
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
                .post("api/login", credentials)
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
                    commit("setErrorMessage", "Incorrect email or password");
                });
        },
        register({ commit }, credentials) {
            this.isLoading = true;
            commit("setIsLoading", this.isLoading);
            axios
                .post("api/auth/register", credentials)
                .then(({ data }) => {

                    if (data) {
                        this.isLoading = false;
                        commit("setIsLoading", this.isLoading);
                        this.userAdded = !this.userAdded;
                        this.registerModal = !this.registerModal;
                        commit("setUserAdded", this.userAdded);
                        commit("setRegisterModal", this.registerModal);

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
        logout({ commit }) {
            commit("clearUserData");
        },
        updateUserInfo({ commit }, data) {
            commit("setUpdatedUserInfo", data);
        },
    },

    mutations: {
        setMobile: (state, mobile) => (state.mobile = mobile),
        setHistories: (state, histories) => (state.histories = histories),
        setIsLoading: (state, isLoading) => (state.isLoading = isLoading),
        setUserAdded: (state, userAdded) => (state.userAdded = userAdded),
        setUserProfileModal: (state, userProfileModal) =>
            (state.userProfileModal = userProfileModal),
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
            localStorage.setItem("user", JSON.stringify(userData));
            axios.defaults.headers.common.Authorization = `Bearer ${userData.token}`;
        },
        setUpdatedUserInfo(state, data) {
            state.userInfo = data.user;
        },
        clearUserData() {
            localStorage.removeItem("user");
            location.reload();
        },
    },
});

export default store;
