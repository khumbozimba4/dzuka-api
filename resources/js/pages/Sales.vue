<template>
    <div class="Main__Wrapper">
        <ConfirmDelete
            @toggleCancel="toggleCancel"
            @toggleDelete="toggleDelete"
            v-if="confirmDelete"
        />
        <div class="NavBar__Container">
            <div class="Title">
                <ShoppingBagIcon class="Icon" />
                <p>Sales</p>
            </div>
            <div class="Search__Bar">
                <input
                    type="text"
                    class="Input"
                    placeholder="Search sales by customer"
                    v-model="search"
                />
                <SearchIcon class="Search__Icon" />
            </div>
            <div class="Options"></div>
        </div>
        <SaleSearch v-if="search" :search="search" />

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
                            <td>Date</td>
                            <td>Product</td>
                            <td>Stock Sold</td>
                            <td>Total Amount</td>
                        </tr>
                    </thead>
                    <tbody class="Table__Body">
                        <tr v-if="!sales.length">
                            No sales made yet!
                        </tr>
                        <tr
                            class="Tr"
                            v-for="(sale, index) in sales"
                            :key="sale.id"
                        >
                            <td>
                                <strong>{{ index + 1 }}</strong>
                            </td>
                            <td>{{ getDate(sale.created_at) }}</td>
                            <td>{{ sale.product.product_name }}</td>
                            <td>{{ sale.quantity }}</td>
                            <td>
                                {{ sale.amount }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div v-if="errorMessage">{{ errorMessage }}</div>
    </div>
</template>

<script>
import {
    CollectionIcon,
    AdjustmentsIcon,
    SearchIcon,
    PrinterIcon,
    ArrowNarrowRightIcon,
    ShoppingBagIcon,
    SortAscendingIcon,
    SortDescendingIcon,
    PencilIcon,
    TrashIcon,
} from "@heroicons/vue/outline";
import { XCircleIcon } from "@heroicons/vue/solid";
import AddSale from "../components/AddSale.vue";
import EditSale from "../components/EditSale.vue";
import SaleSearch from "../components/SaleSearch.vue";
import ConfirmDelete from "../components/ConfirmDelete.vue";
import axios from "axios";
import "@ocrv/vue-tailwind-pagination/styles";
import VueTailwindPagination from "@ocrv/vue-tailwind-pagination";
import { mapActions, mapGetters } from "vuex";
import moment from "moment";
export default {
    components: {
        AddSale,
        EditSale,
        SaleSearch,
        ConfirmDelete,
        VueTailwindPagination,
        TrashIcon,
        SortAscendingIcon,
        XCircleIcon,
        SortDescendingIcon,
        SearchIcon,
        CollectionIcon,
        AdjustmentsIcon,
        PencilIcon,
        ArrowNarrowRightIcon,
        PrinterIcon,
        ShoppingBagIcon,
    },
    data() {
        return {
            sales: [],
        };
    },
    computed: {
        ...mapGetters(["userInfo"]),
    },
    created() {
        this.getProducts();
    },
    methods: {
        ...mapActions(["changeLoading"]),
        getProducts() {
            this.changeLoading();
            axios
                .get("api/sales")
                .then((res) => {
                    this.sales = res.data;
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.changeLoading();
                    this.errorMessage = err.message;
                });
        },
        getDate(date) {
            return moment(new Date(date)).format("LL");
        },
        getSalesQuantity(previous, stock) {
            return previous < stock ? 0 : previous - stock;
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
                width: 250px;

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
                position: relative;
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
                .Filters__Container {
                    position: absolute;
                    width: 300px;
                    z-index: 999;
                    background: #fff;
                    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
                        0 4px 6px -4px rgb(0 0 0 / 0.1);
                    top: 30px;
                    left: 20px;
                    border-top: 1px solid rgb(163, 163, 163);

                    .Filters__Heading {
                        display: flex;
                        width: 100%;
                        justify-content: space-between;
                        padding: 15px 20px 5px 20px;

                        .Close__Icon {
                            height: 20px;
                            object-fit: contain;
                        }
                    }

                    li {
                        padding: 10px 20px;
                        color: rgb(53, 53, 53);
                        display: grid;
                        grid-template-columns: 60% 20% 20%;
                        border-top: 1px solid lightgrey;
                        &:hover {
                            background-color: rgb(236, 236, 236);
                        }

                        .Icon {
                            height: 20px;
                            object-fit: contain;
                            transition: all 0.2s ease-in-out;

                            &:hover {
                                color: rgb(31, 31, 31);
                                transform: scale(1.2);
                            }
                        }
                    }
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
