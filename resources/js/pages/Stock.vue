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

        <!-- CONTAINER FOR SEARCH RESULTS -->
        <div class="Contents__Container" v-if="search">
            <div class="Heading2">
                <h1>Search Results</h1>
                <h3 v-if="searchedProducts.length == 0">
                    Oops! we cant find that user for you😢
                </h3>
            </div>
            <div class="Table__Container" v-if="searchedProducts.length !== 0">
                <table class="Table">
                    <thead class="Table__Head">
                        <tr class="Tr">
                            <td>Product name</td>
                            <td>Last Allocated</td>
                            <td>Previous stock</td>
                            <td>Recently Sold/Removed</td>
                            <td>Available stock</td>
                            <td>Edit stock</td>
                        </tr>
                    </thead>
                    <tbody class="Table__Body">
                        <tr
                            class="Tr"
                            v-for="(product, index) in searchedProducts"
                            :key="product.id"
                        >
                            <td>{{ product.product_name }}</td>
                            <td>{{ product.recently_allocated }}</td>
                            <td>{{ product.previous_stock }}</td>
                            <td>{{ product.recently_subtracted }}</td>
                            <td>{{ product.stock }}</td>
                            <td class="Allocate__Stock">
                                <PencilIcon
                                    class="Icon"
                                    @click="toggleEditSearch(index)"
                                />
                                <EditStock
                                    v-if="
                                        isOpenSearch &&
                                        products[index] === clickedProduct
                                    "
                                    :productID="product.id"
                                    :currentStock="product.stock"
                                    @getProducts="getProducts"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
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
                            <td>Product name</td>
                            <td>Last Allocated</td>
                            <td>Previous stock</td>
                            <td>Recently sold</td>
                            <td>Available stock</td>
                            <td>Edit stock</td>
                        </tr>
                    </thead>
                    <tbody class="Table__Body">
                        <tr
                            class="Tr"
                            v-for="(product, index) in products"
                            :key="product.id"
                        >
                            <td>{{ product.product_name }}</td>
                            <td>{{ product.recently_allocated }}</td>
                            <td>{{ product.previous_stock }}</td>
                            <td>{{ product.recently_subtracted }}</td>
                            <td>{{ product.stock }}</td>
                            <td class="Allocate__Stock">
                                <PencilIcon
                                    class="Icon"
                                    @click="toggleEdit(index)"
                                />
                                <EditStock
                                    v-if="
                                        isOpen &&
                                        products[index] === clickedProduct
                                    "
                                    :productID="product.id"
                                    :currentStock="product.stock"
                                    @getProducts="getProducts"
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
} from "@heroicons/vue/outline";
import EditStock from "../components/EditStock.vue";
import axios from "axios";
export default {
    components: {
        EditStock,
        SearchIcon,
        MinusIcon,
        CollectionIcon,
        AdjustmentsIcon,
        PrinterIcon,
        ArrowNarrowRightIcon,
        PrinterIcon,
        PencilIcon,
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
        };
    },
    created() {
        this.getProducts();
    },
    methods: {
        getProducts() {
            axios
                .get("api/products")
                .then((res) => {
                    this.products = res.data;
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
            position: relative;
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
                        .Allocate__Stock {
                            position: relative;
                            display: flex;
                            align-items: center;
                            gap: 20px;
                            .Icon {
                                height: 20px;
                                object-fit: contain;
                                cursor: pointer;
                            }
                        }
                    }
                }
            }
        }
    }
}
</style>
