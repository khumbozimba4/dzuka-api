<template>
    <div class="Main__Wrapper">
        <div class="NavBar__Container">
            <div class="Title">
                <AdjustmentsIcon class="Icon" />
                <p>Products/Stock</p>
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

        <div class="Contents__Container">
            <div class="Heading">
                <div class="Tabs">
                    <div class="Item__Active Item">Stock</div>
                    <div class="Item" @click="goToAudits">Audit Histories</div>
                    <div class="Item" @click="goToSupplies">Supplies</div>
                </div>
            </div>
            <div class="Table__Container">
                <table class="Table">
                    <thead class="Table__Head">
                        <tr class="Tr">
                            <td>#</td>
                            <td>Product</td>
                            <td>Price(MKW)</td>
                            <td>Stock Count</td>
                            <td>Center</td>
                            <td v-if="userInfo.role !== 'Finance'">Action</td>
                        </tr>
                    </thead>
                    <tbody class="Table__Body">
                        <tr v-if="!list.length">
                            No products available!
                        </tr>
                        <tr
                            class="Tr"
                            v-for="(product, index) in list"
                            :key="product.id"
                        >
                            <td>
                                <strong>{{ index + 1 }}</strong>
                            </td>
                            <td>{{ product.product_name }}</td>
                            <td>{{ getCurrency(product.price) }}</td>
                            <td>{{ product.stock }}</td>
                            <td>{{ product.category.category_name }}</td>
                            <td class="Allocate__Stock" v-if="userInfo.role !== 'Finance'">
                                <button
                                    @click="toggleEdit(index)"
                                    :class="
                                        product.stock
                                            ? 'button button_submit'
                                            : 'button button_submit_disabled'
                                    "

                                >
                                    Submit Audited Stock
                                </button>

                                <SubmitAuditStock
                                    v-if="
                                        isOpen && list[index] === clickedProduct
                                    "
                                    :product="product"
                                    @getProducts="getProducts"
                                    @close="isOpen = false"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <TablePagination
                    v-if="list.length"
                    :current="products.current_page"
                    :total="products.total"
                    :per_page="products.per_page"
                    :prev_page_url="products.prev_page_url"
                    :next_page_url="products.next_page_url"
                    @change="(value) => getProducts(value)"
                />
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
import SubmitAuditStock from "../../components/stock/SubmitAuditStock.vue";
import axios from "axios";
import { mapActions, mapGetters } from "vuex";
import { CurrencyFormatter } from "../../factories/CurrencyFormatterFactory";
import TablePagination from "../../components/TablePagination.vue";
import { API } from "../../api";

export default {
    components: {
        SubmitAuditStock,
        SearchIcon,
        MinusIcon,
        CollectionIcon,
        AdjustmentsIcon,
        ArrowNarrowRightIcon,
        PrinterIcon,
        PencilIcon,
        XIcon,
        TablePagination,
    },
    data() {
        return {
            products: null,
            list: [],
            errorMessage: null,
            allocateIsOpen: false,
            isOpen: false,
            clickedProduct: null,
            search: "",
            searchedProducts: [],
            isOpenSearch: false,
        };
    },
    computed: {
        ...mapGetters(["userInfo"]),
    },
    created() {
        this.getProducts(1);
    },
    methods: {
        ...mapActions(["changeLoading"]),
        getProducts(page) {
            this.changeLoading();
            API.listProducts(page)
                .then(({ data }) => {
                    this.products = data;
                    this.list = data.data;
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.errorMessage = err.message;
                });
        },
        toggleEdit(index) {
            this.clickedProduct = this.list[index];
            this.isOpen = !this.isOpen;
        },
        toggleEditSearch(index) {
            this.clickedProduct = this.list[index];
            this.isOpenSearch = !this.isOpenSearch;
        },
        goToAudits() {
            this.$router.push({
                name: "audits",
            });
        },
        goToSupplies() {
            this.$router.push({
                name: "supplies",
            });
        },
        getCurrency(amount) {
            return CurrencyFormatter.getCurrency(amount);
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

        .Heading {
            padding: 20px;
            border-bottom: 1px solid rgb(163 163 163);

            .Tabs {
                width: 100%;
                display: flex;
                align-items: center;
                cursor: pointer;
                text-transform: uppercase;

                .Item {
                    flex: 1;
                    padding: 10px;
                    color: rgb(31 41 55);
                    text-align: center;
                    cursor: pointer;
                    border: 1px solid rgb(243 244 246);
                    background-color: rgb(229 231 235);
                    border-radius: 3px;
                    margin: 0 5px;

                    &:hover {
                        opacity: 0.7;
                    }
                }

                .Item__Active {
                    background-color: rgb(34 197 94);
                    color: white;
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

                            .button {
                                background: purple;
                                color: #fff;
                                font-weight: bold;
                                text-align: center;
                                padding: 10px;
                                margin-top: 5px;
                                text-transform: capitalize;
                                border-radius: 3px;
                            }

                            .button_submit {
                                background-color: rgb(34 197 94);
                                &:hover {
                                    background-color: rgb(22 163 74);
                                }
                            }

                            .button_submit_disabled {
                                background-color: rgb(34 197 94);
                                opacity: 0.5;
                            }
                        }
                    }
                }
            }
        }
    }
}
</style>
