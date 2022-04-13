<template>
    <div class="Main__Wrapper">
        <Icon Icon class="Icon" />
        <p>{{ Title }}</p>
    </div>
</template>

<script>
export default {
    props: ["Icon", "Title"],
};
</script>

<style lang="scss" scoped>
.Main__Wrapper {
    padding: 10px;
    display: flex;
    gap: 12px;

    .Icon {
        height: 30px;
        object-fit: contain;
        color: white;
    }
}
</style>
