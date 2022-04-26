<template>
    <div class="Main__Wrap">
        <div class="Image__Wrap">
            <img :src="'/images/' + src + '.png'" alt="" />
            <div class="Card__Note">{{ cardNote }}</div>
        </div>
        <p>{{ title }}</p>
    </div>
</template>

<script>
export default {
    props: ["src", "cardNote", "title"],
    components: {},
    data() {
        return {};
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrap {
    position: relative;
    width: 180px;
    padding: 20px;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
        0 4px 6px -4px rgb(0 0 0 / 0.1);
    cursor: pointer;
    p {
        font-weight: 900;
    }

    .Image__Wrap {
        img {
            height: 100px;
            object-fit: contain;
        }
        .Card__Note {
            position: absolute;
            top: -10px;
            right: -30px;
            padding: 15px;
            background-color: #000;
            border-radius: 9999px;
            color: #fff;
            font-weight: 900;
        }
    }
}
</style>
