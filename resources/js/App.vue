<template>
    <div class="Main__Wrapper">
        <SideBar v-if="$store.state.user" />
        <div class="Page__Content">
            <router-view></router-view>
        </div>
        <RegisterModal v-if="$store.state.registerModal" />
        <Loading v-if="$store.state.isLoading" />
        <UserProfile v-if="$store.state.userProfileModal" />
    </div>
</template>

<script>
import SideBar from "./components/SideBar.vue";
import RegisterModal from "./components/user/RegisterModal.vue";
import Loading from "./components/Loading.vue";
import UserProfile from "./components/user/UserProfile.vue";
import { mapGetters } from "vuex";
export default {
    components: {
        SideBar,
        RegisterModal,
        Loading,
        UserProfile,
    },
    created() {
        this.checkUser();
    },
    methods: {
        checkUser() {
            if (!this.user) {
                this.$router.push("/");
            }
        },
    },
    computed: {
        ...mapGetters(["user"]),
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrapper {
    display: flex;
    .Page__Content {
        flex: 0.8;
        background: rgb(245 245 245);
    }
}
</style>
