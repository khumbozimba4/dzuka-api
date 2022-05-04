<template>
    <div class="Main__Wrap">
        <form @submit.prevent="addProduct">
            <h1>Add Product</h1>
            <div class="Input__Container">
                <label for="name">Name</label>
                <input name="name" v-model="name" />
            </div>
            <div class="Input__Container">
                <label for="price">Price (MWK)</label>
                <input name="price" type="number" v-model="price" />
            </div>
            <div class="Input__Container">
                <label for="description">Description</label>
                <textarea
                    id="w3review"
                    name="description"
                    v-model="description"
                    rows="4"
                    cols="30"
                ></textarea>
            </div>

            <button>Add</button>

            <div v-if="errorMessage">{{ errorMessage }}</div>
        </form>
    </div>
</template>

<script>
import axios from "axios";
import { mapActions } from "vuex";
export default {
    emits: ["getProducts", "closeModal"],
    props: ["category_id"],
    data() {
        return {
            description: "",
            name: "",
            price: null,
            errorMessage: null,
        };
    },
    computed: {},
    methods: {
        ...mapActions(["changeLoading"]),
        addProduct() {
            this.changeLoading();
            axios
                .post("api/products/store", {
                    product_name: this.name,
                    description: this.description,
                    price: this.price,
                    category_id: this.category_id,
                })
                .then(() => {
                    this.$emit("closeModal");
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
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrap {
    position: absolute;
    border-top: 1px solid gray;
    background: #fff;
    top: 50px;
    right: 200px;
    border-radius: 5px;
    width: 500px;
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
