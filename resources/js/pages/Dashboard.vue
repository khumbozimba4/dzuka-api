<template>
    <div class="Main__Wrapper">
        <div class="NavBar__Container">
            <div class="Title">
                <ColorSwatchIcon class="Icon" />
                <p>Dashboard</p>
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
        <ProductSearch
            v-if="search"
            :search="search"
            @closeSearch="closeSearch"
        />
        <div class="Cards__Wrap">
            <Card
                :cardNote="total_sales"
                src="checkoutcart"
                title="Total sales today"
                color="Card__Note__Green"
            />
            <Card
                :cardNote="total_products_out_of_stock"
                src="outofstock"
                title="Out of stock"
                color="Card__Note"
                @click="gotoStock"
            />
        </div>

        <div class="Contents__Container">
            <div class="Heading">
                <div class="Right__Side">
                    <h1>
                        <strong class="font-bold">Sales Today</strong>
                        {{ getDate() }}
                    </h1>
                </div>
                <div class="Left__Side">
                    <PrinterIcon class="Icon" />
                </div>
            </div>
            <div class="Table__Container">
                <table class="Table">
                    <thead class="Table__Head">
                        <tr class="Tr">
                            <td>#</td>
                            <td>Product</td>
                            <td>Price</td>
                            <td>Quantity</td>
                            <td>Amount (MWK)</td>
                        </tr>
                    </thead>
                    <tbody class="Table__Body">
                        <tr
                            class="Tr"
                            v-for="(sale, index) in list"
                            :key="sale.id"
                        >
                            <td>
                                <strong>{{ index + 1 }}</strong>
                            </td>
                            <td>{{ sale.product.product_name }}</td>
                            <td>{{ sale.product.price }}</td>
                            <td>{{ sale.quantity }}</td>
                            <td>{{ getCurrency(sale.amount) }}</td>
                        </tr>
                        <div v-if="!list.length">No sales made yet!</div>
                    </tbody>
                </table>
                <TablePagination
                    v-if="list.length"
                    :current="sales.current_page"
                    :total="sales.total"
                    :per_page="sales.per_page"
                    :prev_page_url="sales.prev_page_url"
                    :next_page_url="sales.next_page_url"
                    @change="(value) => getSales(value)"
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
    ArrowNarrowRightIcon,
    ShoppingBagIcon,
    ColorSwatchIcon,
} from "@heroicons/vue/outline";
import Card from "../components/Card.vue";
import ProductSearch from "../components/products/ProductSearch.vue";
import moment from "moment";
import { CurrencyFormatter } from "../factories/CurrencyFormatterFactory";
import { API } from "../api";
import TablePagination from "../components/TablePagination.vue";

export default {
    components: {
        Card,
        ProductSearch,
        SearchIcon,
        ColorSwatchIcon,
        CollectionIcon,
        AdjustmentsIcon,
        ArrowNarrowRightIcon,
        PrinterIcon,
        ShoppingBagIcon,
        TablePagination,
    },
    data() {
        return {
            sales: null,
            list: [],
            search: "",
            total_sales: null,
            total_products_out_of_stock: null,
        };
    },
    created() {
        this.getSummaries();
        this.getSales();
    },
    methods: {
        getSummaries() {
            API.listSummaries()
                .then(({ data }) => {
                    console.log(data);
                    this.total_sales = data["total_sales"];
                    this.total_products_out_of_stock =
                        data["total_products_out_of_stock"];
                })
                .catch((err) => {
                    console.log(err.message);
                });
        },
        getSales(page) {
            API.listSales(page)
                .then(({ data }) => {
                    this.sales = data;
                    this.list = data.data;
                })
                .catch((err) => {
                    console.log(err.message);
                });
        },
        getDate(date = new Date()) {
            return moment(new Date(date)).format("MMM Do YY");
        },
        getCurrency(amount) {
            return CurrencyFormatter.getCurrency(amount);
        },
        closeSearch() {
            this.search = "";
        },
        gotoStock() {
            this.$router.push("/stock");
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
                width: 150px;

                &:focus {
                    outline: none;
                    border: 0px;
                }
            }

            .Search__Icon {
                padding: 5px 10px;
                height: 30px;
                color: rgb(115 115 115);
            }
        }
    }

    .Cards__Wrap {
        display: flex;
        padding: 20px;
        margin: 20px;
        gap: 100px;
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
                            .Icon {
                                height: 30px;
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
