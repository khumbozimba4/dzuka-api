<template>
    <div class="Main__Wrapper">
        <SideBar v-if="$store.state.user" />
        <div class="Page__Content">
            <router-view></router-view>
        </div>
        <RegisterModal v-if="$store.state.registerModal" />
        <Loading v-if="$store.state.isLoading" />
    </div>
</template>

<script>
import SideBar from "./SideBar.vue";
import RegisterModal from "./RegisterModal.vue";
import Loading from "./Loading.vue";
import { mapActions, mapGetters } from "vuex";
export default {
    components: {
        SideBar,
        RegisterModal,
        Loading,
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
