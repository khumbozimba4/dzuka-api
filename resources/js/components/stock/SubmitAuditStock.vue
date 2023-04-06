<template>
    <div class="Modal">
        <form @submit.prevent="addToStock">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1>
                    <strong style="text-transform: capitalize"
                    >Submit Stock - {{ product.product_name }}</strong
                    >
                </h1>
                <button @click="close">Close</button>
            </div>
            <div class="Input__Container">
                <p>
                    Enter counted stock for <strong>{{ getDate() }}</strong> end of
                    business day
                </p>
                <br/>
            </div>
            <div class="Input__Container">
                <label for="input_stock"><strong>Quantity</strong> </label>
                <input name="input_stock" v-model="input_stock" type="number" required/>
            </div>

            <button>Save</button>
        </form>

        <div v-if="errorMessage">{{ errorMessage }}</div>
    </div>
</template>

<script>
import axios from "axios";
import moment from "moment";
import {mapActions, mapGetters} from "vuex";

export default {
    props: ["product"],
    emits: ["getProducts", "close"],
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
            this.changeLoading();
            axios
                .post(`api/submit-audit-stock`, {
                    stock_count: this.input_stock,
                    product_id: this.product.id,
                })
                .then(() => {
                    this.$emit("getProducts");
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.changeLoading();
                    this.errorMessage = err.message;
                });
        },
        getDate() {
            return moment(new Date()).format("LL");
        },
        close() {
            this.$emit("close");
        },
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrap {
    width: 50%;
    right: 25%;
    margin-top: 20px;
    position: absolute;
    border-top: 1px solid gray;
    background: #fff;
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
            margin-top: 5px;
            display: flex;
            flex-direction: column;

            input {
                margin-top: 5px;
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
