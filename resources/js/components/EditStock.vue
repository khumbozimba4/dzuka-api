<template>
    <div class="Main__Wrap">
        <form @submit.prevent="addToStock">
            <div class="Input__Container">
                <p>
                    Current Value
                    <span
                        ><strong>{{ this.currentStock }}</strong>
                    </span>
                </p>
            </div>
            <div class="Input__Container">
                <label for="added_stock"
                    ><strong>Add to stock (Quantity)</strong>
                </label>
                <input name="added_stock" v-model="added_stock" type="number" />
            </div>

            <button>Save</button>
        </form>
        <div v-if="errorMessage">{{ errorMessage }}</div>
    </div>
</template>

<script>
import axios from "axios";
export default {
    props: ["currentStock", "productID"],
    emits: ["getProducts"],
    data() {
        return {
            added_stock: null,
            errorMessage: "",
            stock: null,
        };
    },
    methods: {
        addToStock() {
            this.stock = this.added_stock + this.currentStock;

            axios
                .patch(`api/products/${this.productID}/update`, {
                    stock: this.stock,
                    previous_stock: this.currentStock,
                    recently_allocated: this.added_stock,
                })
                .then(() => {
                    this.$emit("getProducts");
                })
                .catch((err) => {
                    this.errorMessage = err.message;
                });
        },
    },
};
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
