<template>
    <div class="MainWrap">
        <div class="Contents">
            <div class="Question">Confirm delete ?</div>
            <div class="Options">
                <button class="Cancel" @click="toggleCancel">Cancel</button
                ><button class="Delete" @click="toggleDelete">Delete</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    emits: ["toggleCancel", "toggleDelete"],
    methods: {
        toggleCancel() {
            this.$emit("toggleCancel");
        },
        toggleDelete() {
            this.$emit("toggleDelete");
        },
    },
};
</script>

<style lang="scss" scoped>
.MainWrap {
    z-index: 9999;
    position: absolute;
    width: 100vw;
    height: 100vh;
    top: 0;
    right: 0;

    background-color: rgba(0, 0, 0, 0.8);
    display: grid;
    place-items: center;

    .Contents {
        background: #fff;
        padding: 50px;
        .Question {
            font-weight: 800;
        }
        .Options {
            display: flex;
            gap: 20px;
            margin-top: 10px;

            button {
                padding: 10px 20px;
                border: none;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1),
                    0 2px 4px -2px rgb(0 0 0 / 0.1);
                border-radius: 2px;
                font-weight: 700;
            }
            .Cancel {
                color: rgb(36, 36, 36);
                border: 1px solid #555;
            }
            .Delete {
                color: rgb(255, 255, 255);
                background-color: rgb(192, 51, 51);
            }
        }
    }
}
</style>
