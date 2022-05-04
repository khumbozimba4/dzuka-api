<template>
    <div class="Main__Wrap">
        <form @submit.prevent="addSale" v-if="addSaleOpen">
            <div class="Heading">
                <h1>Add sale</h1>
                <XCircleIcon class="Icon" @click="closeAddSale" />
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
            <button class="mt-4">Save</button>
            <div v-if="errMessage">{{ errMessage }}</div>
        </form>
        <!-- Success Add -->
        <div class="Second__Step" v-if="!addSaleOpen && addedSale">
            <div class="Heading">
                <h1>Add sale</h1>
                <XCircleIcon class="Icon" @click="closeAddSale" />
            </div>
            <h6>
                <span><CheckCircleIcon class="Icon" /></span>
                Sale successfully added.
            </h6>
            <p>
                SaleID: <strong>{{ addedSale.id }}</strong>
            </p>
            <p>
                Customer: <strong>{{ addedSale.customer_name }}</strong>
            </p>
        </div>
        <!-- Add products Form -->
        <div class="Add__Products__Wrap" v-if="!addSaleOpen && addedSale">
            <h1 class="pt-4">Add Products</h1>

            <div class="Input__Search">
                <div>
                    <SearchIcon class="Icon" />
                    <input
                        name="name"
                        placeholder="Search products"
                        v-model="search"
                    />
                    <!-- Search results -->
                    <div class="Product__List" v-if="search">
                        <div class="Heading">
                            <h1>SELECT PRODUCTS</h1>
                            <XCircleIcon class="Icon" @click="resetSearch" />
                        </div>
                        <div v-for="item in products" :key="item.id">
                            <label for="selected">
                                {{ item.product_name }}
                            </label>
                            <p>
                                <span class="text-green-500">{{
                                    item.stock
                                }}</span>
                                Available
                            </p>
                            <input
                                type="checkbox"
                                name="selected"
                                :value="item"
                                v-model="selected"
                                v-if="item.stock > 0"
                            />
                            <p v-else class="text-red-500">Out of Stock</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- selected products -->
            <div class="Selected__Products" v-if="selected">
                <h1 v-if="selected">Products Sold</h1>
                <div v-for="(item, index) in selected" :key="item.id">
                    <form
                        @submit.prevent="
                            addProduct(
                                item.id,
                                index,
                                item.stock,
                                item.price,
                                item.product_name
                            )
                        "
                    >
                        <p>
                            <strong>{{ index + 1 }}</strong>
                        </p>
                        <p>{{ item.product_name }}</p>
                        <div class="Input__Wrap">
                            <label for="quantity">Quantity</label>
                            <input
                                type="number"
                                name="quantity"
                                v-model="quantity"
                                required
                            />
                        </div>
                        <p>
                            Price total:
                            <strong>K {{ this.quantity * item.price }}</strong>
                        </p>
                        <button
                            type="submit"
                            v-if="this.quantity <= item.stock"
                        >
                            Save
                        </button>
                        <p class="text-red-500" v-else>
                            Quantity exceeds stock
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { XCircleIcon } from "@heroicons/vue/solid";
import { CheckCircleIcon, SearchIcon } from "@heroicons/vue/outline";
import axios from "axios";
import { mapActions, mapGetters } from "vuex";
export default {
    components: {
        XCircleIcon,
        CheckCircleIcon,
        SearchIcon,
    },
    emits: ["closeAddSale", "getSales"],
    data() {
        return {
            addProductOpen: false,
            addSaleOpen: true,
            customer_name: "",
            customer_contact: "",
            date: null,
            description: "",
            errMessage: "",
            addedSale: {},
            products: [],
            search: "",
            selected: [],
            quantity: null,
        };
    },
    computed: {
        ...mapGetters(["userInfo"]),
    },
    methods: {
        ...mapActions(["changeLoading"]),
        addSale() {
            this.changeLoading();
            axios
                .post("api/sales/store", {
                    customer_name: this.customer_name,
                    customer_contact: this.customer_contact,
                    date: this.date,
                    description: this.description,
                })
                .then((response) => {
                    this.addedSale = response.data;
                })
                .then(() => {
                    this.addSaleOpen = false;
                    this.addProductOpen = true;
                })
                .then(() => {
                    this.$emit("getSales");
                })
                .then(() => {
                    axios.post("api/transactions/store", {
                        user_id: this.userInfo.id,
                        transaction_name: "Add Sale",
                        description: `Added sale Id: ${this.addedSale.id} of Customer: ${this.addedSale.customer_name}`,
                    });
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.changeLoading();
                    this.errMessage = err.message;
                });
        },
        addProduct(id, index, stock, price, productName) {
            if (this.quantity !== "") {
                this.changeLoading();
                axios
                    .post(`api/sales/product/${this.addedSale.id}/store`, {
                        quantity: this.quantity,
                        product_id: id,
                        total_price: this.quantity * price,
                    })
                    .then(() => {
                        this.subtractFromInventory(id, stock);
                    })
                    .then(() => {
                        this.selected.splice(index, 1);
                    })

                    .then(() => {
                        this.updateSale(this.addedSale.id, price);
                    })
                    .then(() => {
                        this.$emit("getSales");
                    })
                    .then(() => {
                        this.quantity = "";
                    })
                    .then(() => {
                        axios.post("api/transactions/store", {
                            user_id: this.userInfo.id,
                            transaction_name: "Add Product Sale",
                            description: `Added Product Id: ${productName} to Sale Id: ${this.addedSale.id}`,
                        });
                    })
                    .then(() => {
                        this.changeLoading();
                    })
                    .catch((err) => {
                        this.changeLoading();
                        this.errMessage = err.message;
                    });
            } else {
                return;
            }
        },
        subtractFromInventory(id, stock) {
            axios.patch(`api/products/${id}/inventory/subtract`, {
                stock: stock - this.quantity,
                recently_subtracted: this.quantity,
                previous_stock: stock,
            });
        },
        updateSale(id, price) {
            axios
                .patch(`api/sales/${id}/amount/update`, {
                    amount: this.quantity * price,
                })
                .catch((err) => {
                    this.errMessage = err.message;
                });
        },
        closeAddSale() {
            this.$emit("closeAddSale");
            this.$emit("getSales");
        },
        resetSearch() {
            this.search = "";
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
                    this.errMessage = err.message;
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
    z-index: 99;
    top: 50px;
    right: 200px;
    border-radius: 5px;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
        0 4px 6px -4px rgb(0 0 0 / 0.1);
    padding: 20px;

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
    button {
        padding: 5px 10px;
        background-color: rgb(30 41 59);
        color: #fff;
        border-radius: 3px;

        &:hover {
            background: rgb(15 23 42);
        }
    }

    h1 {
        font-weight: 800;
    }

    form {
        width: 500px;

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
    .Add__Products__Wrap {
        width: 500px;

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
                    display: flex;
                    flex-direction: column;
                    position: absolute;
                    width: 400px;
                    left: 300px;
                    bottom: -50px;
                    background-color: #fff;
                    border-radius: 5px;
                    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
                        0 4px 6px -4px rgb(0 0 0 / 0.1);
                    padding: 20px;
                    border-top: 1px solid gray;
                    border-left: 1px solid gray;
                    h1 {
                        padding: 10px 0;
                    }
                    div {
                        width: 100%;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        border-top: 1px solid rgb(229 229 229);
                        padding: 5px 0;
                    }
                }
            }
        }
        .Selected__Products {
            margin-top: 20px;

            div {
                border-top: 1px solid rgb(229 229 229);
                padding: 5px;

                form {
                    display: flex;
                    align-items: center;
                    width: 100%;
                    justify-content: space-between;
                    .Input__Wrap {
                        display: flex;
                        align-items: center;
                        gap: 5px;
                        input {
                            height: 30px;
                            width: 50px;
                            border-radius: 2px;
                        }
                    }
                }
            }
        }
    }
}
</style>
