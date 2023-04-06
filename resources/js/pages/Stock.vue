<template>
    <div class="Main__Wrapper">
        <div class="NavBar__Container">
            <div class="Title">
                <AdjustmentsIcon class="Icon" />
                <p>Stock Control</p>
            </div>
            <div class="Search__Bar">
                <input
                    type="text"
                    class="Input"
                    placeholder="Search product"
                    v-model="search"
                />
                <SearchIcon class="Search__Icon" />
            </div>
            <div class="Options"></div>
        </div>

        <!-- MAIN TABLE CONTAINER -->
        <div class="Contents__Container">
            <div class="Heading">
                <div class="Left__Side">
                    <AdjustmentsIcon class="Icon" />
                    Filters
                </div>
                <div class="Right__Side">
                    <PrinterIcon class="Icon" />
                </div>
            </div>
            <div class="Table__Container">
                <table class="Table">
                    <thead class="Table__Head">
                        <tr class="Tr">
                            <td>#</td>
                            <td>Product</td>
                            <td>Stock</td>
                            <td>Action</td>
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
                            <td class="Allocate__Stock">
                                <button
                                    @click="toggleEdit(index)"
                                    class="submit_button"
                                >
                                    Submit Audited Stock
                                </button>

                                <EditStock
                                    v-if="
                                        isOpen &&
                                        products[index] === clickedProduct
                                    "
                                    :product="product"
                                    @getProducts="getProducts"
                                    @close="isOpen = false"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import {
    CollectionIcon,
    AdjustmentsIcon,
    SearchIcon,
    PrinterIcon,
    MinusIcon,
    ArrowNarrowRightIcon,
    PencilIcon,
    XIcon,
} from "@heroicons/vue/outline";
import EditStock from "../components/stock/SubmitAuditStock.vue";
import axios from "axios";
import { mapActions, mapGetters } from "vuex";
export default {
    components: {
        EditStock,
        SearchIcon,
        MinusIcon,
        CollectionIcon,
        AdjustmentsIcon,
        ArrowNarrowRightIcon,
        PrinterIcon,
        PencilIcon,
        XIcon,
    },
    data() {
        return {
            products: [],
            errorMessage: null,
            allocateIsOpen: false,
            isOpen: false,
            clickedProduct: null,
            search: "",
            searchedProducts: [],
            isOpenSearch: false,
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
        getProducts() {
            this.changeLoading();
            axios
                .get("api/products")
                .then((res) => {
                    this.products = res.data;
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.errorMessage = err.message;
                });
        },
        toggleEdit(index) {
            this.clickedProduct = this.products[index];
            this.isOpen = !this.isOpen;
        },
        toggleEditSearch(index) {
            this.clickedProduct = this.products[index];
            this.isOpenSearch = !this.isOpenSearch;
        },
        toggleStockHistory(product) {
            this.$store.commit("setHistories", product.histories);
            this.$router.push({
                name: "history",
                params: {
                    product_id: product.id,
                    product_name: product.product_name,
                },
            });
        },
    },
    watch: {
        search(value) {
            axios
                .get(`api/products/search/${value}`)
                .then((res) => {
                    this.searchedProducts = res.data;
                })
                .catch((err) => {
                    this.errMessage = err.message;
                });
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
        .Heading2 {
            padding: 20px;
            border-bottom: 1px solid rgb(163 163 163);
        }
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
                        td {
                        }
                        .Allocate__Stock {
                            margin: 10px;
                            display: flex;

                            .history_button {
                                background: purple;
                                color: #fff;
                                font-weight: bold;
                                text-align: center;
                                padding: 10px;
                                margin-top: 5px;
                                text-transform: capitalize;
                                border-radius: 3px;
                            }
                            .submit_button {
                                margin-right: 20px;
                                background: green;
                                color: #fff;
                                font-weight: bold;
                                text-align: center;
                                padding: 10px;
                                margin-top: 5px;
                                text-transform: capitalize;
                                border-radius: 3px;
                            }
                        }
                    }
                }
            }
        }
    }
}
</style>
