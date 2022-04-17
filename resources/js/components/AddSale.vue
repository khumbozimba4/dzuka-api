<template>
    <div class="Main__Wrap">
        <form @submit.prevent="addSale" v-if="addSaleOpen">
            <div class="Heading">
                <h1>Add sale</h1>
                <XCircleIcon class="Icon" />
            </div>
            <div class="Input__Container">
                <label for="date">Date</label>
                <input name="date" type="date" v-model="date" />
            </div>
            <div class="Input__Container">
                <label for="customer_name">Customer Name</label>
                <input name="customer_name" v-model="customer_name" />
            </div>
            <div class="Input__Container">
                <label for="customer_contact">Customer Contact</label>
                <input name="customer_contact" v-model="customer_contact" />
            </div>
            <div class="Input__Container">
                <label for="description">Additional details</label>
                <textarea
                    id="w3review"
                    name="description"
                    v-model="description"
                    rows="4"
                    cols="30"
                ></textarea>
            </div>
            <button>Save</button>
            <div v-if="errMessage">{{ errMessage }}</div>
        </form>
        <!-- Success Add -->
        <div class="Second__Step" v-if="!addSaleOpen && addedProduct">
            <h6>
                <span><CheckCircleIcon class="Icon" /></span>
                Sale successfully added.
            </h6>
            <p>SaleID: {{ addedProduct.id }}</p>
            <p>Customer: {{ addedProduct.customer_name }}</p>
        </div>
        <!-- Add products Form -->
        <form
            @submit.prevent="addProductsSale"
            v-if="!addSaleOpen && addedProduct"
        >
            <h1 class="pt-4">Add Products</h1>

            <div class="Input__Search">
                <label for="name">Product</label>
                <div>
                    <SearchIcon class="Icon" />
                    <input name="name" placeholder="Search" v-model="search" />
                    <!-- Search results -->
                    <div class="Product__List" v-if="search">
                        <div v-for="selected in products" :key="selected.id">
                            <input type="checkbox" :value="selected" />
                        </div>
                    </div>
                </div>
            </div>
            <!-- selected products -->
            <div class="Selected__Products" v-if="selected">
                <div v-for="item in selected" :key="item.id">
                    <form @submit.prevent="addProduct(item.id)">
                        {{ item.product_name }}
                        <input type="text" name="quantity" v-model="quantity" />
                    </form>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import { XCircleIcon } from "@heroicons/vue/solid";
import { CheckCircleIcon, SearchIcon } from "@heroicons/vue/outline";
import axios from "axios";
export default {
    components: {
        XCircleIcon,
        CheckCircleIcon,
        SearchIcon,
    },
    data() {
        return {
            addProductOpen: false,
            addSaleOpen: true,
            customer_name: "",
            customer_contact: "",
            date: null,
            description: "",
            errMessage: "",
            addedProduct: {},
            products: [],
            search: "",
            selected: [],
        };
    },
    methods: {
        addSale() {
            axios
                .post("api/sales/store", {
                    customer_name: this.customer_name,
                    customer_contact: this.customer_contact,
                    date: this.date,
                    description: this.description,
                })
                .then((response) => {
                    this.addedProduct = response.data;
                })
                .then(() => {
                    this.addSaleOpen = false;
                    this.addProductOpen = true;
                })
                .catch((err) => {
                    this.errMessage = err.message;
                });
        },
    },
    watch: {
        search(value) {
            axios
                .get(`api/products/search/${value}`)
                .then((res) => {
                    this.products = res.data;
                })
                .catch((err) => {
                    console.log(err);
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
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
        0 4px 6px -4px rgb(0 0 0 / 0.1);
    padding: 20px;
    h1 {
        font-weight: 800;
    }

    form {
        width: 500px;
        .Heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            h1 {
                font-weight: 800;
            }
            .Icon {
                width: 20px;
                object-fit: contain;
                color: rgb(30 41 59);
                background: #fff;
                cursor: pointer;
            }
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
            div {
                display: flex;
                align-items: center;
                .Icon {
                    height: 20px;
                    object-fit: contain;
                }
            }
        }
        .Input__Search {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            border-bottom: 1px solid gray;
            div {
                position: relative;
                display: flex;
                align-items: center;
                .Icon {
                    height: 20px;
                    color: black;
                    object-fit: contain;
                }
                input {
                    background: none;
                    outline: none;
                    padding: 0 5px;
                }
                .Product__List {
                    position: absolute;
                    width: 300px;
                    left: 300px;
                    bottom: 0;
                    background-color: #fff;
                    border-radius: 5px;
                    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
                        0 4px 6px -4px rgb(0 0 0 / 0.1);
                    padding: 20px;
                    border-left: 1px solid gray;
                }
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
    .Second__Step {
        padding: 20px 0;
        h6 {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            .Icon {
                height: 20px;
                object-fit: contain;
                color: rgb(34 197 94);
            }
        }
    }
}
</style>
