<template>
    <div class="Main__Wrapper">
        <SideBar />
        <div class="Page__Content">
            <Body />
        </div>
        <router-view></router-view>
    </div>
</template>

<script>
import SideBar from "./SideBar.vue";
import Body from "./Body.vue";
export default {
    components: {
        SideBar,
        Body,
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrapper {
    widows: 100%;
    display: flex;
    .Page__Content {
        flex: 0.8;
        background: rgb(245 245 245);
    }
}
</style>
