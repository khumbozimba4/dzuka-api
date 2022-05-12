<template>
    <div class="Main__Wrapper">
        <ConfirmDelete
            @toggleCancel="toggleCancel"
            @toggleDelete="toggleDelete"
            v-if="confirmDelete"
        />
        <div class="NavBar__Container">
            <div class="Title">
                <p><strong>SaleID</strong></p>
            </div>

            <div class="Options">
                <strong>{{ sale?.id }}</strong>
            </div>
        </div>

        <div class="Contents__Container">
            <div class="Heading">
                <div class="Left__Side">
                    <p><strong>Customer</strong> : {{ sale?.customer_name }}</p>
                    <p>
                        <strong>Total Amount</strong> : K{{ sale?.sale_amount }}
                    </p>
                </div>
                <div class="Right__Side">
                    <div
                        class="Add__Category"
                        @click="addProductsOpen = !addProductsOpen"
                        v-if="userInfo.role !== finance"
                    >
                        Add Products
                    </div>
                    <AddSaleProducts
                        v-if="addProductsOpen"
                        :sale_Id="sale_id"
                        @getProducts="getSale"
                        @closeAddProducts="addProductsOpen = !addProductsOpen"
                    />
                </div>
            </div>

            <div class="Table__Container" v-if="!errorMessage">
                <table class="Table">
                    <thead class="Table__Head">
                        <tr class="Tr">
                            <td>#</td>
                            <td>Product</td>
                            <td>Price</td>
                            <td>Quantity</td>
                            <td>Total Price</td>
                            <!-- <td v-if="userInfo.role !== finance">Delete</td> -->
                        </tr>
                    </thead>
                    <tbody class="Table__Body">
                        <tr
                            class="Tr"
                            v-for="(product, index) in products"
                            :key="product.id"
                        >
                            <td>
                                <strong>{{ index + 1 }}</strong>
                            </td>
                            <td>{{ product.product_name }}</td>
                            <td>K{{ product.price }}</td>
                            <td>{{ product.pivot.quantity }}</td>
                            <td>
                                K{{ product.price * product.pivot.quantity }}
                            </td>
                            <!-- <td class="Icons" v-if="userInfo.role !== finance">
                                <TrashIcon
                                    class="Icon Icon_Delete"
                                    @click="toggleDeleteProduct(product)"
                                />
                            </td> -->
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else>{{ errorMessage }}</div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import AddSaleProducts from "../components/AddSaleProducts.vue";
import ConfirmDelete from "../components/ConfirmDelete.vue";
import { TrashIcon } from "@heroicons/vue/outline";
import { mapActions, mapGetters } from "vuex";
export default {
    components: {
        AddSaleProducts,
        ConfirmDelete,
        TrashIcon,
    },
    data() {
        return {
            products: [],
            sale_id: null,
            sale: null,
            errorMessage: null,
            addProductsOpen: false,
            confirmDelete: false,
            deletedItem: null,
            finance: "",
        };
    },
    computed: {
        ...mapGetters(["userInfo"]),
    },
    created() {
        this.getSale();
        this.setFinanceVariable();
    },
    methods: {
        ...mapActions(["changeLoading"]),
        setFinanceVariable() {
            this.finance = "finance";
        },
        getSale() {
            this.changeLoading();
            this.sale_id = this.$route.params.sale_id;
            axios
                .get(`api/sales/${this.sale_id}/show`)
                .then((res) => {
                    this.sale = res.data;
                })
                .then(() => {
                    this.getProducts();
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.changeLoading();
                    this.errorMessage = err.message;
                });
        },
        getProducts() {
            axios
                .get(`api/sales/${this.sale_id}/products`)
                .then((res) => {
                    this.products = res.data;
                })
                .catch((err) => {
                    this.errorMessage = err.message;
                });
        },
        toggleDeleteProduct(product) {
            this.deletedItem = product;
            this.confirmDelete = true;
        },
        toggleDelete() {
            this.changeLoading();
            axios
                .delete(`api/sales/product/${this.sale_id}/destroy`, {
                    product_id: this.deletedItem.id,
                })
                .then(() => {
                    this.confirmDelete = false;
                })
                .then(() => {
                    axios.patch(`api/sales/${this.sale_id}/amount/update`, {
                        amount: 0 - this.deletedItem.pivot.total_price,
                    });
                })
                .then(() => {
                    this.getSale();
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.errorMessage = err.message;
                });
        },
        toggleCancel() {
            this.confirmDelete = false;
        },
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrapper {
    width: 100%;
    display: flex;
    flex-direction: column;
    .NavBar__Container {
        background-color: #fff;
        display: flex;
        align-items: center;
        width: 100%;
        padding: 10px;

        .Title {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 0 10px;
            border-right: 1px solid gray;
            margin-right: 25px;
            .Icon {
                height: 30px;
                object-fit: contain;
            }
        }

        .Search__Bar {
            display: flex;
            align-items: center;
            background-color: rgb(212 212 212);
            border-radius: 5px;
            .Input {
                background: none;
                border: 0px;
                padding: 10px 20px;
                width: 200px;

                &:focus {
                    outline: none;
                    border: 0px;
                }
            }
            .Search__Icon {
                padding: 5px 20px;
                height: 30px;
                color: rgb(115 115 115);
            }
        }
    }
    .Contents__Container {
        margin: 20px;
        background-color: #fff;
        border-radius: 3px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
            0 4px 6px -4px rgb(0 0 0 / 0.1);
        .Heading {
            position: relative;
            display: flex;
            justify-content: space-between;
            padding: 20px;
            border-bottom: 1px solid rgb(163 163 163);
            .Left__Side {
                display: flex;
                flex-direction: column;
                gap: 5px;
                cursor: pointer;

                .Icon {
                    height: 20px;
                    object-fit: contain;
                    cursor: pointer;
                }
            }
            .Right__Side {
                position: relative;
                display: flex;
                align-items: center;
                gap: 10px;
                .Add__Category {
                    padding: 5px 20px;
                    border: 1px solid rgb(115 115 115);
                    border-radius: 3px;
                    color: rgb(115 115 115);
                    cursor: pointer;
                    font-size: 15px;

                    &:hover {
                        color: rgb(82 82 82);
                    }
                }
                .Icon {
                    height: 30px;
                    object-fit: contain;
                    padding: 5px 20px;
                    border: 1px solid rgb(115 115 115);
                    border-radius: 3px;
                    color: rgb(115 115 115);
                    cursor: pointer;
                }
            }
        }
        .Table__Container {
            padding: 20px;
            .Table {
                width: 100%;

                .Table__Head {
                    font-weight: 800;
                    color: rgb(38 38 38);
                    .Tr {
                        height: 40px;
                    }
                }
                .Table__Body {
                    .Tr {
                        border-top: 1px solid rgb(229 229 229);
                        height: 40px;
                        .Icons {
                            display: flex;
                            gap: 30px;
                        }
                        td {
                            .Icon {
                                width: 25px;
                                object-fit: contain;
                                cursor: pointer;
                                color: rgb(23, 34, 49);
                                &:hover {
                                    color: rgb(2, 2, 3);
                                }
                            }
                            .Icon_Delete {
                                color: rgb(209, 74, 74);
                                &:hover {
                                    color: rgb(155, 23, 23);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
</style>
