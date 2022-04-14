<template>
    <div class="Main__Wrap">
        <form action="">
            <div class="Input__Container">
                <p>
                    Current Value <span><strong>25</strong> </span>
                </p>
            </div>
            <div class="Input__Container">
                <label for="name"><strong>New value</strong> </label>
                <input name="name" v-model="name" />
            </div>

            <button>Save</button>
        </form>
    </div>
</template>

<script>
export default {};
</script>

<style lang="scss" scoped>
.Main__Wrap {
    position: absolute;
    border-top: 1px solid gray;
    background: #fff;
    top: 30px;
    right: 200px;
    border-radius: 5px;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
        0 4px 6px -4px rgb(0 0 0 / 0.1);

    form {
        padding: 20px;

        .Input__Container {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            input {
                background: none;
                outline: none;
                border-bottom: 1px solid gray;
                color: gray;
            }
        }
        button {
            padding: 5px 10px;
            background-color: rgb(30 41 59);
            color: #fff;
            border-radius: 3px;
            margin-top: 10px;

            &:hover {
                background: rgb(15 23 42);
            }
        }
    }
}
</style>
