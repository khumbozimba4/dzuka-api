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
            <button
                @click="option = 1"
                :class="[option == 1 ? activeClass : inActive]"
            >
                Add
            </button>
            <button
                @click="option = 2"
                :class="[option == 2 ? activeClass : inActive]"
            >
                Subtract
            </button>
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
import { mapActions, mapGetters } from "vuex";
export default {
    props: ["currentStock", "productID"],
    emits: ["getProducts"],
    data() {
        return {
            input_stock: null,
            errorMessage: "",
            stock: null,
            option: 1,
            activeClass: "Button__Active",
            inActive: "Button__inActive",
        };
    },
    computed: {
        ...mapGetters(["userInfo"]),
    },
    methods: {
        ...mapActions(["changeLoading"]),
        addToStock() {
            this.stock = this.input_stock + this.currentStock;
            this.changeLoading();
            axios
                .patch(`api/products/${this.productID}/update`, {
                    stock: this.stock,
                    previous_stock: this.currentStock,
                    recently_allocated: this.input_stock,
                })
                .then(() => {
                    this.$emit("getProducts");
                })
                .then(() => {
                    axios.post("api/transactions/store", {
                        user_id: this.userInfo.id,
                        transaction_name: "Add Stock",
                        description: `Added  ${this.input_stock} Items to Product Id: ${this.productID}`,
                    });
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.changeLoading();
                    this.errorMessage = err.message;
                });
        },
        subtractFromStock() {
            this.stock = this.currentStock - this.input_stock;
            this.changeLoading();

            axios
                .patch(`api/products/${this.productID}/inventory/subtract`, {
                    stock: this.stock,
                    previous_stock: this.currentStock,
                    recently_subtracted: this.input_stock,
                })
                .then(() => {
                    this.$emit("getProducts");
                })
                .then(() => {
                    axios.post("api/transactions/store", {
                        user_id: this.userInfo.id,
                        transaction_name: "Remove Stock",
                        description: `Removed  ${this.input_stock} Items from Product Id: ${this.productID}`,
                    });
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.changeLoading();
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
    right: 80px;
    border-radius: 5px;
    padding: 20px;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
        0 4px 6px -4px rgb(0 0 0 / 0.1);

    .Select__Boxes {
        display: flex;
        width: 100%;
        margin: 5px 0;
        border: 1px solid lightgray;

        .Button__Active {
            padding: 10px 20px;
            color: #fff;
            flex: 0.5;
            background-color: rgb(85, 120, 175);
        }
        .Button__inActive {
            padding: 10px 20px;
            color: #000;
            flex: 0.5;
            background-color: #fff;
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
