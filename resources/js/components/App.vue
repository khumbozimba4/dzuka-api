<template>
    <div class="Main__Wrapper">
        <SideBar />
        <div class="Page__Content">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
import SideBar from "./SideBar.vue";
export default {
    components: {
        SideBar,
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrapper {
    display: flex;
    .Page__Content {
        flex: 0.8;
        background: rgb(245 245 245);
        overflow-y: scroll;
    }
}
</style>
