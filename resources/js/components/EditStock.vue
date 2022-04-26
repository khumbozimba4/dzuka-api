<template>
    <div class="Main__Wrap">
        <div class="Input__Container">
            <p>
                Current Value
                <span
                    ><strong>{{ this.currentStock }}</strong>
                </span>
            </p>
        </div>
        <div class="Select__Boxes">
            <button @click="option = 1" class="Box1">Add</button>
            <button @click="option = 2" class="Box2">Subtract</button>
        </div>
        <form @submit.prevent="addToStock" v-if="option == 1">
            <div class="Input__Container">
                <label for="input_stock"
                    ><strong>Add to stock (Quantity)</strong>
                </label>
                <input name="input_stock" v-model="input_stock" type="number" />
            </div>

            <button>Save</button>
        </form>
        <form @submit.prevent="subtractFromStock" v-if="option == 2">
            <div class="Input__Container">
                <label for="input_stock"
                    ><strong>Subtract from stock (Quantity)</strong>
                </label>
                <input name="input_stock" v-model="input_stock" type="number" />
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
            input_stock: null,
            errorMessage: "",
            stock: null,
            option: 1,
        };
    },
    methods: {
        addToStock() {
            this.stock = this.input_stock + this.currentStock;

            axios
                .patch(`api/products/${this.productID}/update`, {
                    stock: this.stock,
                    previous_stock: this.currentStock,
                    recently_allocated: this.input_stock,
                })
                .then(() => {
                    this.$emit("getProducts");
                })
                .catch((err) => {
                    this.errorMessage = err.message;
                });
        },
        subtractFromStock() {
            this.stock = this.input_stock - this.currentStock;

            axios
                .patch(`api/products/${this.productID}/update`, {
                    stock: this.stock,
                    previous_stock: this.currentStock,
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
    padding: 20px;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
        0 4px 6px -4px rgb(0 0 0 / 0.1);

    .Select__Boxes {
        display: flex;
        width: 100%;
        padding: 10px 0;
        .Box1 {
            padding: 10px 20px;
            background-color: rgb(68, 207, 75);
            color: #fff;
            flex: 0.5;
        }
        .Box2 {
            padding: 10px 20px;
            background-color: rgb(189, 67, 67);
            color: #fff;
            flex: 0.5;
        }
    }

    form {
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
