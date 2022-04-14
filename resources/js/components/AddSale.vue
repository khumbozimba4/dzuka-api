<template>
    <div class="Main__Wrap">
        <form action="">
            <h1>Add sale</h1>
            <div class="Input__Container">
                <label for="name">Name</label>
                <input name="name" v-model="name" />
            </div>
            <div class="Input__Container">
                <label for="name">Product</label>
                <input name="name" v-model="name" />
            </div>
            <div class="Input__Container">
                <label for="name">Customer</label>
                <input name="name" v-model="name" />
            </div>
            <div class="Input__Container">
                <label for="description">Additional details</label>
                <textarea
                    id="w3review"
                    name="description"
                    rows="4"
                    cols="30"
                ></textarea>
            </div>

            <button>Add</button>
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
    top: 50px;
    right: 200px;
    border-radius: 5px;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
        0 4px 6px -4px rgb(0 0 0 / 0.1);

    form {
        padding: 20px;
        h1 {
            font-weight: 800;
        }
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
