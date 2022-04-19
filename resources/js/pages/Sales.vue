<template>
    <div class="Main__Wrapper">
        <div class="NavBar__Container">
            <div class="Title">
                <ShoppingBagIcon class="Icon" />
                <p>Sales</p>
            </div>
            <div class="Search__Bar">
                <input type="text" class="Input" placeholder="Search product" />
                <SearchIcon class="Search__Icon" />
            </div>
            <div class="Options"></div>
        </div>

        <div class="Contents__Container">
            <div class="Heading">
                <div class="Left__Side">
                    <AdjustmentsIcon class="Icon" />
                    Filters
                </div>
                <div class="Right__Side">
                    <div class="Add__Category" @click="isOpen = !isOpen">
                        Create Sale
                    </div>
                    <PrinterIcon class="Icon" />
                </div>
                <AddSale v-if="isOpen" @closeAddSale="isOpen = !isOpen" />
            </div>
            <div class="Table__Container">
                <table class="Table">
                    <thead class="Table__Head">
                        <tr class="Tr">
                            <td>Date</td>
                            <td>Customer</td>
                            <td>Mode</td>
                            <td>Products</td>
                            <td>Price</td>
                        </tr>
                    </thead>
                    <tbody class="Table__Body">
                        <tr class="Tr" v-for="sale in sales" :key="sale.id">
                            <td>{{ sale.date }}</td>
                            <td>{{ sale.customer_name }}</td>
                            <td>{{ sale.customer_contact }}</td>
                            <td>{{ sale.products.length }}</td>
                            <td>30,000</td>
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
} from "@heroicons/vue/outline";
import AddSale from "../components/AddSale.vue";
import axios from "axios";
export default {
    components: {
        AddSale,
        SearchIcon,
        CollectionIcon,
        AdjustmentsIcon,
        PrinterIcon,
        ArrowNarrowRightIcon,
        PrinterIcon,
        ShoppingBagIcon,
    },
    data() {
        return {
            isOpen: false,
            sales: [],
            errorMessage: "",
        };
    },
    created() {
        this.getSales();
    },
    methods: {
        getSales() {
            axios
                .get("api/sales")
                .then((res) => {
                    this.sales = res.data;
                })
                .catch((err) => {
                    this.errorMessage = err.message;
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
