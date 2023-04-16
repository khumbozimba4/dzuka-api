<template>
    <div class="Main__Wrapper">
        <ConfirmDelete
            @toggleCancel="toggleCancel"
            @toggleDelete="toggleDelete"
            @closeModal="isOpen = false"
            v-if="confirmDelete"
        />
        <div class="NavBar__Container">
            <div class="Title">
                <CollectionIcon class="Icon"/>
                <p>{{ categoryName }} | Products</p>
            </div>
            <div class="Search__Bar">
                <input type="text" class="Input" placeholder="Search..."/>
                <SearchIcon class="Search__Icon"/>
            </div>
            <div class="Options"></div>
        </div>

        <div class="Contents__Container">
            <div class="Heading">
                <div class="Left__Side">
                    <AdjustmentsIcon class="Icon"/>
                    Filters
                </div>
                <div class="Right__Side">
                    <div
                        class="Add__Category"
                        @click="isOpen = !isOpen"
                        v-if="userInfo.role !== finance"
                    >
                        Add Product
                    </div>
                    <PrinterIcon class="Icon"/>
                </div>
                <AddProduct
                    v-if="isOpen"
                    @getProducts="getProducts"
                    @closeModal="closeModal"
                    :category_id="category_id"
                />
            </div>

            <div class="Table__Container" v-if="!errorMessage">
                <table class="Table">
                    <thead class="Table__Head">
                    <tr class="Tr">
                        <td>#</td>
                        <td>Product name</td>
                        <td>Total Stock</td>
                        <td>Price (MWK)</td>
                        <td v-if="userInfo.role !== finance">Actions</td>
                    </tr>
                    </thead>
                    <tbody class="Table__Body">
                    <tr v-if="!products.length">
                        No products available!
                    </tr>
                    <tr
                        class="Tr"
                        v-for="(product, index) in products"
                        :key="product.id"
                    >
                        <td>
                            <strong>{{ index + 1 }}</strong>
                        </td>
                        <td>{{ product.product_name }}</td>
                        <td>{{ product.stock }}</td>
                        <td>{{ getCurrency(product.price)}}</td>
                        <td v-if="userInfo.role !== finance">
                            <TrashIcon
                                class="Icon Icon_Delete"
                                @click="toggleDeleteProduct(product.id)"
                            />
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div v-else>{{ errorMessage }}</div>
        </div>
    </div>
</template>

<script>
import {
    CollectionIcon,
    AdjustmentsIcon,
    SearchIcon,
    PrinterIcon,
    ArrowNarrowRightIcon,
    TrashIcon,
} from "@heroicons/vue/outline";
import AddProduct from "../components/products/AddProduct.vue";
import ConfirmDelete from "../components/ConfirmDelete.vue";
import axios from "axios";
import {mapActions, mapGetters} from "vuex";
import {CurrencyFormatter} from "../factories/CurrencyFormatterFactory";
import {API} from "../api";

export default {
    components: {
        AddProduct,
        ConfirmDelete,
        SearchIcon,
        CollectionIcon,
        AdjustmentsIcon,
        ArrowNarrowRightIcon,
        PrinterIcon,
        TrashIcon,
    },
    data() {
        return {
            products: [],
            isOpen: false,
            categoryName: "",
            category_id: null,
            errorMessage: null,
            confirmDelete: false,
            deletedItem: null,
            finance: "",
        };
    },
    computed: {
        ...mapGetters(["userInfo"]),
    },
    created() {
        this.getProducts();
        this.setFinanceVariable();
    },
    methods: {
        ...mapActions(["changeLoading"]),
        setFinanceVariable() {
            this.finance = "finance";
        },
        getCurrency(amount){
            return CurrencyFormatter.getCurrency(amount);
        },
        getProducts() {
            this.changeLoading();
            this.categoryName = this.$route.params.categoryName;
            this.category_id = this.$route.params.category_id;

           API.getCategoryProducts(this.$route.params.category_id)
                .then((response) => {
                    this.products = response.data;
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.changeLoading();
                    this.errorMessage = err.message;
                });
        },
        closeModal() {
            this.isOpen = false;
        },
        toggleDeleteProduct(id) {
            this.deletedItem = id;
            this.confirmDelete = true;
        },
        toggleDelete() {
            this.changeLoading();
            axios
                .delete(`api/products/${this.deletedItem}/destroy`)
                .then(() => {
                    this.confirmDelete = false;
                })
                .then(() => {
                    this.getProducts();
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
            display: flex;
            justify-content: space-between;
            padding: 20px;
            border-bottom: 1px solid rgb(163 163 163);

            .Left__Side {
                display: flex;
                gap: 10px;
                align-items: center;
                font-weight: 700;
                cursor: pointer;

                .Icon {
                    height: 20px;
                    object-fit: contain;
                    cursor: pointer;
                }
            }

            .Right__Side {
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

                        &:hover {
                            background-color: rgb(236, 236, 236);
                        }

                        .Icons {
                            display: flex;
                            gap: 30px;
                        }

                        td {
                            position: relative;

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
